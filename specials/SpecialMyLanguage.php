<?php
/**
 * Contains logic for special page Special:MyLanguage
 *
 * @file
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @copyright Copyright © 2010-2013 Niklas Laxström, Siebrand Mazeland
 * @license GPL-2.0+
 */

/**
 * Unlisted special page just to redirect the user to the translated version of
 * a page, if it exists.
 *
 * Usage: [[Special:MyLanguage/Page name|link text]]
 *
 * @ingroup SpecialPage TranslateSpecialPage
 */
class SpecialMyLanguage extends TranslateSpecialPage {
	public function __construct() {
		parent::__construct( 'MyLanguage' );
	}

	public function isListed() {
		return false;
	}

	/// Only takes arguments from $par
	public function execute( $par ) {
		$title = $this->findTitle( $par );
		// Go to the main page if given invalid title.
		if ( !$title ) {
			$title = Title::newMainPage();
		}

		$this->getOutput()->redirect( $title->getLocalURL() );
	}

	/**
	 * Assuming the user's interface language is fi. Given input Page, it
	 * returns Page/fi if it exists, otherwise Page. Given input Page/de,
	 * it returns Page/fi if it exists, otherwise Page/de if it exists,
	 * otherwise Page.
	 * @param $par
	 * @return Title|null
	 */
	protected function findTitle( $par ) {
		global $wgLanguageCode;
		// base = title without language code suffix
		// provided = the title as it was given
		$base = $provided = Title::newFromText( $par );

		if ( strpos( $par, '/' ) !== false ) {
			$pos = strrpos( $par, '/' );
			$basepage = substr( $par, 0, $pos );
			$code = substr( $par, $pos + 1 );
			$codes = Language::fetchLanguageNames();
			if ( isset( $codes[$code] ) ) {
				$base = Title::newFromText( $basepage );
			}
		}

		if ( !$base ) {
			return null;
		}

		$uiCode = $this->getLanguage()->getCode();
		$proposed = Title::newFromText( $base->getPrefixedText() . "/$uiCode" );
		if ( $uiCode !== $wgLanguageCode && $proposed && $proposed->exists() ) {
			return $proposed;
		} elseif ( $provided && $provided->exists() ) {
			return $provided;
		} else {
			return $base;
		}
	}
}
