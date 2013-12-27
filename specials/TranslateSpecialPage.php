<?php
/**
 * Contains logic for all special pages of the Translate extension
 *
 * @file
 * @author Siebrand Mazeland
 * @copyright Copyright © 2013 Siebrand Mazeland
 * @license GPL-2.0+
 */

/**
 * A special page that all special pages of the Translate extension should use.
 *
 * @ingroup SpecialPage TranslateSpecialPage
 */
class TranslateSpecialPage extends SpecialPage {
	/**
	 * Get a self-referential title object
	 *
	 * For backward compatibility for https://gerrit.wikimedia.org/r/#/c/103587.
	 * Should be removed when the lowest supported version is MediaWiki 1.23.
	 *
	 * @param string|bool $subpage
	 * @return Title|void
	 */
	public function getTitle( $subpage = false ) {
		if ( method_exists( $this, 'getPageTitle' ) ) {
			return $this->getPageTitle();
		} else {
			return self::getTitleFor( $this->mName, $subpage );
		}
	}
}
