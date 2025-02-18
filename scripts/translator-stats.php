<?php
/**
 * Script to gather translator stats.
 *
 * @author Niklas Laxström
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

class TS extends Maintenance {
	public function __construct() {
		parent::__construct();
		$this->addDescription( 'Script to gather translator stats in tsv format. ' .
			'You can further process the output with translator-stats-process.php' );
	}

	public function execute() {
		$dbr = $this->getDB( DB_REPLICA );

		$users = $dbr->newSelectQueryBuilder()
			->select( [
				'user_name',
				'user_registration',
				'user_editcount',
				'ug_group',
			] )
			->from( 'user' )
			->leftJoin( 'user_groups', null, [
				'user_id=ug_user',
				'ug_group' => 'translator',
				'ug_expiry IS NULL OR ug_expiry >= ' . $dbr->addQuotes( $dbr->timestamp() ),
			] )
			->where( [
				'user_registration is not null'
			] )
			->orderBy( 'user_id' )
			->caller( __METHOD__ )
			->fetchResultSet();

		echo "username\tregistration ts\tedit count\tis translator?\tpromoted ts\tmethod\n";

		$rejected = $dbr->newSelectQueryBuilder()
			->select( [
				'log_title',
				'log_timestamp',
			] )
			->from( 'logging' )
			->where( [
				'log_type' => 'translatorsandbox',
				'log_action' => 'rejected',
			] )
			->caller( __METHOD__ )
			->fetchResultSet();

		foreach ( $rejected as $r ) {
			echo "{$r->log_title}\t{$r->log_timestamp}\t0\t\t\tsandbox\n";
		}

		foreach ( $users as $u ) {
			$logs = $dbr->newSelectQueryBuilder()
				->select( [
					'log_type',
					'log_action',
					'log_timestamp',
					'log_params',
				] )
				->from( 'logging' )
				->where( [
					'log_title' => $u->user_name,
					'log_type' => [ 'rights', 'translatorsandbox' ],
				] )
				->orderBy( 'log_id' )
				->caller( __METHOD__ )
				->fetchResultSet();

			$promoted = '';
			$method = 'normal';
			foreach ( $logs as $log ) {
				if ( $log->log_action === 'promoted' ) {
					$promoted = $log->log_timestamp;
					$method = 'sandbox';
					break;
				} elseif ( $log->log_action === 'rights' ) {
					// phpcs:disable Generic.PHP.NoSilencedErrors.Discouraged
					$data = @unserialize( $log->log_params );
					if ( $data === false ) {
						$lines = explode( "\n", $log->log_params, 3 );
						if ( str_contains( $lines[1], 'translator' ) ) {
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
