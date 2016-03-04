<?php
/**
 * Class that enhances Title with stuff related to message groups
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2011-2013 Niklas Laxström
 * @license GPL-2.0+
 */

/**
 * Class for pointing to messages, like Title class is for titles.
 * @since 2011-03-13
 */
class MessageHandle {
	/**
	 * @var Title
	 */
	protected $title;

	/**
	 * @var string|null
	 */
	protected $key;

	/**
	 * @var string|null Language code
	 */
	protected $code;

	/**
	 * @var string[]|null
	 */
	protected $groupIds;

	public function __construct( Title $title ) {
		$this->title = $title;
	}

	/**
	 * Check if this handle is in a message namespace.
	 * @return bool
	 */
	public function isMessageNamespace() {
		global $wgTranslateMessageNamespaces;
		$namespace = $this->getTitle()->getNamespace();

		return in_array( $namespace, $wgTranslateMessageNamespaces );
	}

	/**
	 * Recommended to use getCode and getKey instead.
	 * @return string[] Array of the message key and the language code
	 */
	public function figureMessage() {
		if ( $this->key === null ) {
			$title = $this->getTitle();
			// Check if this is a valid message first
			$this->key = $title->getDBkey();
			$known = MessageIndex::singleton()->getGroupIds( $this ) !== array();

			$pos = strrpos( $this->key, '/' );
			if ( $known || $pos === false ) {
				$this->code = '';
			} else {
				// For keys like Foo/, substr returns false instead of ''
				$this->code = (string)( substr( $this->key, $pos + 1 ) );
				$this->key = substr( $this->key, 0, $pos );
			}
		}

		return array( $this->key, $this->code );
	}

	/**
	 * Returns the identified or guessed message key.
	 * @return String
	 */
	public function getKey() {
		$this->figureMessage();

		return $this->key;
	}

	/**
	 * Returns the language code.
	 * For language codeless source messages will return empty string.
	 * @return String
	 */
	public function getCode() {
		$this->figureMessage();

		return $this->code;
	}

	/**
	 * Return the Language object for the assumed language of the content, which might
	 * be different from the subpage code (qqq, no subpage).
	 * @return Language
	 * @since 2016-01
	 */
	public function getEffectiveLanguage() {
		global $wgContLang;
		$code = $this->getCode();
		if ( $code === '' || $this->isDoc() ) {
			return $wgContLang;
		}

		return wfGetLangObj( $code );
	}

	/**
	 * Determine whether the current handle is for message documentation.
	 * @return bool
	 */
	public function isDoc() {
		global $wgTranslateDocumentationLanguageCode;

		return $this->getCode() === $wgTranslateDocumentationLanguageCode;
	}

	/**
	 * Determine whether the current handle is for page translation feature.
	 * This does not consider whether the handle corresponds to any message.
	 * @return bool
	 */
	public function isPageTranslation() {
		return $this->getTitle()->inNamespace( NS_TRANSLATIONS );
	}

	/**
	 * Returns all message group ids this message belongs to.
	 * The primary message group id is always the first one.
	 * If the handle does not correspond to any message, the returned array
	 * is empty.
	 * @return string[]
	 */
	public function getGroupIds() {
		if ( $this->groupIds === null ) {
			$this->groupIds = MessageIndex::singleton()->getGroupIds( $this );
		}

		return $this->groupIds;
	}

	/**
	 * Get the primary MessageGroup this message belongs to.
	 * You should check first that the handle is valid.
	 * @throws MWException
	 * @return MessageGroup
	 */
	public function getGroup() {
		$ids = $this->getGroupIds();
		if ( !isset( $ids[0] ) ) {
			throw new MWException( 'called before isValid' );
		}

		return MessageGroups::getGroup( $ids[0] );
	}

	/**
	 * Checks if the handle corresponds to a known message.
	 * @since 2011-03-16
	 * @return bool
	 */
	public function isValid() {
		if ( !$this->isMessageNamespace() ) {
			return false;
		}

		$groups = $this->getGroupIds();
		if ( !$groups ) {
			return false;
		}

		// Do another check that the group actually exists
		$group = $this->getGroup();
		if ( !$group ) {
			$warning = "MessageIndex is out of date – refers to unknown group {$groups[0]}. ";
			$warning .= 'Doing a rebuild.';
			wfWarn( $warning );
			MessageIndexRebuildJob::newJob()->run();

			return false;
		}

		return true;
	}

	/**
	 * Get the original title.
	 * @return Title
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * Get the original title.
	 * @param string $code Language code.
	 * @return Title
	 * @since 2014.04
	 */
	public function getTitleForLanguage( $code ) {
		return Title::makeTitle(
			$this->title->getNamespace(),
			$this->getKey() . "/$code"
		);
	}

	/**
	 * Get the title for the page base.
	 * @return Title
	 * @since 2014.04
	 */
	public function getTitleForBase() {
		return Title::makeTitle(
			$this->title->getNamespace(),
			$this->getKey()
		);
	}

	/**
	 * Check if a string contains the fuzzy string.
	 *
	 * @param $text string Arbitrary text
	 * @return bool If string contains fuzzy string.
	 */
	public static function hasFuzzyString( $text ) {
		return strpos( $text, TRANSLATE_FUZZY ) !== false;
	}

	/**
	 * Check if a title is marked as fuzzy.
	 * @return bool If title is marked fuzzy.
	 */
	public function isFuzzy() {
		$dbr = wfGetDB( DB_SLAVE );

		$tables = array( 'page', 'revtag' );
		$field = 'rt_type';
		$conds = array(
			'page_namespace' => $this->title->getNamespace(),
			'page_title' => $this->title->getDBkey(),
			'rt_type' => RevTag::getType( 'fuzzy' ),
			'page_id=rt_page',
			'page_latest=rt_revision'
		);

		$res = $dbr->selectField( $tables, $field, $conds, __METHOD__ );

		return $res !== false;
	}
}
