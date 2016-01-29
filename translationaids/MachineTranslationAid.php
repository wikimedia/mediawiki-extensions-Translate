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
 * @since 2013-01-01 | 2015.02 extends QueryAggregatorAwareTranslationAid
 */
class MachineTranslationAid extends QueryAggregatorAwareTranslationAid {
	public function populateQueries() {
		$definition = $this->getDefinition();
		$translations = $this->getTranslations();
		$from = $this->group->getSourceLanguage();
		$to = $this->handle->getCode();

		foreach ( $this->getWebServices( 'mt' ) as $service ) {
			if ( $service->checkTranslationServiceFailure() ) {
				continue;
			}

			if ( $service->isSupportedLanguagePair( $from, $to ) ) {
				$this->storeQuery( $service, $from, $to, $definition );
				continue;
			}

			// Loop of the the translations we have to see which can be used as source
			// @todo: Support setting priority of languages like Yandex used to have
			foreach ( $translations as $from => $text ) {
				if ( !$service->isSupportedLanguagePair( $from, $to ) ) {
					continue;
				}

				$this->storeQuery( $service, $from, $to, $text );
				break;
			}
		}
	}

	public function getData() {
		$suggestions = array( '**' => 'suggestion' );

		foreach ( $this->getQueryData() as $queryData ) {
			$suggestions[] = $this->formatSuggestion( $queryData );
		}

		return $suggestions;
	}

	protected function formatSuggestion( array $queryData ) {
		$service = $queryData['service'];
		$response = $queryData['response'];
		$sourceLanguage = $queryData['language'];
		$sourceText = $queryData['text'];

		return array(
			'target' => $service->getResultData( $response ),
			'service' => $service->getName(),
			'source_language' => $sourceLanguage,
			'source' => $sourceText,
		);
	}
}
