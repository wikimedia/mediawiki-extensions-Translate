<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TranslatorInterface\Aid;

/**
 * Translation aid that provides the message definition.
 * This usually matches the content of the page ns:key/source_language.
 * @ingroup TranslationAids
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @since 2013-01-01
 */
class MessageDefinitionAid extends TranslationAid {
	public function getData(): array {
		$language = $this->group->getSourceLanguage();

		return [
			'value' => $this->dataProvider->getDefinition(),
			'language' => $language,
		];
	}
}
