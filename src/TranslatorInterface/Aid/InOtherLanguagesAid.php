<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TranslatorInterface\Aid;

use MediaWiki\MediaWikiServices;

/**
 * Translation aid that provides the "in other languages" suggestions.
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @since 2013-01-01
 * @ingroup TranslationAids
 */
class InOtherLanguagesAid extends TranslationAid {
	public function getData(): array {
		$suggestions = [
			'**' => 'suggestion',
		];

		// Fuzzy translations are not included in these
		$translations = $this->dataProvider->getGoodTranslations();
		$code = $this->handle->getCode();

		$sourceLanguage = $this->handle->getGroup()->getSourceLanguage();

		foreach ( $this->getFallbacks( $code ) as $fallbackCode ) {
			if ( !isset( $translations[$fallbackCode] ) ) {
				continue;
			}

			if ( $fallbackCode === $sourceLanguage ) {
				continue;
			}

			$suggestions[] = [
				'language' => $fallbackCode,
				'value' => $translations[$fallbackCode],
			];
		}

		return $suggestions;
	}

	/**
	 * Get the languages for "in other languages". That would be translation
	 * assistant languages with defined language fallbacks additionally.
	 * @param string $code
	 * @return string[] List of language codes
	 */
	protected function getFallbacks( string $code ): array {
		global $wgTranslateLanguageFallbacks;
		$mwServices = MediaWikiServices::getInstance();

		// User preference has the final say
		$userOptionLookup = $mwServices->getUserOptionsLookup();
		$preference = $userOptionLookup->getOption( $this->context->getUser(), 'translate-editlangs' );
		if ( $preference !== 'default' ) {
			$fallbacks = array_map( 'trim', explode( ',', $preference ) );
			foreach ( $fallbacks as $k => $v ) {
				if ( $v === $code ) {
					unset( $fallbacks[$k] );
				}
			}

			return $fallbacks;
		}

		// Global configuration settings
		$fallbacks = [];
		if ( isset( $wgTranslateLanguageFallbacks[$code] ) ) {
			$fallbacks = (array)$wgTranslateLanguageFallbacks[$code];
		}

		$list = $mwServices->getLanguageFallback()->getAll( $code );
		$fallbacks = array_merge( $list, $fallbacks );

		return array_unique( $fallbacks );
	}
}
