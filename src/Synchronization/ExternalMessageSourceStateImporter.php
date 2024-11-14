<?php
declare( strict_types = 1 );

/**
 * Finds external changes for file based message groups.
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @since 2016.02
 */
namespace MediaWiki\Extension\Translate\Synchronization;

use FileBasedMessageGroup;
use JobQueueGroup;
use MediaWiki\Config\ServiceOptions;
use MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroups;
use MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroupSubscription;
use MediaWiki\Extension\Translate\MessageLoading\MessageHandle;
use MediaWiki\Extension\Translate\MessageLoading\MessageIndex;
use MediaWiki\Extension\Translate\MessageSync\MessageSourceChange;
use MediaWiki\Language\RawMessage;
use MediaWiki\Status\Status;
use MediaWiki\Title\TitleFactory;
use MessageGroup;
use Psr\Log\LoggerInterface;
use RuntimeException;
use function wfWarn;

class ExternalMessageSourceStateImporter {
	private GroupSynchronizationCache $groupSynchronizationCache;
	private JobQueueGroup $jobQueueGroup;
	private LoggerInterface $logger;
	private MessageIndex $messageIndex;
	private TitleFactory $titleFactory;
	private MessageGroupSubscription $messageGroupSubscription;
	private bool $isGroupSyncCacheEnabled;
	// Do not perform any import
	public const IMPORT_NONE = 1;
	// Import changes in a language for a group if it only has additions
	public const IMPORT_SAFE = 2;
	// Import changes in a language for a group if it only has additions or changes, but
	// not deletions as it may be a rename of an addition
	public const IMPORT_NON_RENAMES = 3;
	public const CONSTRUCTOR_OPTIONS = [ 'TranslateGroupSynchronizationCache' ];

	public function __construct(
		GroupSynchronizationCache $groupSynchronizationCache,
		JobQueueGroup $jobQueueGroup,
		LoggerInterface $logger,
		MessageIndex $messageIndex,
		TitleFactory $titleFactory,
		MessageGroupSubscription $messageGroupSubscription,
		ServiceOptions $options
	) {
		$this->groupSynchronizationCache = $groupSynchronizationCache;
		$this->jobQueueGroup = $jobQueueGroup;
		$this->logger = $logger;
		$this->messageIndex = $messageIndex;
		$this->titleFactory = $titleFactory;
		$this->messageGroupSubscription = $messageGroupSubscription;
		$options->assertRequiredOptions( self::CONSTRUCTOR_OPTIONS );
		$this->isGroupSyncCacheEnabled = $options->get( 'TranslateGroupSynchronizationCache' );
	}

