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

		$this->addOption(
			'really',
			'(optional) Really delete, no dry-run',
			false,
			false
		);
	}

	public function execute() {
		$dbw = wfGetDB( DB_PRIMARY );

		$orphanedActors = $dbw->select(
			[ 'actor', 'user' ],
			[ 'actor_id', 'actor_name', 'actor_user' ],
			[ 'user_id IS NULL', 'actor_user IS NOT NULL' ],
			__METHOD__,
			[],
			[ 'user' => [ 'LEFT JOIN', [ 'actor_user = user_id' ] ] ]
		);

		$this->output( 'found ' . $orphanedActors->numRows() . " orphaned actors...\n" );

		if ( $orphanedActors->numRows() === 0 ) {
			$this->output( "nothing to delete ... exit!\n" );
			return;
		}

		$orphanedActorIds = [];

		$this->output( "----\n" );
		foreach ( $orphanedActors as $actor ) {
			$this->output(
				"Actor: '$actor->actor_name'; " .
				"Actor Id: $actor->actor_id; " .
				"Actor User: $actor->actor_user;\n"
			);

			$orphanedActorIds[] = $actor->actor_id;
		}
		$this->output( "----\n" );

		if ( !$this->hasOption( 'really' ) ) {
			$this->output( "dry run...exiting!\n" );
			return;
		}

		// Delete orphaned actors
		$dbw->delete( 'actor', [ 'actor_id' => $orphanedActorIds ], __METHOD__ );

		$this->output( "deleted orphaned actors\n" );

		// Update the site stats
		$users = $dbw->selectField( 'user', 'COUNT(*)', [], __METHOD__ );
		$dbw->update(
			'site_stats',
			[ 'ss_users' => $users ],
			[ 'ss_row_id' => 1 ],
			__METHOD__
		);

		$this->output( "updated the site stats for users\n" );
	}
}

$maintClass = RemoveOrphanedActors::class;
require_once RUN_MAINTENANCE_IF_MAIN;
