<?php
/**
 * Translation aid provider.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0+
 */

/**
 * Translation aid which gives the current saved translation.
 *
 * @ingroup TranslationAids
 * @since 2013-01-01
 */
class CurrentTranslationAid extends TranslationAid {
	public function getData() {
		$translation = null;

		$title = $this->handle->getTitle();
		$translation = TranslateUtils::getMessageContent(
			$this->handle->getKey(),
			$this->handle->getCode(),
			$title->getNamespace()
		);

		Hooks::run( 'TranslatePrefillTranslation', [ &$translation, $this->handle ] );
		$fuzzy = MessageHandle::hasFuzzyString( $translation ) || $this->handle->isFuzzy();
		$translation = str_replace( TRANSLATE_FUZZY, '', $translation );

		return [
			'language' => $this->handle->getCode(),
			'fuzzy' => $fuzzy,
			'value' => $translation,
		];
	}
}
