<?php

/**
 * Finds external changes for file based message groups.
 *
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @since 2016.02
 */

use MediaWiki\Extension\Translate\MessageSync\MessageSourceChange;
use MediaWiki\Extension\Translate\Services;
use MediaWiki\Extension\Translate\Synchronization\MessageUpdateParameter;
use MediaWiki\MediaWikiServices;

class ExternalMessageSourceStateImporter {

	/**
	 * @param MessageSourceChange[] $changeData
	 * @return array
	 */
	public function importSafe( array $changeData ) {
		$processed = [];
		$skipped = [];
		$jobs = [];
		$jobs[] = MessageIndexRebuildJob::newJob();

		/** @var MessageSourceChange $changesForGroup */
		foreach ( $changeData as $groupId => $changesForGroup ) {
			/** @var FileBasedMessageGroup */
			$group = MessageGroups::getGroup( $groupId );
			if ( !$group ) {
				unset( $changeData[$groupId] );
				continue;
			}
			'@phan-var FileBasedMessageGroup $group';

			$processed[$groupId] = [];
			$languages = $changesForGroup->getLanguages();
			$groupJobs = [];

			// If the source language is not safe to import, skip importing all other
			// languages for the group.
			$sourceLanguage = $group->getSourceLanguage();
			if ( !self::isSafe( $changesForGroup, $sourceLanguage ) ) {
				$skipped[$groupId] = true;
				continue;
			}

			foreach ( $languages as $language ) {
				if ( !self::isSafe( $changesForGroup, $language ) ) {
					// changes other than additions were present
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

			if ( $groupJobs !== [] ) {
				$this->updateGroupSyncInfo( $groupId, $groupJobs );
				$jobs = array_merge( $jobs, $groupJobs );
			}
		}

		// Remove groups where everything was imported
		$changeData = array_filter( $changeData, function ( MessageSourceChange $change ) {
			return $change->getAllModifications() !== [];
		} );

		// Remove groups with no imports
		$processed = array_filter( $processed );

		$name = 'unattended';
		$file = MessageChangeStorage::getCdbPath( $name );
		MessageChangeStorage::writeChanges( $changeData, $file );
		JobQueueGroup::singleton()->push( $jobs );

		return [
			'processed' => $processed,
			'skipped' => $skipped,
			'name' => $name,
		];
	}

	/**
	 * Checks if changes for a language in a group are safe.
	 * @param MessageSourceChange $changesForGroup
	 * @param string $language
	 * @return bool
	 */
	public static function isSafe( MessageSourceChange $changesForGroup, string $language ): bool {
		if ( !$changesForGroup->hasOnly( $language, MessageSourceChange::ADDITION ) ) {
			return false;
		}

		foreach ( $changesForGroup->getAdditions( $language ) as $change ) {
			if ( $change['content'] === '' ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Creates MessagUpdateJobs additions for a language under a group
	 *
	 * @param MessageGroup $group
	 * @param string[][] $additions
	 * @param string $language
	 * @return array
	 */
	private function createMessageUpdateJobs(
		MessageGroup $group, array $additions, string $language
	) {
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
		$config = MediaWikiServices::getInstance()->getMainConfig();

		if ( !$config->get( 'TranslateGroupSynchronizationCache' ) ) {
			return;
		}

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

		MessageIndex::singleton()->storeInterim( $group, array_keys( $groupMessageKeys ) );

		$groupSyncCache = Services::getInstance()->getGroupSynchronizationCache();
		$groupSyncCache->addMessages( $groupId, ...$messageParams );
		$groupSyncCache->markGroupForSync( $groupId );
	}
}
