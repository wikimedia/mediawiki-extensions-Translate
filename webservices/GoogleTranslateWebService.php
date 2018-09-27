<?php
/**
* Contains a class for querying external translation service.
*
* @file
* @author Carsten Schmitz / LimeSurvey GmbH
* @license GPL-2.0-or-later
*/

/**
* Implements support for Google Translate API
* @see https://tech.yandex.com/translate/
* @ingroup TranslationWebService
* @since 2013-01-01
*/
class GoogleTranslateWebService extends TranslationWebService {
	public function getType() {
		return 'mt';  // Type MachineTranslationAid
	}

	protected function mapCode( $code ) {
		if ( $code === 'be-tarask' ) {
			$code = 'be';
		}
		return $code;
	}

	/**
	* Test whether Google Translate does support the language - there are no certain pairs
	*
	* @param string $from Source language
	* @param string $to Target language
	* @return bool
	* @since 2015.12
	* @throws TranslationWebServiceConfigurationException
	*/
	public function isSupportedLanguagePair( $from, $to ) {
		$pairs = $this->getSupportedLanguagePairs();
		// As long as the source & target language exist at Google it is fine
		return (isset( $pairs[$from]) && isset( $pairs[to]));
	}

	protected function doPairs() {
		if ( !isset( $this->config['key'] ) ) {
			throw new TranslationWebServiceConfigurationException( 'API key is not set' );
		}

		$pairs = [];

		$params = [
			'key' => $this->config['key'],
		];

		$url = $this->config['pairs'] . '?' . wfArrayToCgi( $params );
		$json = Http::get(
			$url,
			[ 'timeout' => $this->config['timeout'] ],
			__METHOD__
		);
		$response = FormatJson::decode( $json );

		if ( !is_object( $response ) ) {
			$exception = 'Malformed reply from remote server: ' . (string)$json;
			throw new TranslationWebServiceException( $exception );
		}
		foreach ( $response->data->languages as $language ) {
			// Create fake pairs because Google Translate doesn't do pairs
			$pairs[$language->language][$language->language] = true;
		}

		return $pairs;
	}

	protected function getQuery( $text, $from, $to ) {
		if ( !isset( $this->config['key'] ) ) {
			throw new TranslationWebServiceConfigurationException( 'API key is not set' );
		}
		# https://cloud.google.com/translate/docs/reference/translate
		if ( strlen( $text ) > 10000 ) {
			// There is no limitation but we don't want the translation service to be abused, don't we?
			throw new TranslationWebServiceInvalidInputException( 'Source text too long' );
		}

		$text = trim( $text );
		$text = $this->wrapUntranslatable( $text );

		return TranslationQuery::factory( $this->config['url'] )
		->timeout( $this->config['timeout'] )
		->postWithData(
			[
				'key' => $this->config['key'],
				'q' => $text,
				'target' => $to,
				'source' => $from,
				'format' => 'html',
			]
		);
	}

	protected function parseResponse( TranslationQueryResponse $reply ) {
		$body = $reply->getBody();
		$response = FormatJson::decode( $body );
		if ( !is_object( $response ) ) {
			throw new TranslationWebServiceException( 'Invalid json: ' . serialize( $body ) );
		}
		$text = Sanitizer::decodeCharReferences( $response->data->translations[0]->translatedText );
		$text = $this->unwrapUntranslatable( $text );

		return trim( $text );

	}
}
