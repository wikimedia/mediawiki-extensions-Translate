<?php
/**
 * Translation aid provider.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0+
 */

/**
 * Translation aid which gives suggestion from translation memory.
 *
 * @ingroup TranslationAids
 * @since 2013-01-01 | 2015.02 extends QueryAggregatorAwareTranslationAid
 */
class TTMServerAid extends QueryAggregatorAwareTranslationAid {
	public function populateQueries() {
		$text = $this->getDefinition();
		$from = $this->group->getSourceLanguage();
		$to = $this->handle->getCode();

		foreach ( $this->getWebServices( 'ttmserver' ) as $service ) {
			$this->storeQuery( $service, $from, $to, $text );
		}
	}

	public function getData() {
		$suggestions = array();

		$text = $this->getDefinition();
		$from = $this->group->getSourceLanguage();
		$to = $this->handle->getCode();

		// "Local" queries using a client can't be run in parallel with web services
		global $wgTranslateTranslationServices;
		foreach ( $wgTranslateTranslationServices as $name => $config ) {
			$server = TTMServer::factory( $config );

			try {
				if ( $server instanceof ReadableTTMServer ) {
					// Except if they are public, we can call back via API
					if ( isset( $config['public'] ) && $config['public'] === true ) {
						continue;
					}

					$query = $server->query( $from, $to, $text );
				} else {
					continue;
				}
			} catch ( Exception $e ) {
				// Not ideal to catch all exceptions
				continue;
			}

			foreach ( $query as $item ) {
				$item['service'] = $name;
				$item['source_language'] = $from;
				$item['local'] = $server->isLocalSuggestion( $item );
				$item['uri'] = $server->expandLocation( $item );
				$suggestions[] = $item;
			}
		}

		// Results from web services
		foreach ( $this->getQueryData() as $queryData ) {
			$sugs = $this->formatSuggestions( $queryData );
			$suggestions = array_merge( $suggestions, $sugs );
		}

		$suggestions = TTMServer::sortSuggestions( $suggestions );
		// Must be here to not mess up the sorting function
		$suggestions['**'] = 'suggestion';

		return $suggestions;
	}

	protected function formatSuggestions( array $queryData ) {
		$service = $queryData['service'];
		$response = $queryData['response'];
		$sourceLanguage = $queryData['language'];
		$sourceText = $queryData['text'];

		$sugs = $service->getResultData( $response );
		foreach ( $sugs as &$sug ) {
			$sug += array(
				'service' => $service->getName(),
				'source_language' => $sourceLanguage,
				'source' => $sourceText,
			);
		}
		return $sugs;
	}
}
