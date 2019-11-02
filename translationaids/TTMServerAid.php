<?php
/**
 * Translation aid provider.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */

/**
 * Translation aid which gives suggestion from translation memory.
 *
 * @ingroup TranslationAids
 * @since 2013-01-01 | 2015.02 extends QueryAggregatorAwareTranslationAid
 */
class TTMServerAid extends QueryAggregatorAwareTranslationAid {
	public function populateQueries() {
		$text = $this->dataProvider->getDefinition();
		$from = $this->group->getSourceLanguage();
		$to = $this->handle->getCode();

		foreach ( $this->getWebServices( 'ttmserver' ) as $service ) {
			$this->storeQuery( $service, $from, $to, $text );
		}
	}

	public function getData() {
		$text = $this->dataProvider->getDefinition();
		if ( trim( $text ) === '' ) {
			return [];
		}

		$suggestions = [];
		$from = $this->group->getSourceLanguage();
		$to = $this->handle->getCode();

		foreach ( $this->getInternalServices() as $name => $service ) {
			try {
				$queryData = $service->query( $from, $to, $text );
			} catch ( Exception $e ) {
				// Not ideal to catch all exceptions
				continue;
			}

			$sugs = $this->formatInternalSuggestions( $queryData, $service, $name, $from );
			$suggestions = array_merge( $suggestions, $sugs );
		}

		// Results from web services
		foreach ( $this->getQueryData() as $queryData ) {
			$sugs = $this->formatWebSuggestions( $queryData );
			$suggestions = array_merge( $suggestions, $sugs );
		}

		$suggestions = TTMServer::sortSuggestions( $suggestions );
		// Must be here to not mess up the sorting function
		$suggestions['**'] = 'suggestion';

		return $suggestions;
	}

	protected function formatWebSuggestions( array $queryData ) {
		$service = $queryData['service'];
		$response = $queryData['response'];
		$sourceLanguage = $queryData['language'];
		$sourceText = $queryData['text'];

		// getResultData returns a null on failure instead of throwing an exception
		$items = $service->getResultData( $response );
		if ( $items === null ) {
			return [];
		}

		$localPrefix = Title::makeTitle( NS_MAIN, '' )->getFullURL( '', false, PROTO_CANONICAL );
		$localPrefixLength = strlen( $localPrefix );

		foreach ( $items as &$item ) {
			$local = strncmp( $item['uri'], $localPrefix, $localPrefixLength ) === 0;
			$item = array_merge( $item, [
				'service' => $service->getName(),
				'source_language' => $sourceLanguage,
				'source' => $sourceText,
				'local' => $local,
			] );

			// ApiTTMServer expands this... need to fix it again to be the bare name
			if ( $local ) {
				$pagename = urldecode( substr( $item['location'], $localPrefixLength ) );
				$item['location'] = $pagename;
				$handle = new MessageHandle( Title::newfromText( $pagename ) );
				$item['editorUrl'] = TranslateUtils::getEditorUrl( $handle );
			}
		}
		return $items;
	}

	/**
	 * @param array[] $queryData
	 * @param ReadableTTMServer $s
	 * @param string $serviceName
	 * @param string $sourceLanguage
	 * @return array[]
	 */
	protected function formatInternalSuggestions(
		array $queryData, ReadableTTMServer $s, $serviceName, $sourceLanguage
	) {
		$items = [];

		foreach ( $queryData as $item ) {
			$local = $s->isLocalSuggestion( $item );

			$item['service'] = $serviceName;
			$item['source_language'] = $sourceLanguage;
			$item['local'] = $local;
			// Likely only needed for non-public DatabaseTTMServer
			$item['uri'] = $item['uri'] ?? $s->expandLocation( $item );
			if ( $local ) {
				$handle = new MessageHandle( Title::newfromText( $item[ 'location' ] ) );
				$item['editorUrl'] = TranslateUtils::getEditorUrl( $handle );
			}
			$items[] = $item;
		}

		return $items;
	}

	/**
	 * @return ReadableTTMServer[]
	 */
	private function getInternalServices() {
		$services = [];

		// "Local" queries using a client can't be run in parallel with web services
		global $wgTranslateTranslationServices;
		foreach ( $wgTranslateTranslationServices as $name => $config ) {
			$service = TTMServer::factory( $config );
			if ( !$service ) {
				continue;
			}

			// Except if they are public, we can call back via API.
			// See TranslationWebService::factory
			$public = $config['public'] ?? false;
			if ( $service instanceof ReadableTTMServer && $public === true ) {
				continue;
			}

			$services[ $name ] = $service;
		}

		return $services;
	}
}
