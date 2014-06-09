<?php
/**
 * Translation aid code.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2013, Niklas Laxström
 * @license GPL-2.0+
 */

/**
 * Multipurpose class for translation aids:
 *  - interface for translation aid classes
 *  - listing of available translation aids
 *  - some utility functions for translation aid classes
 *
 * @defgroup TranslationAids Translation Aids
 * @since 2013-01-01
 */
abstract class TranslationAid {
	/**
	 * @var MessageGroup
	 */
	protected $group;

	/**
	 * @var MessageHandle
	 */
	protected $handle;

	/**
	 * @var IContextSource
	 */
	protected $context;

	public function __construct( MessageGroup $group, MessageHandle $handle,
		IContextSource $context
	) {
		$this->group = $group;
		$this->handle = $handle;
		$this->context = $context;
	}

	/**
	 * Translation aid class should implement this function. Return value should
	 * be an array with keys and values. Because these are used in the MediaWiki
	 * API, lists (numeric keys) should have key '**' set to element name that
	 * describes the list values. For example if the translation aid provides
	 * translation suggestions, it would return an array which has key '**' set
	 * to 'suggestion' and then list of arrays, each containing fields for the
	 * information of the suggestions. See InOtherLanguagesAid for example.
	 *
	 * @throw TranslationHelperException Used to signal unexpected errors to aid
	 *  debugging
	 * @return array
	 */
	abstract public function getData();

	/**
	 * Get the message definition. Cached for performance.
	 *
	 * @return string
	 */
	public function getDefinition() {
		static $cache = array();

		$key = $this->handle->getTitle()->getPrefixedText();

		if ( array_key_exists( $key, $cache ) ) {
			return $cache[$key];
		}

		if ( method_exists( $this->group, 'getMessageContent' ) ) {
			$cache[$key] = $this->group->getMessageContent( $this->handle );
		} else {
			$cache[$key] = $this->group->getMessage(
				$this->handle->getKey(),
				$this->group->getSourceLanguage()
			);
		}

		return $cache[$key];
	}

	/**
	 * @return Content
	 */
	protected function getDefinitionContent() {
		$text = $this->getDefinition();

		return ContentHandler::makeContent( $text, $this->handle->getTitle() );
	}

	/**
	 * Get the translations in all languages. Cached for performance.
	 * Fuzzy translation are not included.
	 *
	 * @return array Language code => Translation
	 */
	public function getTranslations() {
		static $cache = array();

		$key = $this->handle->getTitle()->getPrefixedText();

		if ( array_key_exists( $key, $cache ) ) {
			return $cache[$key];
		}

		$data = ApiQueryMessageTranslations::getTranslations( $this->handle );
		$namespace = $this->handle->getTitle()->getNamespace();

		$cache[$key] = array();

		foreach ( $data as $page => $info ) {
			$tTitle = Title::makeTitle( $namespace, $page );
			$tHandle = new MessageHandle( $tTitle );

			$fuzzy = MessageHandle::hasFuzzyString( $info[0] ) || $tHandle->isFuzzy();
			if ( $fuzzy ) {
				continue;
			}

			$code = $tHandle->getCode();
			$cache[$key][$code] = $info[0];
		}

		return $cache[$key];
	}

	/**
	 * List of available message types mapped to the classes
	 * implementing them.
	 *
	 * @return array
	 */
	public static function getTypes() {
		$types = array(
			'definition' => 'MessageDefinitionAid',
			'translation' => 'CurrentTranslationAid',
			'inotherlanguages' => 'InOtherLanguagesAid',
			'documentation' => 'DocumentationAid',
			'mt' => 'MachineTranslationAid',
			'definitiondiff' => 'UpdatedDefinitionAid',
			'ttmserver' => 'TTMServerAid',
			'support' => 'SupportAid',
			'gettext' => 'GettextDocumentationAid',
			'insertables' => 'InsertablesAid',
		);

		return $types;
	}
}
