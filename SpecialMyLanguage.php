<?php
/**
 * Special page just to redirect the user to translated version if page,
 * if it exists.
 *
 * Usage: [[Special:MyLanguage/Page name|link text]]
 *
 * @author Niklas Laxström
 * 
 * Copyright © 2010 Niklas Laxström, Siebrand Mazeland
 * http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

class SpecialMyLanguage extends UnlistedSpecialPage {
	/**
	 * Construct
	 */
	public function __construct() {
		parent::__construct( 'MyLanguage' );
	}

	/**
	 * Execute method
	 */
	public function execute( $par ) {
		global $wgOut, $wgLang;

		$title = null;
		if ( strval( $par ) !== '' ) {
			$title = Title::newFromText( $par );
			if ( $title && $title->exists() ) {
				$local = Title::newFromText( "$par/" . $wgLang->getCode() );
				if ( $local && $local->exists() ) {
					$title = $local;
				}
			}
		}

		/**
		 * Go to the main page if given invalid title.
		 */
		if ( !$title ) {
			$title = Title::newMainPage();
		}

		$wgOut->redirect( $title->getLocalURL() );
	}
}
