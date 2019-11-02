<?php

/**
 * Finds external changes for file based message groups.
 *
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @since 2016.02
 */

use MediaWiki\Extensions\Translate\MessageSync\MessageSourceChange;

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

		/**
		 * @var MessageSourceChange $changesForGroup
		 */
		foreach ( $changeData as $groupId => $changesForGroup ) {
			$group = MessageGroups::getGroup( $groupId );
			if ( !$group ) {
				unset( $changeData[$groupId] );
				continue;
			}

			$processed[$groupId] = 0;
			$languages = $changesForGroup->getLanguages();

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

				list( $groupJobs, $groupProcessed ) = $this->createMessageUpdateJobs(
					$group, $additions, $language
				);

				$jobs = array_merge( $jobs, $groupJobs );
				$processed[$groupId] = $groupProcessed;

				$changesForGroup->removeChangesForLanguage( $language );

				$cache = new MessageGroupCache( $groupId, $language );
				$cache->create();
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
}
