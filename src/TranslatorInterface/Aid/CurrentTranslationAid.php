<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TranslatorInterface\Aid;

use Hooks;
use MessageHandle;
use TranslateUtils;

/**
 * Translation aid that provides the current saved translation.
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @since 2013-01-01
 * @ingroup TranslationAids
 */
class CurrentTranslationAid extends TranslationAid {
	public function getData(): array {
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
