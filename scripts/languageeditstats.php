<?php
/**
 * Shows a top list of language codes with edits in a given time period
 *
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @copyright Copyright © 2008-2010 Niklas Laxström, Siebrand Mazeland
 * @license GPL-2.0-or-later
 * @file
 * @ingroup Script Stats
 */

// Standard boilerplate to define $IP
use MediaWiki\Extension\Translate\SystemUsers\FuzzyBot;
use MediaWiki\Extension\Translate\Utilities\Utilities;
use MediaWiki\MediaWikiServices;

if ( getenv( 'MW_INSTALL_PATH' ) !== false ) {
	$IP = getenv( 'MW_INSTALL_PATH' );
} else {
	$dir = __DIR__;
	$IP = "$dir/../../..";
}
require_once "$IP/maintenance/Maintenance.php";

class Languageeditstats extends Maintenance {
	public function __construct() {
		parent::__construct();
		$this->addDescription( 'Script to show number of edits per language for all message groups.' );
		$this->addOption(
			'top',
			'(optional) Show given number of language codes (default: 10)',
			false, /*required*/
			true /*has arg*/
		);
		$this->addOption(
			'days',
			'(optional) Calculate for given number of days (default: 7)',
			false, /*required*/
			true /*has arg*/
		);
		$this->addOption(
			'bots',
			'(optional) Include bot edits'
		);
		$this->addOption(
			'ns',
			'(optional) Comma separated list of namespace IDs',
			false, /*required*/
			true /*has arg*/
		);
		$this->requireExtension( 'Translate' );
	}

	public function execute() {
		$hours = ( $this->getOption( 'days' ) ?: 7 ) * 24;
		$top = (int)$this->getOption( 'top' ) ?: 10;
		$bots = $this->hasOption( 'bots' );

		$namespaces = [];
		if ( $this->hasOption( 'ns' ) ) {
			$input = explode( ',', $this->getOption( 'ns' ) );

			foreach ( $input as $namespace ) {
				if ( is_numeric( $namespace ) ) {
					$namespaces[] = $namespace;
				}
			}
		}

		/**
		 * Select set of edits to report on
		 */
		$rows = $this->translationChanges( $hours, $bots, $namespaces );

		/**
		 * Get counts for edits per language code after filtering out edits by FuzzyBot
		 */
		$codes = [];
		foreach ( $rows as $_ ) {
			// Filter out edits by FuzzyBot
			if ( $_->rc_user_text === FuzzyBot::getName() ) {
				continue;
			}

			[ , $code ] = Utilities::figureMessage( $_->rc_title );

			if ( !isset( $codes[$code] ) ) {
				$codes[$code] = 0;
			}

			$codes[$code]++;
		}

		/**
		 * Sort counts and report descending up to $top rows.
		 */
		arsort( $codes );
		$i = 0;
		foreach ( $codes as $code => $num ) {
			if ( $i++ === $top ) {
				break;
			}

			$this->output( "$code\t$num\n" );
		}
	}

	/**
	 * Fetches recent changes for titles in given namespaces
	 *
	 * @param int $hours Number of hours.
	 * @param bool $bots Should bot edits be included.
	 * @param int[] $ns List of namespace IDs.
	 * @return \stdClass[] List of recent changes.
	 */
	private function translationChanges( int $hours, bool $bots, array $ns ): array {
		global $wgTranslateMessageNamespaces;

		$mwServices = MediaWikiServices::getInstance();
		$dbr = $mwServices->getDBLoadBalancer()->getConnection( DB_REPLICA );

		$cutoff_unixtime = time() - ( $hours * 3600 );
		$cutoff = $dbr->timestamp( $cutoff_unixtime );

		$conds = [
			'rc_timestamp >= ' . $dbr->addQuotes( $cutoff ),
			'rc_namespace' => $ns ?: $wgTranslateMessageNamespaces,
		];
		if ( $bots ) {
			$conds['rc_bot'] = 0;
		}

		$res = $dbr->select(
			[ 'recentchanges', 'actor' ],
			[
				'rc_namespace', 'rc_title', 'rc_timestamp',
				'rc_user_text' => 'actor_name',
			],
			$conds,
			__METHOD__,
			[],
			[ 'actor' => [ 'JOIN', 'actor_id=rc_actor' ] ]
		);
		$rows = iterator_to_array( $res );

		// Calculate 'lang', then sort by it and rc_timestamp
		foreach ( $rows as &$row ) {
			$pos = strrpos( $row->rc_title, '/' );
			$row->lang = $pos === false ? $row->rc_title : substr( $row->rc_title, $pos + 1 );
		}
		unset( $row );

		usort( $rows, static function ( $a, $b ) {
			$x = strcmp( $a->lang, $b->lang );
			if ( !$x ) {
				// descending order
				$x = strcmp(
					wfTimestamp( TS_MW, $b->rc_timestamp ),
					wfTimestamp( TS_MW, $a->rc_timestamp )
				);
			}
			return $x;
		} );

		return $rows;
	}
}

$maintClass = Languageeditstats::class;
require_once RUN_MAINTENANCE_IF_MAIN;
