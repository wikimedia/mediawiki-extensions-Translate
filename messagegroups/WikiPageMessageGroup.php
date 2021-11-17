<?php
/**
 * This file contains an unmanaged message group implementation.
 *
 * @file
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @license GPL-2.0-or-later
 */

use MediaWiki\Extension\Translate\PageTranslation\TranslatablePageInsertablesSuggester;
use MediaWiki\Extension\Translate\PageTranslation\TranslationUnit;
use MediaWiki\Extension\Translate\Validation\ValidationRunner;
use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\SlotRecord;

/**
 * Wraps the translatable page sections into a message group.
 * @ingroup PageTranslation MessageGroup
 */
class WikiPageMessageGroup extends MessageGroupOld implements IDBAccessObject {
	/** @var Title|string */
	protected $title;
	/** @var int */
	protected $namespace = NS_TRANSLATIONS;

	/**
	 * @param string $id
	 * @param Title|string $title
	 */
	public function __construct( $id, $title ) {
		$this->id = $id;
		$this->title = $title;
	}

	public function getSourceLanguage() {
		return $this->getTitle()->getPageLanguage()->getCode();
	}

	/** @return Title */
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

	/** @return string[] */
	public function getDefinitions() {
		if ( is_array( $this->definitions ) ) {
			return $this->definitions;
		}

		$title = $this->getTitle();

		$dbr = TranslateUtils::getSafeReadDB();
		$tables = [ 'page', 'translate_sections' ];
		$vars = [ 'trs_key', 'trs_text' ];
		$conds = [
			'page_namespace' => $title->getNamespace(),
			'page_title' => $title->getDBkey(),
			// The join condition
			'page_id = trs_page',
		];
		$options = [ 'ORDER BY' => 'trs_order' ];
		$res = $dbr->select( $tables, $vars, $conds, __METHOD__, $options );

		$defs = [];

		foreach ( $res as $r ) {
			$section = new TranslationUnit( $r->trs_text );
			$defs[$r->trs_key] = $section->getTextWithVariables();
		}

		$groupKeys = $this->makeGroupKeys( array_keys( $defs ) );
		$this->definitions = array_combine( $groupKeys, array_values( $defs ) );

		return $this->definitions;
	}

	/**
	 * @param string[] $keys
	 * @return string[]
	 */
	public function makeGroupKeys( array $keys ): array {
		$prefix = $this->getTitle()->getPrefixedDBkey() . '/';
		return array_map( static function ( string $key ) use ( $prefix ): string {
			return $prefix . str_replace( ' ', '_', $key );
		}, $keys );
	}

	/**
	 * Overriding the getLabel method and deriving the label from the title.
	 * Mainly to reduce the amount of data stored in the cache.
	 *
	 * @param IContextSource|null $context
	 * @return string
	 */
	public function getLabel( IContextSource $context = null ) {
		return $this->getTitle()->getPrefixedText();
	}

	/**
	 * Clear caches to avoid stale data.
	 *
	 * For example JobQueue can run for a longer time, and stale definitions would
	 * cause the total number of messages to be incorrect.
	 *
	 * @since 2016.04
	 */
	public function clearCaches() {
		$this->definitions = null;
	}

	public function load( $code ) {
		if ( $this->isSourceLanguage( $code ) ) {
			return $this->getDefinitions();
		}

		return [];
	}

	/**
	 * Returns of stored translation of message specified by the $key in language
	 * code $code.
	 *
	 * @param string $key Message key
	 * @param string $code Language code
	 * @param int $flags READ_* class constant bitfield
	 * @return string|null Stored translation or null.
	 */
	public function getMessage( $key, $code, $flags = self::READ_LATEST ) {
		if ( $this->isSourceLanguage( $code ) ) {
			$stuff = $this->load( $code );

			$title = Title::newFromText( $key );
			if ( $title ) {
				$key = $title->getPrefixedDBkey();
			}

			return $stuff[$key] ?? null;
		}

		$title = Title::makeTitleSafe( $this->getNamespace(), "$key/$code" );
		if ( PageTranslationHooks::$renderingContext ) {
			$revFlags = IDBAccessObject::READ_NORMAL; // bug T95753
		} else {
			$revFlags = ( $flags & self::READ_LATEST ) == self::READ_LATEST
				? IDBAccessObject::READ_LATEST
				: IDBAccessObject::READ_NORMAL;
		}
		$rev = MediaWikiServices::getInstance()
			->getRevisionLookup()
			->getRevisionByTitle( $title, false, $revFlags );

		if ( !$rev ) {
			return null;
		}

		$content = $rev->getContent( SlotRecord::MAIN );
		return ( $content instanceof TextContent ) ? $content->getText() : null;
	}

	/** @return ValidationRunner */
	public function getValidator() {
		$validator = new ValidationRunner( $this->getId() );
		$validator->setValidators( [
			[ 'id' => 'MediaWikiPlural' ],
			[ 'id' => 'BraceBalance' ]
		] );

		return $validator;
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