	/**
	 * @param MessageSourceChange[] $changeData
	 * @param string $name
	 * @param int $importStrategy
	 * @return array
	 */
	public function import( array $changeData, string $name, int $importStrategy ): array {
		$processed = [];
		$skipped = [];
		$jobs = [];

		foreach ( $changeData as $groupId => $changesForGroup ) {
			$group = MessageGroups::getGroup( $groupId );
			if ( !$group ) {
				unset( $changeData[$groupId] );
				continue;
			}

			if ( !$group instanceof FileBasedMessageGroup ) {
				$this->logger->warning(
					'[ExternalMessageSourceStateImporter] Expected FileBasedMessageGroup, ' .
					'but got {class} for group {groupId}',
					[
						'class' => get_class( $group ),
						'groupId' => $groupId
					]
				);
				unset( $changeData[$groupId] );
				continue;
			}

			$processed[$groupId] = [];
			$languages = $changesForGroup->getLanguages();
			$groupJobs = [];

			$groupSafeLanguages = $this->identifySafeLanguages( $group, $changesForGroup, $importStrategy );

			foreach ( $languages as $language ) {
				if ( !$groupSafeLanguages[ $language ] ) {
					$skipped[$groupId] = true;
					continue;
				}

				$additions = $changesForGroup->getAdditions( $language );
				if ( $additions === [] ) {
					continue;
				}

				[ $groupLanguageJobs, $groupProcessed ] = $this->createUpdateMessageJobs(
					$group, $additions, $language
				);

				$groupJobs = array_merge( $groupJobs, $groupLanguageJobs );
				$processed[$groupId][$language] = $groupProcessed;

				// We only remove additions since if less-safe-import option is used, then
				// changes to existing messages might still need to be processed manually.
				$changesForGroup->removeAdditions( $language, null );
				$group->getMessageGroupCache( $language )->create();
			}

			// Mark the skipped group as in review
			if ( $this->isGroupSyncCacheEnabled && isset( $skipped[$groupId] ) ) {
				$this->groupSynchronizationCache->markGroupAsInReview( $groupId );
			}

			if ( $groupJobs !== [] ) {
				if ( $this->isGroupSyncCacheEnabled ) {
					$this->updateGroupSyncInfo( $groupId, $groupJobs );
				}
				$jobs = array_merge( $jobs, $groupJobs );
			}
		}

		$this->messageGroupSubscription->queueNotificationJob();

		// Remove groups where everything was imported
		$changeData = array_filter( $changeData, static function ( MessageSourceChange $change ) {
			return $change->getAllModifications() !== [];
		} );

		// Remove groups with no imports
		$processed = array_filter( $processed );

		$file = MessageChangeStorage::getCdbPath( $name );
		MessageChangeStorage::writeChanges( $changeData, $file );
		$this->jobQueueGroup->push( $jobs );

		return [
			'processed' => $processed,
			'skipped' => $skipped,
			'name' => $name,
		];
	}

	public function canImportGroup( MessageGroup $group, bool $skipGroupSyncCache ): Status {
		$groupId = $group->getId();
		if ( !$group instanceof FileBasedMessageGroup ) {
			$error = "Group $groupId expected to be FileBasedMessageGroup, got " . get_class( $group ) . " instead.";
			return Status::newFatal( new RawMessage( $error ) );
		}

		if ( $this->isGroupSyncCacheEnabled && !$skipGroupSyncCache ) {
			if ( $this->groupSynchronizationCache->isGroupBeingProcessed( $groupId ) ) {
				$error = "Group $groupId is currently being synchronized; skipping processing of changes\n";
				return Status::newFatal( new RawMessage( $error ) );
			}

			if ( $this->groupSynchronizationCache->groupHasErrors( $groupId ) ) {
				$error = "Skipping $groupId due to an error during synchronization\n";
				return Status::newFatal( new RawMessage( $error ) );
			}
		}

		return Status::newGood();
	}

	/**
	 * Creates UpdateMessageJobs additions for a language under a group. Also queues a message
	 * for notification if the addition is in the source language
	 */
	private function createUpdateMessageJobs(
		FileBasedMessageGroup $group,
		array $additions,
		string $language
	): array {
		$groupId = $group->getId();
		$isSourceLanguage = $group->getSourceLanguage() === $language;
		$jobs = [];
		$processed = 0;
		foreach ( $additions as $addition ) {
			$namespace = $group->getNamespace();
			$name = "{$addition['key']}/$language";

			$title = $this->titleFactory->makeTitleSafe( $namespace, $name );
			if ( !$title ) {
				wfWarn( "Invalid title for group $groupId key {$addition['key']}" );
				continue;
			}

			$jobs[] = UpdateMessageJob::newJob( $title, $addition['content'] );
			$processed++;

			if ( $isSourceLanguage ) {
				$this->messageGroupSubscription->queueMessage(
					$title,
					MessageGroupSubscription::STATE_ADDED,
					$groupId
				);
			}
		}

		return [ $jobs, $processed ];
	}

