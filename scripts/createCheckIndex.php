<?php
/**
 * Creates serialised database of messages that need checking for problems.
 *
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @copyright Copyright © 2008-2013, Niklas Laxström, Siebrand Mazeland
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

class CreateCheckIndex extends Maintenance {
	public function __construct() {
		parent::__construct();
		$this->mDescription = 'Creates serialised database of messages that need ' .
			'checking for problems.';
		$this->addOption(
			'group',
			'(optional) Comma separated list of group IDs to process (can use * as wildcard). ' .
			'Default: "*"',
			false, /*required*/
			true /*has arg*/
		);
	}

	public function execute() {
		$codes = Language::fetchLanguageNames( false );

		// Exclude the documentation language code
		global $wgTranslateDocumentationLanguageCode;
		if ( $wgTranslateDocumentationLanguageCode ) {
			unset( $codes[$wgTranslateDocumentationLanguageCode] );
		}

		$reqGroups = $this->getOption( 'group' );
		if ( $reqGroups ) {
			$reqGroups = explode( ',', $reqGroups );
			$reqGroups = array_map( 'trim', $reqGroups );
			$reqGroups = MessageGroups::expandWildcards( $reqGroups );
		}

		$verbose = isset( $options['verbose'] );

		$groups = MessageGroups::singleton()->getGroups();

		/** @var $g MessageGroup */
		foreach ( $groups as $g ) {
			$id = $g->getId();
			$sourceLanguage = $g->getSourceLanguage();

			// Skip groups that are not requested
			if ( $reqGroups && !in_array( $id, $reqGroups ) ) {
				unset( $g );
				continue;
			}

			$checker = $g->getChecker();
			if ( !$checker ) {
				unset( $g );
				continue;
			}

			// Initialise messages, using unique definitions if appropriate
			$collection = $g->initCollection( $sourceLanguage, true );
			if ( !count( $collection ) ) {
				continue;
			}

			$this->output( "Working with $id: ", $id );

			// Skip source language code
			$langCodes = $codes;
			unset( $langCodes[$sourceLanguage] );

			$langCodes = array_keys( $langCodes );
			sort( $langCodes );

			foreach ( $langCodes as $code ) {
				$this->output( "$code ", $id );

				$problematic = array();

				$collection->resetForNewLanguage( $code );
				$collection->loadTranslations();

				global $wgContLang;

				foreach ( $collection as $key => $message ) {
					$prob = $checker->checkMessageFast( $message, $code );
					if ( $prob ) {

						if ( $verbose ) {
							// Print it
							$nsText = $wgContLang->getNsText( $g->namespaces[0] );
							$this->output( "# [[$nsText:$key/$code]]\n" );
						}

						// Add it to the array
						$problematic[] = array( $g->namespaces[0], "$key/$code" );
					}
				}

				self::tagFuzzy( $problematic );
			}
		}
	}

	public static function tagFuzzy( $problematic ) {
		if ( !count( $problematic ) ) {
			return;
		}

		$dbw = wfGetDB( DB_MASTER );
		foreach ( $problematic as $p ) {
			$title = Title::makeTitleSafe( $p[0], $p[1] );
			$titleText = $title->getDBkey();
			$res = $dbw->select( 'page', array( 'page_id', 'page_latest' ),
				array( 'page_namespace' => $p[0], 'page_title' => $titleText ), __METHOD__ );

			$inserts = array();
			foreach ( $res as $r ) {
				$inserts = array(
					'rt_page' => $r->page_id,
					'rt_revision' => $r->page_latest,
					'rt_type' => RevTag::getType( 'fuzzy' )
				);
			}
			$dbw->replace( 'revtag', 'rt_type_page_revision', $inserts, __METHOD__ );
		}
	}
}

$maintClass = 'CreateCheckIndex';
require_once RUN_MAINTENANCE_IF_MAIN;
