<?php
/**
 * This file contains an unmanaged message group implementation.
 *
 * @file
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @license GPL-2.0-or-later
 */

/**
 * Wraps the translatable page sections into a message group.
 * @ingroup PageTranslation MessageGroup
 */
class WikiPageMessageGroup extends WikiMessageGroup implements IDBAccessObject, \Serializable {
	/**
	 * @var Title|string
	 */
	protected $title;

	/**
	 * @var int
	 */
	protected $namespace = NS_TRANSLATIONS;

	/**
	 * @param string $id
	 * @param Title|string $source
	 */
	public function __construct( $id, $source ) {
		$this->id = $id;
		$this->title = $source;
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

		$dbr = TranslateUtils::getSafeReadDB();
		$tables = 'translate_sections';
		$vars = [ 'trs_key', 'trs_text' ];
		$conds = [ 'trs_page' => $this->getTitle()->getArticleID() ];
		$options = [ 'ORDER BY' => 'trs_order' ];
		$res = $dbr->select( $tables, $vars, $conds, __METHOD__, $options );

		$defs = [];
		$prefix = $this->getTitle()->getPrefixedDBkey() . '/';

		foreach ( $res as $r ) {
			$section = new TPSection();
			$section->text = $r->trs_text;
			$defs[$r->trs_key] = $section->getTextWithVariables();
		}

		$new_defs = [];
		foreach ( $defs as $k => $v ) {
			$k = str_replace( ' ', '_', $k );
			$new_defs[$prefix . $k] = $v;
		}

		$this->definitions = $new_defs;
		return $this->definitions;
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
			$revFlags = Revision::READ_NORMAL; // bug T95753
		} else {
			$revFlags = ( $flags & self::READ_LATEST ) == self::READ_LATEST
				? Revision::READ_LATEST
				: Revision::READ_NORMAL;
		}
		$rev = Revision::newFromTitle( $title, false, $revFlags );

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
		$checker->setChecks( [
			[ $checker, 'pluralCheck' ],
			[ $checker, 'braceBalanceCheck' ],
			[ $checker, 'miscMWChecks' ]
		] );

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

	public function serialize() {
		$toSerialize = [
			'title' => $this->getTitle()->getPrefixedText(),
			'id' => $this->id,
			'_v' => 1 // version - to track incompatible changes
		];

		// NOTE: get_class_vars returns properties before the constructor has run so if any default
		// values have to be set for properties, do them while declaring the properties themselves.
		// Also any properties that are object will automatically be serialized because `===`
		// does not actually compare object properties to see that they are same.

		// Using array_diff_key to unset the properties already set earlier.
		$defaultProps = array_diff_key( get_class_vars( self::class ),  $toSerialize );

		foreach ( $defaultProps as $prop => $defaultVal ) {
			if ( $this->{$prop} === $defaultVal ) {
				continue;
			}

			$toSerialize[$prop] = $this->{$prop};
		}

		return FormatJson::encode( $toSerialize, false, FormatJson::ALL_OK );
	}

	public function unserialize( $serialized ) {
		$deserialized = FormatJson::decode( $serialized );
		if ( $deserialized === false ) {
			// Unrecoverable. This should not happen but still.
			throw new \UnexpectedValueException(
				'Error while deserializing to WikiPageMessageGroup object - FormatJson::decode failed. ' .
				"Serialize string - $serialized."
			);
		}

		// Use as needed in the future to track incompatible changes.
		// $version = $deserialized->_v;
		// unset($deserialized->_v);

		// Only set the properties that are present in the class and the deserialized object.
		$classProps = array_keys( get_class_vars( self::class ) );

		foreach ( $classProps as $prop ) {
			if ( property_exists( $deserialized, $prop ) ) {
				$this->{$prop} = $deserialized->{$prop};
			}
		}
	}
}
