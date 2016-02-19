<?php

/**
 * Finds external changes for file based message groups.
 *
 * @author Niklas Laxström
 * @license GPL-2.0+
 * @since 2016.02
 */
class ExternalMessageSourceStateImporter {

	public function importSafe( $changeData ) {
		$processed = array();
		$skipped = array();
		$jobs = array();
		$jobs[] = MessageIndexRebuildJob::newJob();

		foreach ( $changeData as $groupId => $changesForGroup ) {
			$group = MessageGroups::getGroup( $groupId );
			if ( !$group ) {
				unset( $changeData[$groupId] );
				continue;
			}

			$processed[$groupId] = 0;

			foreach ( $changesForGroup as $languageCode => $changesForLanguage ) {
				if ( !self::isSafe( $changesForLanguage ) ) {
					$skipped[$groupId] = true;
					continue;
				}

				if ( !isset( $changesForLanguage['addition'] ) ) {
					continue;
				}

				foreach ( $changesForLanguage['addition'] as $addition ) {
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

				unset( $changeData[$groupId][$languageCode] );

				$cache = new MessageGroupCache( $groupId, $languageCode );
				$cache->create();
			}
		}

		// Remove groups where everything was imported
		$changeData = array_filter( $changeData );
		// Remove groups with no imports
		$processed = array_filter( $processed );

		$name = 'unattended';
		$file = MessageChangeStorage::getCdbPath( $name );
		MessageChangeStorage::writeChanges( $changeData, $file );
		JobQueueGroup::singleton()->push( $jobs );

		return array(
			'processed' => $processed,
			'skipped' => $skipped,
			'name' => $name,
		);
	}

	protected static function isSafe( array $changesForLanguage ) {
		foreach ( array_keys( $changesForLanguage ) as $changeType ) {
			if ( $changeType !== 'addition' ) {
				return false;
			}
		}

		return true;
	}
}
