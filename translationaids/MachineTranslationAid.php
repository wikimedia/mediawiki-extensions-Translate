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
 * Translation aid which gives suggestion from machine translation services.
 *
 * @ingroup TranslationAids
 * @since 2013-01-01
 */
class MachineTranslationAid extends TranslationAid {
	public function getData() {
		$suggestions = array( '**' => 'suggestion' );

		$translations = $this->getTranslations();
		$from = $this->group->getSourceLanguage();
		$to = $this->handle->getCode();

		global $wgTranslateTranslationServices;
		foreach ( $wgTranslateTranslationServices as $name => $config ) {
			if ( $config['type'] === 'ttmserver' ) {
				continue;
			}

			$service = TranslationWebService::factory( $name, $config );
			if ( !$service ) {
				continue;
			}

			$results = $service->getSuggestions( $translations, $from, $to );
			$suggestions = array_merge( $suggestions, $results );
		}

		return $suggestions;
	}
}
