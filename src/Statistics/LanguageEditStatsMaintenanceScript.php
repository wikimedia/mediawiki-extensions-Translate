<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Statistics;

use MediaWiki\Extension\Translate\SystemUsers\FuzzyBot;
use MediaWiki\Extension\Translate\Utilities\BaseMaintenanceScript;
use MediaWiki\Extension\Translate\Utilities\Utilities;
use MediaWiki\MediaWikiServices;

/**
 * Shows a top list of language codes with edits in a given time period
 *
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @copyright Copyright © 2008-2010 Niklas Laxström, Siebrand Mazeland
 * @license GPL-2.0-or-later
 * @ingroup Script Stats
 */
class LanguageEditStatsMaintenanceScript extends BaseMaintenanceScript {
	public function __construct() {
		parent::__construct();
		$this->addDescription( 'Script to show number of edits per language for all message groups.' );
		$this->addOption(
			'top',
			'(optional) Show given number of language codes (default: 10)',
			self::OPTIONAL,
			self::HAS_ARG
		);
		$this->addOption(
			'days',
			'(optional) Calculate for given number of days (default: 7)',
			self::OPTIONAL,
			self::HAS_ARG
		);
		$this->addOption(
			'bots',
			'(optional) Include bot edits'
		);
		$this->addOption(
			'ns',
			'(optional) Comma separated list of namespace IDs',
			self::OPTIONAL,
			self::HAS_ARG
		);
		$this->requireExtension( 'Translate' );
	}

	public function execute(): void {
		$days = (int)$this->getOption( 'days' ) ?: 7;
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

		// Select set of edits to report on
		$rows = $this->translationChanges( $days, $bots, $namespaces );

		// Get counts for edits per language code
		$codes = [];
		foreach ( $rows as $row ) {
			[ , $code ] = Utilities::figureMessage( $row );

			if ( !isset( $codes[$code] ) ) {
				$codes[$code] = 0;
			}

			$codes[$code]++;
		}

		// Sort counts and report descending up to $top rows.
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
	 * @param int $days Number of hours.
	 * @param bool $bots Should bot edits be included.
	 * @param int[] $ns List of namespace IDs.
	 * @return string[] List of recent changes.
	 */
	private function translationChanges( int $days, bool $bots, array $ns ): array {
		global $wgTranslateMessageNamespaces;
		$dbr = MediaWikiServices::getInstance()->getConnectionProvider()->getReplicaDatabase();

		$cutoff = $dbr->timestamp( time() - ( $days * 24 * 3600 ) );

		$conds = [
			'rc_timestamp >= ' . $dbr->addQuotes( $cutoff ),
			'rc_namespace' => $ns ?: $wgTranslateMessageNamespaces,
			'actor_name <> ' . $dbr->addQuotes( FuzzyBot::getName() )
		];
		if ( $bots ) {
			$conds['rc_bot'] = 0;
		}

		return $dbr->newSelectQueryBuilder()
			->select( [ 'rc_title' ] )
			->from( 'recentchanges' )
			->join( 'actor', null, 'actor_id=rc_actor' )
			->where( $conds )
			->caller( __METHOD__ )
			->fetchFieldValues();
	}
}
