<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Diagnostics;

use MediaWiki\Maintenance\LoggedUpdateMaintenance;

/**
 * Remove redundant values from the translate_metadata table
 * @since 2024.04
 * @license GPL-2.0-or-later
 * @author Abijeet Patro
 */
class RemoveRedundantMessageGroupMetadataMaintenanceScript extends LoggedUpdateMaintenance {
	private const SCRIPT_VERSION = 1;

	public function __construct() {
		parent::__construct();
		$this->addDescription( 'Remove redundant values from the translate_metadata table' );
		$this->requireExtension( 'Translate' );
	}

	/** @inheritDoc */
	protected function getUpdateKey(): string {
		return __CLASS__ . '_v' . self::SCRIPT_VERSION;
	}

	/** @inheritDoc */
	protected function doDBUpdates(): bool {
		$this->output( '... Removing empty values from the translate_metadata table ... ' );
		$dbw = $this->getDB( DB_PRIMARY );

		$dbw->newDeleteQueryBuilder()
			->deleteFrom( 'translate_metadata' )
			->where( [
				'tmd_key' => 'priorityforce',
				'tmd_value' => 'off'
			] )
			->caller( __METHOD__ )
			->execute();

		$dbw->newDeleteQueryBuilder()
			->deleteFrom( 'translate_metadata' )
			->where( [
				'tmd_key' => 'reason',
				'tmd_value' => ''
			] )
			->caller( __METHOD__ )
			->execute();

		$this->output( "done\n" );

		return true;
	}
}