	/**
	 * @param string $groupId
	 * @param UpdateMessageJob[] $groupJobs
	 */
	private function updateGroupSyncInfo( string $groupId, array $groupJobs ): void {
		$messageParams = [];
		$groupMessageKeys = [];
		foreach ( $groupJobs as $job ) {
			$messageParams[] = MessageUpdateParameter::createFromJob( $job );
			// Ensure there are no duplicates as the same key may be present in
			// multiple languages
			$groupMessageKeys[( new MessageHandle( $job->getTitle() ) )->getKey()] = true;
		}

		$group = MessageGroups::getGroup( $groupId );
		if ( $group === null ) {
			// How did we get here? This should never happen.
			throw new RuntimeException( "Did not find group $groupId" );
		}

		$this->messageIndex->storeInterim( $group, array_keys( $groupMessageKeys ) );

		$this->groupSynchronizationCache->addMessages( $groupId, ...$messageParams );
		$this->groupSynchronizationCache->markGroupForSync( $groupId );

		$this->logger->info(
			'[ExternalMessageSourceStateImporter] Synchronization started for {groupId}',
			[ 'groupId' => $groupId ]
		);
	}

	/**
	 * Identifies languages in a message group that are safe to import
	 * @return array<string,bool>
	 */
	private function identifySafeLanguages(
		FileBasedMessageGroup $group,
		MessageSourceChange $changesForGroup,
		int $importStrategy
	): array {
		$sourceLanguage = $group->getSourceLanguage();
		$safeLanguagesMap = [];
		$modifiedLanguages = $changesForGroup->getLanguages();

		// Set all languages to not safe to start with.
		$safeLanguagesMap[ $sourceLanguage ] = false;
		foreach ( $modifiedLanguages as $language ) {
			$safeLanguagesMap[ $language ] = false;
		}

		if ( !self::isLanguageSafe( $changesForGroup, $sourceLanguage, $importStrategy ) ) {
			return $safeLanguagesMap;
		}

		$sourceLanguageKeyCache = [];
		foreach ( $changesForGroup->getAdditions( $sourceLanguage ) as $change ) {
			if ( $change['content'] === '' ) {
				return $safeLanguagesMap;
			}

			$sourceLanguageKeyCache[ $change['key'] ] = true;
		}

		$safeLanguagesMap[ $sourceLanguage ] = true;

		$groupNamespace = $group->getNamespace();

		// Remove source language from the modifiedLanguage list if present since it's already processed.
		// The $sourceLanguageKeyCache will only have values if sourceLanguage has safe changes.
		if ( $sourceLanguageKeyCache ) {
			array_splice( $modifiedLanguages, array_search( $sourceLanguage, $modifiedLanguages ), 1 );
		}

		foreach ( $modifiedLanguages as $language ) {
			if ( !self::isLanguageSafe( $changesForGroup, $sourceLanguage, $importStrategy ) ) {
				continue;
			}

			foreach ( $changesForGroup->getAdditions( $language ) as $change ) {
				if ( $change['content'] === '' ) {
					continue 2;
				}

				$msgKey = $change['key'];

				if ( !isset( $sourceLanguageKeyCache[ $msgKey ] ) ) {
					// This is either a new external translation which is not added in the same sync
					// as the source language key, or this translation does not have a corresponding
					// definition. We will check the message index to determine which of the two.
					$sourceHandle = new MessageHandle( $this->titleFactory->makeTitle( $groupNamespace, $msgKey ) );
					$sourceLanguageKeyCache[ $msgKey ] = $sourceHandle->isValid();
				}

				if ( !$sourceLanguageKeyCache[ $msgKey ] ) {
					continue 2;
				}
			}

			$safeLanguagesMap[ $language ] = true;
		}

		return $safeLanguagesMap;
	}

	private static function isLanguageSafe(
		MessageSourceChange $changesForGroup,
		string $languageCode,
		int $importStrategy
	): bool {
		if ( $importStrategy === self::IMPORT_NONE ) {
			// If import strategy is none, every change needs to be reviewed.
			return false;
		}

		if ( $importStrategy === self::IMPORT_SAFE ) {
			return $changesForGroup->hasOnly( $languageCode, MessageSourceChange::ADDITION );
		}

		// If language has deletions, we consider additions also unsafe because deletions
		// maybe renames of messages that have been added, so they have to be reviewed.
		return $changesForGroup->getDeletions( $languageCode ) === [];
	}
}
