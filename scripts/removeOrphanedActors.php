<?php
/**
 * A script to remove orphaned actors whose users are no longer
 * present in the user table. See T225999
 *
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 * @file
 */

// Standard boilerplate to define $IP
if ( getenv( 'MW_INSTALL_PATH' ) !== false ) {
	$IP = getenv( 'MW_INSTALL_PATH' );
} else {
	$dir = __DIR__;
	$IP = "$dir/../../..";
}
require_once "$IP/maintenance/Maintenance.php";

class RemoveOrphanedActors extends Maintenance {
	public function __construct() {
		parent::__construct();
		$this->addDescription( 'A script to remove orphaned actor records.' );
	}

	public function execute() {
		$dbw = wfGetDB( DB_MASTER );
		// Delete orphaned users
		$dbw->delete(
			'actor',
			[
				'actor_user NOT IN ( SELECT user_id FROM user ) AND actor_user IS NOT NULL',
			]
		);

		$this->output( "deleted orphaned actors\n" );

		// Update the site stats
		$users = $dbw->selectField( 'user', 'COUNT(*)', [], __METHOD__ );
		$dbw->update(
			'site_stats',
			[ 'ss_users' => $users ],
			[ 'ss_row_id' => 1 ],
			__METHOD__
		);

		$this->output( "updated the site stats\n" );
	}
}

$maintClass = RemoveOrphanedActors::class;
require_once RUN_MAINTENANCE_IF_MAIN;
