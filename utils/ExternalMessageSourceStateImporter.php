<?php

/**
 * Finds external changes for file based message groups.
 *
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @since 2016.02
 */
class ExternalMessageSourceStateImporter {

	/**
	 * @param MessageSourceChange[] $changeData
	 * @return array
	 */
	public function importSafe( $changeData ) {
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

			foreach ( $languages as $languageCode ) {
				if ( $changesForGroup->hasOnly( languageCode, MessageSourceChange::M_ADDITION ) ) {
					$skipped[$groupId] = true;
					continue;
				}

				$additions = $changesForGroup->getAdditions( $languageCode );
				if ( $additions === [] ) {
					continue;
				}

				foreach ( $additions as $addition ) {
					$namespace = $group->getNamespace();
					$name = "{$addition['key']}/$languageCode";

					$title = Title::makeTitleSafe( $namespace, $name );
					if ( !$title ) {
						wfWarn( "Invalid title for group $groupId key {$addition['key']}" );
						continue;
					}

					$jobs[] = MessageUpdateJob::newJob( $title, $addition['content'] );
					$processed[$groupId]++;
				}

				$changesForGroup->removeLanguageChanges( $languageCode );

				$cache = new MessageGroupCache( $groupId, $languageCode );
				$cache->create();
			}
		}

		// Remove groups where everything was imported
		$changeData = array_filter( $changeData, function ( MessageSourceChange $change ) {
			return $change->getModifications() !== [];
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
}
