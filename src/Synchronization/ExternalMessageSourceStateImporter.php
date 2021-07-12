<?php
declare( strict_types = 1 );

/**
 * Finds external changes for file based message groups.
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @since 2016.02
 */

namespace MediaWiki\Extension\Translate\Synchronization;

use Config;
use FileBasedMessageGroup;
use JobQueueGroup;
use MediaWiki\Extension\Translate\MessageSync\MessageSourceChange;
use MessageChangeStorage;
use MessageGroups;
use MessageHandle;
use MessageIndex;
use MessageUpdateJob;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Title;
use function wfWarn;

class ExternalMessageSourceStateImporter {
	/** @var Config */
	private $config;
	/** @var GroupSynchronizationCache */
	private $groupSynchronizationCache;
	/** @var JobQueueGroup */
	private $jobQueueGroup;
	/** @var LoggerInterface */
	private $logger;
	/** @var MessageIndex */
	private $messageIndex;

	public function __construct(
		Config $config,
		GroupSynchronizationCache $groupSynchronizationCache,
		JobQueueGroup $jobQueueGroup,
		LoggerInterface $logger,
		MessageIndex $messageIndex
	) {
		$this->config = $config;
		$this->groupSynchronizationCache = $groupSynchronizationCache;
		$this->jobQueueGroup = $jobQueueGroup;
		$this->logger = $logger;
		$this->messageIndex = $messageIndex;
	}

	/**
	 * @param MessageSourceChange[] $changeData
	 * @param string $name
	 * @return array
	 */
	public function importSafe( array $changeData, string $name ): array {
		$processed = [];
		$skipped = [];
		$jobs = [];

		$groupSyncCacheEnabled = $this->config->get( 'TranslateGroupSynchronizationCache' );

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

			$groupSafeLanguages = self::identifySafeLanguages( $group, $changesForGroup );

			foreach ( $languages as $language ) {
				if ( !$groupSafeLanguages[ $language ] ) {
					$skipped[$groupId] = true;
					continue;
				}

				$additions = $changesForGroup->getAdditions( $language );
				if ( $additions === [] ) {
					continue;
				}

				[ $groupLanguageJobs, $groupProcessed ] = $this->createMessageUpdateJobs(
					$group, $additions, $language
				);

				$groupJobs = array_merge( $groupJobs, $groupLanguageJobs );
				$processed[$groupId][$language] = $groupProcessed;

				$changesForGroup->removeChangesForLanguage( $language );
				$group->getMessageGroupCache( $language )->create();
			}

			// Mark the skipped group as in review
			if ( $groupSyncCacheEnabled && isset( $skipped[$groupId] ) ) {
				$this->groupSynchronizationCache->markGroupAsInReview( $groupId );
			}

			if ( $groupJobs !== [] ) {
				if ( $groupSyncCacheEnabled ) {
					$this->updateGroupSyncInfo( $groupId, $groupJobs );
				}
				$jobs = array_merge( $jobs, $groupJobs );
			}
		}

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

	/** Creates MessageUpdateJobs additions for a language under a group */
	private function createMessageUpdateJobs(
		FileBasedMessageGroup $group,
		array $additions,
		string $language
	): array {
		$groupId = $group->getId();
		$jobs = [];
		$processed = 0;
		foreach ( $additions as $addition ) {
			$namespace = $group->getNamespace();
			$name = "{$addition['key']}/$language";

			$title = Title::makeTitleSafe( $namespace, $name );
			if ( !$title ) {
				wfWarn( "Invalid title for group $groupId key {$addition['key']}" );
				continue;
			}

			$jobs[] = MessageUpdateJob::newJob( $title, $addition['content'] );
			$processed++;
		}

		return [ $jobs, $processed ];
	}

	/**
	 * @param string $groupId
	 * @param MessageUpdateJob[] $groupJobs
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
	private static function identifySafeLanguages(
		FileBasedMessageGroup $group,
		MessageSourceChange $changesForGroup
	): array {
		$sourceLanguage = $group->getSourceLanguage();
		$safeLanguagesMap = [];
		$modifiedLanguages = $changesForGroup->getLanguages();

		// Set all languages to not safe to start with.
		$safeLanguagesMap[ $sourceLanguage ] = false;
		foreach ( $modifiedLanguages as $language ) {
			$safeLanguagesMap[ $language ] = false;
		}

		if ( !$changesForGroup->hasOnly( $sourceLanguage, MessageSourceChange::ADDITION ) ) {
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
			if ( !$changesForGroup->hasOnly( $language, MessageSourceChange::ADDITION ) ) {
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
					$sourceHandle = new MessageHandle( Title::makeTitle( $groupNamespace, $msgKey ) );
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
}
