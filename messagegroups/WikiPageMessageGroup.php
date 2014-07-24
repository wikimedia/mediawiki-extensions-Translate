<?php
/**
 * This file contains an unmanaged message group implementation.
 *
 * @file
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @license GPL-2.0+
 */

/**
 * Wraps the translatable page sections into a message group.
 * @ingroup PageTranslation MessageGroup
 */
class WikiPageMessageGroup extends WikiMessageGroup {
	/// @var string|Title
	protected $title;

	public function __construct( $id, $source ) {
		$this->id = $id;
		$this->title = $source;
		$this->namespace = NS_TRANSLATIONS;
	}

	public function getSourceLanguage() {
		return $this->getTitle()->getPageLanguage()->getCode();
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
	 * Only used for caching to avoid repeating database queries
	 * for example during message index rebuild.
	 */
	protected $definitions;

	/**
	 * @return array
	 */
	public function getDefinitions() {
		if ( is_array( $this->definitions ) ) {
			return $this->definitions;
		}

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
			$section = new TPSection();
			$section->text = $r->trs_text;
			$defs[$r->trs_key] = $section->getTextWithVariables();
		}

		$new_defs = array();
		foreach ( $defs as $k => $v ) {
			$k = str_replace( ' ', '_', $k );
			$new_defs[$prefix . $k] = $v;
		}

		return $this->definitions = $new_defs;
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
	 * @param string $key Message key
	 * @param string $code Language code
	 * @return string|null Stored translation or null.
	 */
	public function getMessage( $key, $code ) {
		if ( $this->isSourceLanguage( $code ) ) {
			$stuff = $this->load( $code );

			return isset( $stuff[$key] ) ? $stuff[$key] : null;
		}

		$title = Title::makeTitleSafe( $this->getNamespace(), "$key/$code" );
		$rev = Revision::newFromTitle( $title, false, Revision::READ_LATEST );

		if ( !$rev ) {
			return null;
		}

		return ContentHandler::getContentText( $rev->getContent() );
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

	public function getInsertablesSuggester() {
		return new TranslatablePageInsertablesSuggester();
	}

	public function getDescription( IContextSource $context = null ) {
		$title = $this->getTitle()->getPrefixedText();
		$target = ":$title";
		$pageLanguageCode = $this->getSourceLanguage();
		$inLanguageCode = $context ? $context->getLanguage()->getCode() : null;
		$languageName = Language::fetchLanguageName( $pageLanguageCode, $inLanguageCode );

		// Allow for adding a custom group description by using
		// "MediaWiki:Tp-custom-<group ID>".
		$customText = '';
		$msg = wfMessage( 'tp-custom-' . $this->id );
		self::addContext( $msg, $context );
		if ( $msg->exists() ) {
			$customText = $msg->plain();
		}

		$msg = wfMessage( 'translate-tag-page-desc', $title, $target, $languageName, $pageLanguageCode );
		self::addContext( $msg, $context );

		return $msg->plain() . $customText;
	}
}
