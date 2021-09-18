<?php
/**
 * Creates serialised database of messages that need checking for problems.
 *
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @copyright Copyright © 2008-2013, Niklas Laxström, Siebrand Mazeland
 * @license GPL-2.0-or-later
 * @file
 */

// Standard boilerplate to define $IP
use MediaWiki\MediaWikiServices;

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
		$this->addDescription( 'Creates serialised database of messages that need ' .
			'checking for problems.' );
		$this->addOption(
			'group',
			'Comma separated list of group IDs to process (can use * as wildcard).',
			true, /*required*/
			true /*has arg*/
		);

		$this->addOption(
			'verbose',
			'(optional) Enable verbose logging. Default: off',
			false, /*required*/
			false /*has arg*/
		);
		$this->requireExtension( 'Translate' );
	}

	public function execute() {
		$codes = Language::fetchLanguageNames( null, Language::ALL );

		// Exclude the documentation language code
		global $wgTranslateDocumentationLanguageCode;
		if ( $wgTranslateDocumentationLanguageCode ) {
			unset( $codes[$wgTranslateDocumentationLanguageCode] );
		}

		$reqGroupsPattern = $this->getOption( 'group' );
		$reqGroups = explode( ',', $reqGroupsPattern );
		$reqGroups = array_map( 'trim', $reqGroups );
		$reqGroups = MessageGroups::expandWildcards( $reqGroups );

		$verbose = $this->hasOption( 'verbose' );

		if ( !$reqGroups ) {
			$this->fatalError( "Pattern '$reqGroupsPattern' did not match any groups" );
		}

		$groups = MessageGroups::singleton()->getGroups();
		$contLang = MediaWikiServices::getInstance()->getContentLanguage();

		/** @var MessageGroup $g */
		foreach ( $reqGroups as $id ) {
			$g = MessageGroups::getGroup( $id );
			// Aliases may have changed the id
			$id = $g->getId();
			$sourceLanguage = $g->getSourceLanguage();

			$validator = $g->getValidator();
			if ( !$validator ) {
				unset( $g );
				$this->output( "Skipping group $id due to lack of validators" );
				continue;
			}

			// Initialise messages, using unique definitions if appropriate
			// @phan-suppress-next-line PhanParamTooMany MessageGroupOld takes two args
			$collection = $g->initCollection( $sourceLanguage, true );
			if ( !count( $collection ) ) {
				continue;
			}

			$this->output( "Processing group $id: ", $id );

			// Skip source language code
			$langCodes = $codes;
			unset( $langCodes[$sourceLanguage] );

			$langCodes = array_keys( $langCodes );
			sort( $langCodes );

			foreach ( $langCodes as $code ) {
				$this->output( "$code ", $id );

				$problematic = [];

				$collection->resetForNewLanguage( $code );
				$collection->loadTranslations();
				$collection->filter( 'ignored' );
				$collection->filter( 'fuzzy' );
				$collection->filter( 'translated', false );

				foreach ( $collection as $key => $message ) {
					$result = $validator->quickValidate( $message, $code );
					if ( $result->hasIssues() ) {
						if ( $verbose ) {
							// Print it
							$nsText = $contLang->getNsText( $g->getNamespace() );
							$this->output( "# [[$nsText:$key/$code]]\n" );
						}

						// Add it to the array
						$problematic[] = [ $g->getNamespace(), "$key/$code" ];
					}
				}

				self::tagFuzzy( $problematic );
			}
		}
	}

	public static function tagFuzzy( array $problematic ): void {
		if ( $problematic === [] ) {
			return;
		}

		$titleConditions = [];
		$dbw = wfGetDB( DB_PRIMARY );

		foreach ( $problematic as $p ) {
			// Normalize page key
			$title = Title::makeTitleSafe( $p[0], $p[1] );
			$titleText = $title->getDBkey();
			$titleConditions[] = $dbw->makeList(
				[
					'page_namespace' => $p[0],
					'page_title' => $titleText
				],
				LIST_AND
			);
		}

		$conds = $dbw->makeList( $titleConditions, LIST_OR );

		$res = $dbw->select( 'page', [ 'page_id', 'page_latest' ], $conds, __METHOD__ );
		$inserts = [];
		foreach ( $res as $row ) {
			$inserts[] = [
				'rt_page' => $row->page_id,
				'rt_revision' => $row->page_latest,
				'rt_type' => RevTag::getType( 'fuzzy' )
			];
		}
		$dbw->replace( 'revtag', 'rt_type_page_revision', $inserts, __METHOD__ );
	}
}

$maintClass = CreateCheckIndex::class;
require_once RUN_MAINTENANCE_IF_MAIN;
