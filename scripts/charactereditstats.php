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
			'(optional) Show given number of language codes (default: 10)',
			false, /*required*/
			true /*has arg*/
		);
		$this->addOption(
			'days',
			'(optional) Calculate for given number of days (default: 30)',
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
		$hours = (int)$this->getOption( 'days' );
		$hours = $hours ? $hours * 24 : 30 * 24;

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

		// Select set of edits to report on
		$rows = TranslateUtils::translationChanges( $hours, $bots, $namespaces );
		// Get counts for edits per language code after filtering out edits by FuzzyBot
		$codes = array();
		global $wgTranslateFuzzyBotName;
		foreach ( $rows as $revId => $_ ) {
			// Filter out edits by $wgTranslateFuzzyBotName
			if ( $_->rc_user_text === $wgTranslateFuzzyBotName ) {
				continue;
			}

			list( , $code ) = TranslateUtils::figureMessage( $_->rc_title );

			if ( !isset( $codes[$code] ) ) {
				$codes[$code] = 0;
			}

			$revision = Revision::newFromId( $revId );

			if ( $revision !== null ) {
				if ( !$this->hasOption( 'diff' ) ) {
					$codes[$code] += $revision->getSize();
					continue;
				}

				$prevRevision = $revision->getPrevious();
				if ( $prevRevision === null ) {
					$diff = $revision->getSize();
				} else {
					$diff = $prevRevision->getSize() - $revision->getSize();
					$diff = $diff >= 0 ? $diff : $diff * -1;
				}
				$codes[$code] += $diff;
			}
		}

		// Sort counts and report descending up to $top rows.
		arsort( $codes );
		$i = 0;
		$total = 0;
		$this->output( "code\tname\tedit\n" );
		$this->output( "-----------------------\n" );
		foreach ( $codes as $code => $num ) {
			if ( $i++ === $top ) {
				break;
			}
			$language = Language::fetchLanguageName( $code );
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
