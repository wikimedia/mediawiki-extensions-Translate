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
			'(optional) Calculate for given number of days (default: 30)',
			false, /*required*/
			true /*has arg*/
		);
		$this->addOption(
			'ns',
			'(optional) Comma separated list of namespace IDs',
			false, /*required*/
			true /*has arg*/
		);
	}

	public function execute() {
		global $wgTranslateFuzzyBotName, $wgSitename, $wgTranslateMessageNamespaces;

		$days = (int)$this->getOption( 'days', 30 );
		$top = (int)$this->getOption( 'top', -1 );

		$namespaces = array();
		if ( $this->hasOption( 'ns' ) ) {
			$input = explode( ',', $this->getOption( 'ns' ) );

			foreach ( $input as $namespace ) {
				if ( is_numeric( $namespace ) ) {
					$namespaces[] = $namespace;
				}
			}
		} else {
			$namespaces = $wgTranslateMessageNamespaces;
		}

		// Select set of edits to report on
		$rows = self::getRevisionsFromHistory( $days, $namespaces );

		// Get counts for edits per language code after filtering out edits by FuzzyBot
		$codes = array();

		foreach ( $rows as $_ ) {
			// Filter out edits by $wgTranslateFuzzyBotName
			if ( $_->user_text === $wgTranslateFuzzyBotName ) {
				continue;
			}

			$handle = new MessageHandle( Title::newFromText( $_->title ) );
			$code = $handle->getCode();

			if ( !isset( $codes[$code] ) ) {
				$codes[$code] = 0;
			}

			$codes[$code] += $_->length;
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
			$num = (int)( $num * $charRatio );
			$total += $num;
			$this->output( "$code\t$language\t$num\n" );
		}
		$this->output( "-----------------------\n" );
		$this->output( "Total\t\t$total\n" );
	}

	private function getRevisionsFromHistory( $days, array $namespaces ) {
		$dbr = wfGetDB( DB_SLAVE );
		$cutoff = $dbr->addQuotes( $dbr->timestamp( time() - $days * 24 * 3600 ) );

		// The field renames are to be compatible with recentchanges table query
		$fields = array(
			'page_title as title',
			'rev_user_text as user_text',
			'rev_len as length',
		);
		$tables = array( 'revision', 'page' );
		$conds = array(
			"rev_timestamp > $cutoff",
			'rev_page = page_id',
			'page_namespace' => $namespaces,
		);

		$res = $dbr->select( $tables, $fields, $conds, __METHOD__ );
		return iterator_to_array( $res );
	}
}

$maintClass = 'CharacterEditStats';
require_once RUN_MAINTENANCE_IF_MAIN;
