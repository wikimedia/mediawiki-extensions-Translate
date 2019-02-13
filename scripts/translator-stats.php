<?php
/**
 * Script to gather translator stats.
 *
 * @author Niklas Laxström
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

class TS extends Maintenance {
	public function __construct() {
		parent::__construct();
		$this->mDescription = 'Script to gather translator stats in tsv format. ' .
			'You can further process the output with translate-stats-process.php';
	}

	public function execute() {
		global $wgDisableUserGroupExpiry;

		$dbr = wfGetDB( DB_REPLICA );
		$users = $dbr->select(
			[ 'user', 'user_groups' ],
			[
				'user_name',
				'user_registration',
				'user_editcount',
				'ug_group',
			],
			[
				'user_registration is not null'
			],
			__METHOD__,
			[
				'ORDER BY' => 'user_id ASC',
			],
			[
				'user_groups' => [
					'LEFT JOIN',
					[
						'user_id=ug_user',
						'ug_group' => 'translator',
						( isset( $wgDisableUserGroupExpiry ) && !$wgDisableUserGroupExpiry ) ?
							'ug_expiry IS NULL OR ug_expiry >= ' . $dbr->addQuotes( $dbr->timestamp() ) :
							''
					]
				]
			]
		);

		echo "username\tregistration ts\tedit count\tis translator?\tpromoted ts\tmethod\n";

		$rejected = $dbr->select(
			[ 'logging' ],
			[
				'log_title',
				'log_timestamp',
			],
			[
				'log_type' => 'translatorsandbox',
				'log_action' => 'rejected',
			],
			__METHOD__
		);

		foreach ( $rejected as $r ) {
			echo "{$r->log_title}\t{$r->log_timestamp}\t0\t\t\tsandbox\n";
		}

		foreach ( $users as $u ) {
			$logs = $dbr->select(
				'logging',
				[
					'log_type',
					'log_action',
					'log_timestamp',
					'log_params',
				],
				[
					'log_title' => $u->user_name,
					'log_type' => [ 'rights', 'translatorsandbox' ],
				],
				__METHOD__,
				[
					'ORDER BY' => 'log_id ASC',
				]
			);

			$promoted = null;
			$method = 'normal';
			foreach ( $logs as $log ) {
				if ( $log->log_action === 'promoted' ) {
					$promoted = $log->log_timestamp;
					$method = 'sandbox';
					break;
				} elseif ( $log->log_action === 'rights' ) {
					Wikimedia\suppressWarnings();
					$data = unserialize( $log->log_params );
					Wikimedia\restoreWarnings();
					if ( $data === false ) {
						$lines = explode( "\n", $log->log_params );
						if ( strpos( $lines[1], 'translator' ) !== false ) {
							$promoted = $log->log_timestamp;
							break;
						}
					} elseif (
						isset( $data['5::newgroups'] ) &&
						in_array( 'translator', $data['5::newgroups'] )
					) {
						$promoted = $log->log_timestamp;
						break;
					}
				}
			}

			echo "{$u->user_name}\t{$u->user_registration}\t{$u->user_editcount}" .
			"\t{$u->ug_group}\t{$promoted}\t{$method}\n";
		}
	}
}

$maintClass = TS::class;
require_once RUN_MAINTENANCE_IF_MAIN;
