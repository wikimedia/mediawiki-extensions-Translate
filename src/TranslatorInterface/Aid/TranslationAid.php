<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TranslatorInterface\Aid;

use IContextSource;
use MediaWiki\Extension\Translate\TranslatorInterface\TranslationHelperException;
use MessageGroup;
use MessageHandle;

/**
 * Multipurpose class for translation aids:
 *  - interface for translation aid classes
 *  - listing of available translation aids
 *
 * @defgroup TranslationAids Translation Aids
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @since 2013-01-01
 */
abstract class TranslationAid {
	/** @var MessageGroup */
	protected $group;
	/** @var MessageHandle */
	protected $handle;
	/** @var IContextSource */
	protected $context;
	/** @var TranslationAidDataProvider */
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
	abstract public function getData(): array;

	/**
	 * List of available message types mapped to the classes
	 * implementing them.
	 *
	 * @return array
	 */
	public static function getTypes(): array {
		return [
			'groups' => GroupsAid::class,
			'definition' => MessageDefinitionAid::class,
			'translation' => CurrentTranslationAid::class,
			'inotherlanguages' => InOtherLanguagesAid::class,
			'documentation' => DocumentationAid::class,
			'mt' => MachineTranslationAid::class,
			'definitiondiff' => UpdatedDefinitionAid::class,
			'ttmserver' => TTMServerAid::class,
			'support' => SupportAid::class,
			'gettext' => GettextDocumentationAid::class,
			'insertables' => InsertablesAid::class,
		];
	}
}
