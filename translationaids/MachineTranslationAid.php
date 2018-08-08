<?php
/**
 * Translation aid provider.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2012-2013, Niklas Laxström
 * @license GPL-2.0-or-later
 */

/**
 * Translation aid which gives suggestion from machine translation services.
 *
 * @ingroup TranslationAids
 * @since 2013-01-01 | 2015.02 extends QueryAggregatorAwareTranslationAid
 */
class MachineTranslationAid extends QueryAggregatorAwareTranslationAid {
	public function populateQueries() {
		$definition = $this->dataProvider->getDefinition();
		$translations = $this->dataProvider->getGoodTranslations();
		$from = $this->group->getSourceLanguage();
		$to = $this->handle->getCode();

		if ( trim( $definition ) === '' ) {
			return;
		}

		foreach ( $this->getWebServices( 'mt' ) as $service ) {
			if ( $service->checkTranslationServiceFailure() ) {
				continue;
			}

			try {
				if ( $service->isSupportedLanguagePair( $from, $to ) ) {
					$this->storeQuery( $service, $from, $to, $definition );
					continue;
				}

				// Search for translations which we can use as a source for MT
				// @todo: Support setting priority of languages like Yandex used to have
				foreach ( $translations as $from => $text ) {
					if ( !$service->isSupportedLanguagePair( $from, $to ) ) {
						continue;
					}

					$this->storeQuery( $service, $from, $to, $text );
					break;
				}
			} catch ( TranslationWebServiceConfigurationException $e ) {
				throw new TranslationHelperException( $service->getName() . ': ' . $e->getMessage() );
			}
		}
	}

	public function getData() {
		$suggestions = [ '**' => 'suggestion' ];

		foreach ( $this->getQueryData() as $queryData ) {
			$suggestions[] = $this->formatSuggestion( $queryData );
		}

		return array_filter( $suggestions );
	}

	/**
	 * @param array $queryData
	 * @return array|null
	 */
	protected function formatSuggestion( array $queryData ) {
		$service = $queryData['service'];
		$response = $queryData['response'];
		$sourceLanguage = $queryData['language'];
		$sourceText = $queryData['text'];

		$result = $service->getResultData( $response );
		if ( $result === null ) {
			return null;
		}

		return [
			'target' => $result,
			'service' => $service->getName(),
			'source_language' => $sourceLanguage,
			'source' => $sourceText,
		];
	}
}
