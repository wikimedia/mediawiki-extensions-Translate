<?php
/**
 * Script to gather translator stats.
 *
 * @author Niklas Laxström
 * @license GPL-2.0+
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
		$dbr = wfGetDB( DB_SLAVE );
		$users = $dbr->select(
			array( 'user', 'user_groups' ),
			array(
				'user_name',
				'user_registration',
				'user_editcount',
				'ug_group',
			),
			array(
				'user_registration is not null'
			),
			__METHOD__,
			array(
				'ORDER BY' => 'user_id ASC',
			),
			array(
				'user_groups' => array(
					'LEFT JOIN',
					array( 'user_id=ug_user', 'ug_group' => 'translator' )
				)
			)
		);

		echo "username\tregistration ts\tedit count\tis translator?\tpromoted ts\tmethod\n";

		$rejected = $dbr->select(
			array( 'logging' ),
			array(
				'log_title',
				'log_timestamp',
			),
			array(
				'log_type' => 'translatorsandbox',
				'log_action' => 'rejected',
			),
			__METHOD__
		);

		foreach ( $rejected as $r ) {
			echo "{$r->log_title}\t{$r->log_timestamp}\t0\t\t\tsandbox\n";
		}

		foreach ( $users as $u ) {
			$logs = $dbr->select(
				'logging',
				array(
					'log_type',
					'log_action',
					'log_timestamp',
					'log_params',
				),
				array(
					'log_title' => $u->user_name,
					'log_type' => array( 'rights', 'translatorsandbox' ),
				),
				__METHOD__,
				array(
					'ORDER BY' => 'log_id ASC',
				)
			);

			$promoted = null;
			$method = 'normal';
			foreach ( $logs as $log ) {
				if ( $log->log_action === 'promoted' ) {
					$promoted = $log->log_timestamp;
					$method = 'sandbox';
					break;
				} elseif ( $log->log_action === 'rights' ) {
					wfSuppressWarnings();
					$data = unserialize( $log->log_params );
					wfRestoreWarnings();
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

$maintClass = 'TS';
require_once RUN_MAINTENANCE_IF_MAIN;
