<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Synchronization;

use Maintenance;
use MediaWiki\Extension\Translate\Services;

/**
 * Clear the contents of the group synchronization cache
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 * @since 2021.01
 */
class ClearGroupSyncCacheMaintenanceScript extends Maintenance {
	public function __construct() {
		parent::__construct();
		$this->addDescription( 'Clear the contents of the group synchronization cache for a single or all groups' );

		$this->addOption(
			'group',
			'(optional) Group Id being cleared',
			false, /*required*/
			true /*has arg*/
		);

		$this->addOption(
			'all',
			'(optional) Clear all groups',
			false, /*required*/
			false /*has arg*/
		);

		$this->requireExtension( 'Translate' );
	}

	public function execute() {
		$this->validateParamsAndArgs();
		$groupId = $this->getOption( 'group' );
		$all = $this->hasOption( 'all' );
		$groupSyncCache = Services::getInstance()->getGroupSynchronizationCache();

		if ( $groupId ) {
			$this->clearGroupFromSync( $groupSyncCache, $groupId );
			$this->output( "Ended synchronization for group: $groupId\n" );
		} elseif ( $all ) {
			// Remove all groups
			$groupsInSync = $groupSyncCache->getGroupsInSync();
			$this->output( 'Found ' . count( $groupsInSync ) . " groups in sync.\n" );
			foreach ( $groupsInSync as $groupId ) {
				$this->clearGroupFromSync( $groupSyncCache, $groupId );
				$this->output( "Ended synchronization for group: $groupId\n" );
			}
		}
	}

	public function validateParamsAndArgs() {
		parent::validateParamsAndArgs();

		$group = $this->getOption( 'group' );
		$all = $this->hasOption( 'all' );

		if ( $all && $group !== null ) {
			$this->fatalError( 'The "all" and "group" options cannot be used together.' );
		}

		if ( !$all && $group === null ) {
			$this->fatalError( 'One of "all" OR "group" options must be specified.' );
		}
	}

	private function clearGroupFromSync( GroupSynchronizationCache $groupSyncCache, string $groupId ): void {
		if ( !$groupSyncCache->isGroupBeingProcessed( $groupId ) ) {
			$this->fatalError( "$groupId is currently not being processed" );
		}

		$groupSyncCache->forceEndSync( $groupId );
	}
}
