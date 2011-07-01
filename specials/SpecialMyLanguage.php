<?php
/**
 * Contains logic for special page Special:MyLanguage
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2010-2011 Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

/**
 * Unlisted special page just to redirect the user to the translated version of
 * a page, if it exists.
 *
 * Usage: [[Special:MyLanguage/Page name|link text]]
 *
 * @ingroup SpecialPage TranslateSpecialPage
 */
class SpecialMyLanguage extends UnlistedSpecialPage {

	public function __construct() {
		parent::__construct( 'MyLanguage' );
	}

	/// Only takes arguments from $par
	public function execute( $par ) {
		global $wgOut, $wgLang;

		$title = null;
		if ( strval( $par ) !== '' ) {
			$title = Title::newFromText( $par );
			if ( $title && $title->exists() && $wgLang->getCode() !== 'en' ) {
				$local = Title::newFromText( "$par/" . $wgLang->getCode() );
				if ( $local && $local->exists() ) {
					$title = $local;
				}
			}
		}

		// Go to the main page if given invalid title.
		if ( !$title ) {
			$title = Title::newMainPage();
		}

		$wgOut->redirect( $title->getLocalURL() );
	}

	/**
	 * Make Special:MyLanguage links red if the target page doesn't exists.
	 * A bit hacky because the core code is not so flexible.
	 */
	public static function linkfix( $dummy, $target, &$html, &$customAttribs, &$query, &$options, &$ret ) {
		if ( $target->getNamespace() == NS_SPECIAL ) {
			list( $name, $subpage ) = SpecialPage::resolveAliasWithSubpage( $target->getDBkey() );
			if ( $name === 'MyLanguage' ) {
				$realTarget = Title::newFromText( $subpage );
				if ( !$realTarget || !$realTarget->exists() ) {
					$options[] = 'broken';
					$index = array_search( 'known', $options, true );
					if ( $index !== false ) unset( $options[$index] );

					$index = array_search( 'noclasses', $options, true );
					if ( $index !== false ) unset( $options[$index] );
				}
			}
		}
		return true;
	}
}
