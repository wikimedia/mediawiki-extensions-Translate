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
 * Translation aid which gives the "in other languages" suggestions.
 *
 * @ingroup TranslationAids
 * @since 2013-01-01
 */
class InOtherLanguagesAid extends TranslationAid {
	public function getData() {
		$suggestions = array(
			'**' => 'suggestion',
		);

		// Fuzzy translations are not included in these
		$translations = $this->getTranslations();
		$code = $this->handle->getCode();

		$sourceLanguage = $this->handle->getGroup()->getSourceLanguage();

		foreach ( $this->getFallbacks( $code ) as $fbcode ) {
			if ( !isset( $translations[$fbcode] ) ) {
				continue;
			}

			if ( $fbcode === $sourceLanguage ) {
				continue;
			}

			$suggestions[] = array(
				'language' => $fbcode,
				'value' => $translations[$fbcode],
			);
		}

		return $suggestions;
	}

	/**
	 * Get the languages for "in other languages". That would be translation
	 * assistant languages with defined language fallbacks additionally.
	 * @param string $code
	 * @return string[] List of language codes
	 */
	protected function getFallbacks( $code ) {
		global $wgTranslateLanguageFallbacks;

		// User preference has the final say
		$preference = $this->context->getUser()->getOption( 'translate-editlangs' );
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
		$fallbacks = array();
		if ( isset( $wgTranslateLanguageFallbacks[$code] ) ) {
			$fallbacks = (array)$wgTranslateLanguageFallbacks[$code];
		}

		$list = Language::getFallbacksFor( $code );
		array_pop( $list ); // Get 'en' away from the end
		$fallbacks = array_merge( $list, $fallbacks );

		return array_unique( $fallbacks );
	}
}
