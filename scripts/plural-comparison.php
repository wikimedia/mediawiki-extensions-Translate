<?php
/**
 * Script for comparing different plural implementations.
 *
 * @author Niklas Laxström
 *
 * @copyright Copyright © 2010, Niklas Laxström
 * @license GPL-2.0-or-later
 * @file
 */

use CLDRPluralRuleParser\Evaluator;
use MediaWiki\Languages\LanguageNameUtils;
use MediaWiki\Maintenance\Maintenance;
use MediaWiki\MediaWikiServices;

// Standard boilerplate to define $IP
if ( getenv( 'MW_INSTALL_PATH' ) !== false ) {
	$IP = getenv( 'MW_INSTALL_PATH' );
} else {
	$IP = __DIR__ . '/../../..';
}
require_once "$IP/maintenance/Maintenance.php";

/// Script for comparing different plural implementations.
class PluralCompare extends Maintenance {
	public function __construct() {
		parent::__construct();
		$this->addDescription( 'Script for comparing different plural implementations.' );
	}

	public function execute() {
		$mwLanguages = $this->loadMediaWiki();
		$gtLanguages = $this->loadGettext();
		$clLanguages = $this->loadCLDR();

		$services = MediaWikiServices::getInstance();
		$all = $services
			->getLanguageNameUtils()
			->getLanguageNames( LanguageNameUtils::AUTONYMS, LanguageNameUtils::ALL );
		$allkeys = array_keys( $all + $mwLanguages + $gtLanguages + $clLanguages );
		sort( $allkeys );
		$languageFallback = $services->getLanguageFallback();

		$this->output( sprintf( "%12s %3s %3s %4s\n", 'Code', 'MW', 'Get', 'CLDR' ) );
		foreach ( $allkeys as $code ) {
			$mw = isset( $mwLanguages[$code] ) ? '+' : '';
			$gt = isset( $gtLanguages[$code] ) ? '+' : '';
			$cl = isset( $clLanguages[$code] ) ? '+' : '';

			if ( $mw === '' ) {
				$fallbacks = $languageFallback->getAll( $code );
				foreach ( $fallbacks as $fcode ) {
					if ( $fcode !== 'en' && isset( $mwLanguages[$fcode] ) ) {
						$mw = '.';
					}
				}
			}

			$error = '';
			if ( substr_count( sprintf( '%s%s%s', $mw, $gt, $cl ), '+' ) > 1 ) {
				$error = $this->tryMatch( $code, $mw, $gtLanguages, $clLanguages );
			}

			$this->output( sprintf( "%12s %-3s %-3s %-4s %s\n", $code, $mw, $gt, $cl, $error ) );
		}
	}

	protected function tryMatch( $code, $mws, $gtLanguages, $clLanguages ) {
		if ( $mws !== '' ) {
			$mwExp = true;
			$lang = MediaWikiServices::getInstance()->getLanguageFactory()->getLanguage( $code );
		} else {
			$mwExp = false;
		}

		if ( isset( $gtLanguages[$code] ) ) {
			$gtExp = 'return (int) ' . str_replace( 'n', '$i', $gtLanguages[$code] ) . ';';
		} else {
			$gtExp = false;
		}

		$cldrExp = $clLanguages[$code] ?? false;

		for ( $i = 0; $i <= 250; $i++ ) {
			$mw = $gt = $cl = '?';

			if ( $mwExp ) {
				// @phan-suppress-next-line PhanPossiblyUndeclaredVariable
				$exp = $lang->getCompiledPluralRules();
				$mw = Evaluator::evaluateCompiled( $i, $exp );
			}

			if ( $gtExp ) {
				// phpcs:ignore MediaWiki.Usage.ForbiddenFunctions.eval
				$gt = eval( $gtExp );
			}

			if ( $cldrExp ) {
				$cl = Evaluator::evaluate( $i, $cldrExp );
			}

			if ( self::comp( $mw, $gt ) && self::comp( $gt, $cl ) && self::comp( $cl, $mw ) ) {
				continue;
			}

			return "$i: $mw $gt $cl";
		}

		return '';
	}

	public static function comp( $a, $b ) {
		return $a === '?' || $b === '?' || $a === $b;
	}

	protected function loadPluralFile( $fileName ) {
		$doc = new DOMDocument;
		$doc->load( $fileName );
		$rulesets = $doc->getElementsByTagName( 'pluralRules' );
		$plurals = [];
		foreach ( $rulesets as $ruleset ) {
			$codes = $ruleset->getAttribute( 'locales' );
			$rules = [];
			$ruleElements = $ruleset->getElementsByTagName( 'pluralRule' );
			foreach ( $ruleElements as $elt ) {
				$rules[] = $elt->nodeValue;
			}
			foreach ( explode( ' ', $codes ) as $code ) {
				$plurals[$code] = $rules;
			}
		}

		return $plurals;
	}

	public function loadCLDR() {
		global $IP;

		return $this->loadPluralFile( "$IP/languages/data/plurals.xml" );
	}

	public function loadMediaWiki() {
		global $IP;

		$rules = $this->loadPluralFile( "$IP/languages/data/plurals.xml" );
		$rulesMW = $this->loadPluralFile( "$IP/languages/data/plurals-mediawiki.xml" );

		return array_merge( $rules, $rulesMW );
	}

	public function loadGettext() {
		$gtData = file_get_contents( __DIR__ . '/../data/plural-gettext.txt' );
		$gtLanguages = [];
		foreach ( preg_split( '/\n|\r/', $gtData, -1, PREG_SPLIT_NO_EMPTY ) as $line ) {
			[ $code, $rule ] = explode( "\t", $line );
			$rule = preg_replace( '/^.*?plural=/', '', $rule );
			$gtLanguages[$code] = $rule;
		}

		return $gtLanguages;
	}
}

$maintClass = PluralCompare::class;
require_once RUN_MAINTENANCE_IF_MAIN;
