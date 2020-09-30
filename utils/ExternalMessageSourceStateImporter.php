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
	public static function isSafe( MessageSourceChange $changesForGroup, $language ) {
		return $changesForGroup->hasOnly( $language, MessageSourceChange::ADDITION );
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
		$messageParams = [];
		foreach ( $groupJobs as $job ) {
			$messageParams[] = MessageUpdateParameter::createFromJob( $job );
		}

		$groupSyncCache = Services::getInstance()->getGroupSynchronizationCache();
		$groupSyncCache->addMessages( $groupId, ...$messageParams );
		$groupSyncCache->markGroupForSync( $groupId );
	}
}
