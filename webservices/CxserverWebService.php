<?php
/**
 * Contains a class for querying external translation service.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0+
 */

/**
 * Implements support for cxserver api
 * @ingroup TranslationWebService
 * @since 2015.02
 */
class CxserverWebService extends TranslationWebService {
	public function getType() {
		return 'mt';
	}

	protected function mapCode( $code ) {
		return $code;
	}

	protected function doPairs() {
		if ( !isset( $this->config['host'] ) ) {
			throw new TranslationWebServiceException( 'Cxserver host not set' );
		}

		$pairs = array();

		$url = $this->config['host'] . '/v1/list/mt';
		$json = Http::get(
			$url,
			array( $this->config['timeout'] ),
			__METHOD__
		);
		$response = FormatJson::decode( $json, true );

		if ( !is_array( $response ) ) {
			$exception = 'Malformed reply from remote server: ' . (string)$json;
			throw new TranslationWebServiceException( $exception );
		}

		foreach ( $response['Apertium'] as $source => $targets ) {
			foreach ( $targets as $target ) {
				$pairs[$source][$target] = true;
			}
		}

		return $pairs;
	}

	protected function getQuery( $text, $from, $to ) {
		if ( !isset( $this->config['host'] ) ) {
			throw new TranslationWebServiceException( 'Cxserver host not set' );
		}

		$text = trim( $text );
		$text = $this->wrapUntranslatable( $text );
		$url = $this->config['host'] . "/v1/mt/$from/$to/Apertium";

		return TranslationQuery::factory( $url )
			->timeout( $this->config['timeout'] )
			->postWithData( array( 'html' => $text ) );
	}

	protected function parseResponse( TranslationQueryResponse $reply ) {
		$body = $reply->getBody();
		$response = FormatJson::decode( $body );
		if ( !is_object( $response ) ) {
			throw new TranslationWebServiceException( 'Invalid json: ' . serialize( $body ) );
		}

		$text = preg_replace( '~^<div>(.*)</div>$~', '\1', $response->contents );
		$text = $this->unwrapUntranslatable( $text );

		return trim( $text );
	}
}
