<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageBundleTranslation;

use InvalidArgumentException;
use JobQueueGroup;
use MediaWiki\Context\RequestContext;
use MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroups;
use MediaWiki\Extension\Translate\MessageGroupProcessing\RevTagStore;
use MediaWiki\Extension\Translate\MessageGroupProcessing\TranslatableBundle;
use MediaWiki\Extension\Translate\MessageGroupProcessing\TranslatableBundleStore;
use MediaWiki\Extension\Translate\MessageLoading\MessageIndex;
use MediaWiki\Extension\Translate\MessageLoading\RebuildMessageIndexJob;
use MediaWiki\Extension\Translate\MessageProcessing\MessageGroupMetadata;
use MediaWiki\Languages\LanguageNameUtils;
use MediaWiki\Message\Message;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\Specials\SpecialPageLanguage;
use MediaWiki\Title\Title;

/**
 * @author Abijeet Patro
 * @author Niklas Laxström
 * @since 2022.04
 * @license GPL-2.0-or-later
 */
class MessageBundleStore implements TranslatableBundleStore {
	private RevTagStore $revTagStore;
	private JobQueueGroup $jobQueue;
	private LanguageNameUtils $languageNameUtils;
	private MessageIndex $messageIndex;
	private MessageGroupMetadata $messageGroupMetadata;
	private const METADATA_KEYS_DB = [
		'priorityforce',
		'prioritylangs'
	];

	public function __construct(
		RevTagStore $revTagStore,
		JobQueueGroup $jobQueue,
		LanguageNameUtils $languageNameUtils,
		MessageIndex $messageIndex,
		MessageGroupMetadata $messageGroupMetadata
	) {
		$this->revTagStore = $revTagStore;
		$this->jobQueue = $jobQueue;
		$this->languageNameUtils = $languageNameUtils;
		$this->messageIndex = $messageIndex;
		$this->messageGroupMetadata = $messageGroupMetadata;
	}

	public function move( Title $oldName, Title $newName ): void {
		$oldBundle = new MessageBundle( $oldName );
		$newBundle = new MessageBundle( $newName );

		$this->messageGroupMetadata->moveMetadata(
			$oldBundle->getMessageGroupId(),
			$newBundle->getMessageGroupId(),
			self::METADATA_KEYS_DB
		);

		MessageBundle::clearSourcePageCache();

		MessageGroups::singleton()->recache();
		// Update message index now so that, when after this job the MoveTranslationUnits hook
		// runs in deferred updates, it will not run RebuildMessageIndexJob (T175834).
		// Notice: currently this code is only called on CLI or in jobs, but this is not very
		// obvious. messageIndex->rebuild() should never be called during web requests due to
		// its slowness.
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
		$this->messageGroupMetadata->clearMetadata( $bundle->getMessageGroupId(), self::METADATA_KEYS_DB );

		MessageBundle::clearSourcePageCache();

		MessageGroups::singleton()->recache();
		$this->jobQueue->push( RebuildMessageIndexJob::newJob( __METHOD__ ) );
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

			$metadata = $content->getMetadata();

			if ( $previousRevisionId === null ) {
				// A new message bundle, set the source language.
				$definedLanguageCode = $metadata->getSourceLanguageCode();
				$pageLanguageCode = $pageTitle->getPageLanguage()->getCode();
				if ( $definedLanguageCode !== null && $definedLanguageCode !== $pageLanguageCode ) {
					$context = RequestContext::getMain();
					SpecialPageLanguage::changePageLanguage(
						$context,
						$pageTitle,
						$definedLanguageCode,
						$context->msg( 'translate-messagebundle-change-sourcelanguage' )->inContentLanguage()->text()
					);
				}
			}

			// Save the metadata
			$messageBundle = new MessageBundle( $pageTitle );
			$groupId = $messageBundle->getMessageGroupId();

			$priorityForce = $metadata->areOnlyPriorityLanguagesAllowed() ? 'on' : false;
			$priorityLanguages = $metadata->getPriorityLanguages();
			$priorityLanguages = $priorityLanguages ? implode( ',', $priorityLanguages ) : false;

			$this->messageGroupMetadata->set( $groupId, 'prioritylangs', $priorityLanguages );
			$this->messageGroupMetadata->set( $groupId, 'priorityforce', $priorityForce );

			$description = $metadata->getDescription();
			$this->messageGroupMetadata->set( $groupId, 'description', $description ?? false );

			$label = $metadata->getLabel();
			$this->messageGroupMetadata->set( $groupId, 'label', $label ?? false );
		}

		// What should we do if there are no messages? Use the previous version? Remove the group?
		// Currently, the bundle is removed from translation.
	}
}
