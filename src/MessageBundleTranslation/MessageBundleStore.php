<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageBundleTranslation;

use InvalidArgumentException;
use JobQueueGroup;
use MediaWiki\Extension\Translate\MessageGroupProcessing\RevTagStore;
use MediaWiki\Extension\Translate\MessageGroupProcessing\TranslatableBundle;
use MediaWiki\Extension\Translate\MessageGroupProcessing\TranslatableBundleStore;
use MediaWiki\Languages\LanguageNameUtils;
use MediaWiki\Revision\RevisionRecord;
use Message;
use MessageGroups;
use MessageIndex;
use RequestContext;
use SpecialPageLanguage;
use Title;
use TranslateMetadata;

/**
 * @author Abijeet Patro
 * @author Niklas Laxström
 * @since 2022.04
 * @license GPL-2.0-or-later
 */
class MessageBundleStore implements TranslatableBundleStore {
	/** @var RevTagStore */
	private $revTagStore;
	/** @var JobQueueGroup */
	private $jobQueue;
	/** @var LanguageNameUtils */
	private $languageNameUtils;
	/** @var MessageIndex */
	private $messageIndex;
	private const METADATA_KEYS_DB = [
		'priorityforce',
		'prioritylangs'
	];

	public function __construct(
		RevTagStore $revTagStore,
		JobQueueGroup $jobQueue,
		LanguageNameUtils $languageNameUtils,
		MessageIndex $messageIndex
	) {
		$this->revTagStore = $revTagStore;
		$this->jobQueue = $jobQueue;
		$this->languageNameUtils = $languageNameUtils;
		$this->messageIndex = $messageIndex;
	}

	public function move( Title $oldName, Title $newName ): void {
		$oldBundle = new MessageBundle( $oldName );
		$newBundle = new MessageBundle( $newName );

		TranslateMetadata::moveMetadata(
			$oldBundle->getMessageGroupId(),
			$newBundle->getMessageGroupId(),
			self::METADATA_KEYS_DB
		);

		MessageBundle::clearSourcePageCache();

		// Re-render the bundles to get everything in sync
		MessageGroups::singleton()->recache();
		// Update message index now so that, when after this job the MoveTranslationUnits hook
		// runs in deferred updates, it will not run MessageIndexRebuildJob (T175834).
		$this->messageIndex->rebuild();
	}

	public function handleNullRevisionInsert( TranslatableBundle $bundle, RevisionRecord $revision ): void {
		if ( !$bundle instanceof MessageBundle ) {
			throw new InvalidArgumentException(
				'Expected $bundle to be of type MessageBundle, got ' . get_class( $bundle )
			);
		}

		$this->revTagStore->replaceTag( $bundle->getTitle(), RevTagStore::MB_VALID_TAG, $revision->getId() );
		MessageBundle::clearSourcePageCache();
	}

	public function delete( Title $title ): void {
		$this->revTagStore->removeTags( $title, RevTagStore::MB_VALID_TAG );

		$bundle = new MessageBundle( $title );
		TranslateMetadata::clearMetadata( $bundle->getMessageGroupId(), self::METADATA_KEYS_DB );

		MessageBundle::clearSourcePageCache();

		MessageGroups::singleton()->recache();
		$this->messageIndex->rebuild();
	}

	public function validate( Title $pageTitle, MessageBundleContent $content ): void {
		$content->validate();
		// Verify that the language code is valid
		$metadata = $content->getMetadata();
		$sourceLanguageCode = $metadata->getSourceLanguageCode();
		if ( $sourceLanguageCode ) {
			if ( !$this->languageNameUtils->isKnownLanguageTag( $sourceLanguageCode ) ) {
				throw new MalformedBundle(
					'translate-messagebundle-error-invalid-sourcelanguage', [ $sourceLanguageCode ]
				);
			}

			$revisionId = $this->revTagStore->getLatestRevisionWithTag( $pageTitle, RevTagStore::MB_VALID_TAG );
			// If request wants the source language to be changed after creation, then throw an exception
			if ( $revisionId !== null && $sourceLanguageCode !== $pageTitle->getPageLanguage()->getCode() ) {
				throw new MalformedBundle( 'translate-messagebundle-sourcelanguage-changed' );
			}

		}

		$priorityLanguageCodes = $metadata->getPriorityLanguages();
		if ( $priorityLanguageCodes ) {
			$invalidLanguageCodes = [];
			foreach ( $priorityLanguageCodes as $languageCode ) {
				if ( !is_string( $languageCode ) ) {
					throw new MalformedBundle( 'translate-messagebundle-error-invalid-prioritylanguage-format' );
				}

				if ( !$this->languageNameUtils->isKnownLanguageTag( $languageCode ) ) {
					$invalidLanguageCodes[] = $languageCode;
				}
			}

			if ( $invalidLanguageCodes ) {
				throw new MalformedBundle(
					'translate-messagebundle-error-invalid-prioritylanguage',
					[ Message::listParam( $invalidLanguageCodes ), count( $invalidLanguageCodes ) ]
				);
			}
		}
	}

	public function save(
		Title $pageTitle,
		RevisionRecord $revisionRecord,
		MessageBundleContent $content
	): void {
		// Validate the content before saving
		$this->validate( $pageTitle, $content );

		$previousRevisionId = $this->revTagStore->getLatestRevisionWithTag( $pageTitle, RevTagStore::MB_VALID_TAG );
		if ( $previousRevisionId !== null ) {
			$this->revTagStore->removeTags( $pageTitle, RevTagStore::MB_VALID_TAG );
		}

		if ( $content->isValid() ) {
			// Bundle is valid and contains translatable messages
			$this->revTagStore->replaceTag( $pageTitle, RevTagStore::MB_VALID_TAG, $revisionRecord->getId() );
			MessageBundle::clearSourcePageCache();

			// Defer most of the heavy work to the job queue
			$job = UpdateMessageBundleJob::newJob( $pageTitle, $revisionRecord->getId(), $previousRevisionId );

			$this->jobQueue->push( $job );

			// A new message bundle, set the source language.
			$definedLanguageCode = $content->getMetadata()->getSourceLanguageCode();
			$pageLanguageCode = $pageTitle->getPageLanguage()->getCode();
			if ( $previousRevisionId === null ) {
				if ( $definedLanguageCode !== $pageLanguageCode ) {
					$context = RequestContext::getMain();
					SpecialPageLanguage::changePageLanguage(
						$context,
						$pageTitle,
						$definedLanguageCode,
						wfMessage( 'translate-messagebundle-change-sourcelanguage' )->inContentLanguage()
					);
				}
			}

			// Save the metadata
			$messageBundle = new MessageBundle( $pageTitle );
			$groupId = $messageBundle->getMessageGroupId();

			$metadata = $content->getMetadata();
			$priorityForce = $metadata->areOnlyPriorityLanguagesAllowed() ? 'on' : false;
			$priorityLanguages = $metadata->getPriorityLanguages();
			$priorityLanguages = $priorityLanguages ? implode( ',', $priorityLanguages ) : false;

			TranslateMetadata::set( $groupId, 'prioritylangs', $priorityLanguages );
			TranslateMetadata::set( $groupId, 'priorityforce', $priorityForce );

			$description = $metadata->getDescription();
			TranslateMetadata::set( $groupId, 'description', $description ?? false );
		}

		// What should we do if there are no messages? Use the previous version? Remove the group?
		// Currently, the bundle is removed from translation.
	}
}
