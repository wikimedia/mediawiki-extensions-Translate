<?php
/**
 * Contains a class for querying external translation service.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0+
 */

/**
 * Implements support Apetrium translator api.
 * @see http://wiki.apertium.org/wiki/Apertium_web_service
 * @ingroup TranslationWebService
 * @since 2013-01-01
 */
class ApertiumWebService extends TranslationWebService {
	public function getType() {
		return 'mt';
	}

	protected function mapCode( $code ) {
		return str_replace( '-', '_', wfBCP47( $code ) );
	}

	protected function doPairs() {
		$pairs = array();
		$json = Http::get(
			$this->config['pairs'],
			array( 'timeout' => $this->config['timeout'] ),
			__METHOD__
		);
		$response = FormatJson::decode( $json );

		if ( !is_object( $response ) ) {
			$error = 'Malformed reply from remote server: ' . (string)$json;
			throw new TranslationWebServiceException( $error );
		}

		foreach ( $response->responseData as $pair ) {
			$source = $pair->sourceLanguage;
			$target = $pair->targetLanguage;
			$pairs[$source][$target] = true;
		}

		return $pairs;
	}

	protected function getQuery( $text, $from, $to ) {
		if ( !isset( $this->config['key'] ) ) {
			throw new TranslationWebServiceException( 'API key is not set' );
		}

		$text = trim( $text );
		$text = $this->wrapUntranslatable( $text );

		$params = array(
			'q' => $text,
			'langpair' => "$from|$to",
			'x-application' => 'Translate ' . TRANSLATE_VERSION . ')',
		);

		return TranslationQuery::factory( $this->config['url'] )
			->timeout( $this->config['timeout'] )
			->queryParamaters( $params );
	}

	protected function parseResponse( TranslationQueryResponse $reply ) {
		$body = $reply->getBody();
		$response = FormatJson::decode( $body );
		if ( !is_object( $response ) ) {
			throw new TranslationWebServiceException( 'Invalid json: ' . serialize( $body ) );
		} elseif ( $response->responseStatus !== 200 ) {
			throw new TranslationWebServiceException( $response->responseDetails );
		}

		$text = Sanitizer::decodeCharReferences( $response->responseData->translatedText );
		$text = $this->unwrapUntranslatable( $text );

		return trim( $text );
	}
}
