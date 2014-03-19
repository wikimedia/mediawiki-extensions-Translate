<?php
/**
 * Show number of characters translated over a given period of time.
 *
 * @author Santhosh Thottingal
 * @copyright Copyright 2013 Santhosh Thottingal
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

class CharacterEditStats extends Maintenance {
	public function __construct() {
		parent::__construct();
		$this->mDescription = 'Script to show number of characters translated .';
		$this->addOption(
			'top',
			'(optional) Show given number of language codes (default: show all)',
			false, /*required*/
			true /*has arg*/
		);
		$this->addOption(
			'days',
			'(optional) Calculate for given number of days (default: 30) ' .
			'(capped by the max age of recent changes on the wiki)',
			false, /*required*/
			true /*has arg*/
		);
		$this->addOption(
			'bots',
			'(optional) Include bot edits'
		);
		$this->addOption(
			'diff',
			'(optional) Count the edit diffs alone'
		);
		$this->addOption(
			'ns',
			'(optional) Comma separated list of namespace IDs',
			false, /*required*/
			true /*has arg*/
		);
	}

	public function execute() {
		global $wgTranslateFuzzyBotName, $wgSitename;

		$days = (int)$this->getOption( 'days', 30 );
		$hours = $days * 24;

		$top = (int)$this->getOption( 'top', -1 );

		$bots = $this->hasOption( 'bots' );

		$namespaces = array();
		if ( $this->hasOption( 'ns' ) ) {
			$input = explode( ',', $this->getOption( 'ns' ) );

			foreach ( $input as $namespace ) {
				if ( is_numeric( $namespace ) ) {
					$namespaces[] = $namespace;
				}
			}
		}

		// Select set of edits to report on

		// Fetch some extrac fields that normally TranslateUtils::translationChanges wont
		$extraFields = array( 'rc_old_len', 'rc_new_len' );
		$rows = TranslateUtils::translationChanges( $hours, $bots, $namespaces, $extraFields );
		// Get counts for edits per language code after filtering out edits by FuzzyBot
		$codes = array();

		foreach ( $rows as $_ ) {
			// Filter out edits by $wgTranslateFuzzyBotName
			if ( $_->rc_user_text === $wgTranslateFuzzyBotName ) {
				continue;
			}

			$handle = new MessageHandle( Title::newFromText( $_->rc_title ) );
			$code = $handle->getCode();

			if ( !isset( $codes[$code] ) ) {
				$codes[$code] = 0;
			}

			if ( $this->hasOption( 'diff' ) ) {
				$diff = abs( $_->rc_new_len - $_->rc_old_len );
			} else {
				$diff = $_->rc_new_len;
			}
			$codes[$code] += $diff;
		}

		// Sort counts and report descending up to $top rows.
		arsort( $codes );
		$i = 0;
		$total = 0;
		$this->output( "Character edit stats for last $days days in $wgSitename\n" );
		$this->output( "code\tname\tedit\n" );
		$this->output( "-----------------------\n" );
		foreach ( $codes as $code => $num ) {
			if ( $i++ === $top ) {
				break;
			}
			$language = Language::fetchLanguageName( $code );
			if ( !$language ) {
				// this will be very rare, but avoid division by zero in next line
				continue;
			}
			$charRatio = mb_strlen( $language, 'UTF-8' ) / strlen( $language );
			$num = intval( $num * $charRatio );
			$total += $num;
			$this->output( "$code\t$language\t$num\n" );
		}
		$this->output( "-----------------------\n" );
		$this->output( "Total\t\t$total\n" );
	}
}

$maintClass = 'CharacterEditStats';
require_once RUN_MAINTENANCE_IF_MAIN;
