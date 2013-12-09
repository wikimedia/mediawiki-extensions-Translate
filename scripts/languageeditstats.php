<?php
/**
 * Shows a top list of language codes with edits in a given time period
 *
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @copyright Copyright © 2008-2010 Niklas Laxström, Siebrand Mazeland
 * @license GPL-2.0+
 * @file
 * @ingroup Script Stats
 */

// Standard boilerplate to define $IP
if ( getenv( 'MW_INSTALL_PATH' ) !== false ) {
	$IP = getenv( 'MW_INSTALL_PATH' );
} else {
	$dir = __DIR__;
	$IP = "$dir/../../..";
}
require_once "$IP/maintenance/Maintenance.php";

class LanguageEditStats extends Maintenance {
	public function __construct() {
		parent::__construct();
		$this->mDescription = 'Script to show number of edits per language for all message groups.';
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
	}

	public function execute() {
		$hours = (int)$this->getOption( 'days' );
		$hours = $hours ? $hours * 7 : 7 * 24;

		$top = (int)$this->getOption( 'top' );
		$top = $top ? $top : 10;

		$bots = $this->hasOption( 'bots' );

		$namespaces = array();
		if ( $this->hasOption( 'ns' ) ) {
			$input = explode( ',', $this->getOption( 'ns' ) );

			foreach ( $input as $namespace ) {
				if ( is_numeric( $namespace ) ) {
					array_push( $namespaces, $namespace );
				}
			}
		}

		/**
		 * Select set of edits to report on
		 */
		$rows = TranslateUtils::translationChanges( $hours, $bots, $namespaces );

		/**
		 * Get counts for edits per language code after filtering out edits by FuzzyBot
		 */
		$codes = array();
		global $wgTranslateFuzzyBotName;
		foreach ( $rows as $_ ) {
			// Filter out edits by $wgTranslateFuzzyBotName
			if ( $_->rc_user_text === $wgTranslateFuzzyBotName ) {
				continue;
			}

			list( , $code ) = TranslateUtils::figureMessage( $_->rc_title );

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
}

$maintClass = 'LanguageEditStats';
require_once RUN_MAINTENANCE_IF_MAIN;
