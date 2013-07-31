<?php
/**
 * Translation aid provider.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2012-2013, Niklas Laxström
 * @license GPL-2.0+
 */

/**
 * Translation aid which gives the message definition.
 * This usually matches the content of the page ns:key/source_language.
 *
 * @ingroup TranslationAids
 * @since 2013-01-01
 */
class MessageDefinitionAid extends TranslationAid {
	public function getData() {
		$language = $this->group->getSourceLanguage();

		return array(
			'value' => $this->getDefinition(),
			'language' => $language,
		);
	}
}
