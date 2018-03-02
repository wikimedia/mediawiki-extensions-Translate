<?php
/**
 * Translation aid code.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */

/**
 * Multipurpose class for translation aids:
 *  - interface for translation aid classes
 *  - listing of available translation aids
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

	/**
	 * @var TranslationAidDataProvider
	 */
	protected $dataProvider;

	public function __construct(
		MessageGroup $group,
		MessageHandle $handle,
		IContextSource $context,
		TranslationAidDataProvider $dataProvider
	) {
		$this->group = $group;
		$this->handle = $handle;
		$this->context = $context;
		$this->dataProvider = $dataProvider;
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
	 * @throws TranslationHelperException Used to signal unexpected errors to aid
	 *  debugging
	 * @return array
	 */
	abstract public function getData();

	/**
	 * List of available message types mapped to the classes
	 * implementing them.
	 *
	 * @return array
	 */
	public static function getTypes() {
		$types = [
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
		];

		return $types;
	}
}
