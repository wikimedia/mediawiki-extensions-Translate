<?php
/**
 * This file contains a unmanaged message group implementation.
 *
 * @file
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @copyright Copyright © 2008-2012, Niklas Laxström, Siebrand Mazeland
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

/**
 * Wraps the translatable page sections into a message group.
 * @ingroup PageTranslation MessageGroup
 */
class WikiPageMessageGroup extends WikiMessageGroup {
	protected $title;

	public function __construct( $id, $source ) {
		$this->id = $id;
		$this->title = $source;
		$this->namespace = NS_TRANSLATIONS;
	}

	/// Defaults to wiki content language.
	public function getSourceLanguage() {
		global $wgLanguageCode;

		return $wgLanguageCode;
	}

	/**
	 * @return Title
	 */
	public function getTitle() {
		if ( is_string( $this->title ) ) {
			$this->title = Title::newFromText( $this->title );
		}
		return $this->title;
	}

	/**
	 * @return array
	 */
	public function getDefinitions() {
		// Avoid replication issues
		$dbr = wfGetDB( DB_MASTER );
		$tables = 'translate_sections';
		$vars = array( 'trs_key', 'trs_text' );
		$conds = array( 'trs_page' => $this->getTitle()->getArticleID() );
		$options = array( 'ORDER BY' => 'trs_order' );
		$res = $dbr->select( $tables, $vars, $conds, __METHOD__, $options );

		$defs = array();
		$prefix = $this->getTitle()->getPrefixedDBKey() . '/';
		$re = '~<tvar\|([^>]+)>(.*?)</>~u';

		foreach ( $res as $r ) {
			/// @todo: use getTextForTrans?
			$text = $r->trs_text;
			$text = preg_replace( $re, '$\1', $text );
			$defs[$r->trs_key] = $text;
		}

		$new_defs = array();
		foreach ( $defs as $k => $v ) {
			$k = str_replace( ' ', '_', $k );
			$new_defs[$prefix . $k] = $v;
		}

		return $new_defs;
	}

	public function load( $code ) {
		if ( $this->isSourceLanguage( $code ) ) {
			return $this->getDefinitions();
		}

		return array();
	}

	/**
	 * Returns of stored translation of message specified by the $key in language
	 * code $code.
	 *
	 * @param $key \string Key of the message.
	 * @param $code \string Language code.
	 * @return \mixed Stored translation or null.
	 */
	public function getMessage( $key, $code ) {
		if ( $this->isSourceLanguage( $code ) ) {
			$stuff = $this->load( $code );
			return isset( $stuff[$key] ) ? $stuff[$key] : null;
		}

		$title = Title::makeTitleSafe( $this->getNamespace(), "$key/$code" );
		$rev = Revision::newFromTitle( $title );

		if ( !$rev ) {
			return null;
		}

		return $rev->getText();
	}

	/**
	 * @return MediaWikiMessageChecker
	 */
	public function getChecker() {
		$checker = new MediaWikiMessageChecker( $this );
		$checker->setChecks( array(
			array( $checker, 'pluralCheck' ),
			array( $checker, 'XhtmlCheck' ),
			array( $checker, 'braceBalanceCheck' ),
			array( $checker, 'pagenameMessagesCheck' ),
			array( $checker, 'miscMWChecks' )
		) );

		return $checker;
	}

	public function getDescription() {
		$title = $this->title;
		$target = SpecialPage::getTitleFor( 'MyLanguage', $title )->getPrefixedText();

		// Allow for adding a custom group description by using
		// "MediaWiki:Tp-custom-<group ID>".
		$customText = '';
		$customMessage = wfMessage( 'tp-custom-' . $this->id )->inContentLanguage();
		if ( $customMessage->exists() ) {
			$customText = $customMessage->plain();
		}

		return wfMessage( 'translate-tag-page-desc', $title, $target )->plain() . $customText;
	}
}
