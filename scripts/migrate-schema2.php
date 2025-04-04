<?php
/**
 * Script to convert Translate extension database schema to v2
 *
 * @author Niklas Laxström
 * @copyright Copyright © 2011, Niklas Laxström
 * @license GPL-2.0-or-later
 * @file
 */

use MediaWiki\Maintenance\Maintenance;

// Standard boilerplate to define $IP
if ( getenv( 'MW_INSTALL_PATH' ) !== false ) {
	$IP = getenv( 'MW_INSTALL_PATH' );
} else {
	$dir = __DIR__;
	$IP = "$dir/../../..";
}
require_once "$IP/maintenance/Maintenance.php";

/**
 * Script to convert Translate extension database schema to v2.
 * Essentially gets rid of revtag_type table, which was unnecessary
 * abstraction.
 */
class TSchema2 extends Maintenance {

	public function __construct() {
		parent::__construct();
		$this->addDescription( 'Migrates database schema to version 2.' );
		$this->requireExtension( 'Translate' );
	}

	public function execute() {
		$dbw = $this->getDB( DB_PRIMARY );

		if ( !$dbw->tableExists( 'revtag', __METHOD__ ) ) {
			$this->fatalError( "Table revtag doesn't exist. Translate extension is not installed?" );
		}

		if ( !$dbw->tableExists( 'revtag_type', __METHOD__ ) ) {
			$this->fatalError( "Table revtag_type doesn't exist. Migration is already done." );
		}

		if ( $dbw->getType() !== 'mysql' ) {
			$this->error( 'This migration script only supports mysql. Please help ' .
				"us to write routine for {$dbw->getType()}.", 1 );
		}

		$table = $dbw->tableName( 'revtag' );
		$dbw->query( "ALTER TABLE $table MODIFY rt_type varbinary(60) not null", __METHOD__ );

		$res = $dbw->newSelectQueryBuilder()
			->select( [ 'rtt_id', 'rtt_name' ] )
			->from( 'revtag_type' )
			->caller( __METHOD__ )
			->fetchResultSet();

		foreach ( $res as $row ) {
			$dbw->newUpdateQueryBuilder()
				->update( 'revtag' )
				->set( [ 'rt_type' => $row->rtt_name ] )
				->where( [ 'rt_type' => (string)$row->rtt_id ] )
				->caller( __METHOD__ )
				->execute();
		}

		$dbw->dropTable( 'revtag_type', __METHOD__ );
	}
}

$maintClass = TSchema2::class;
require_once RUN_MAINTENANCE_IF_MAIN;
