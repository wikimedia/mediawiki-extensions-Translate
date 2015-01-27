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
	protected function mapCode( $code ) {
		return $code;
	}

	protected function doPairs() {
		if ( !isset( $this->config['host'] ) ) {
			throw new TranslationWebServiceException( 'Cxserver host not set' );
		}

		$pairs = array();

		$url = $this->config['host'] . '/languagepairs';
		$json = Http::get( $url, $this->config['timeout'] );
		$response = FormatJson::decode( $json, true );

		if ( !is_array( $response ) ) {
			$exception = 'Malformed reply from remote server: ' . strval( $json );
			throw new TranslationWebServiceException( $exception );
		}

		foreach ( $response as $source => $targets ) {
			foreach ( $targets as $target ) {
				$pairs[$source][$target] = true;
			}
		}

		return $pairs;
	}

	protected function doRequest( $text, $from, $to ) {
		if ( !isset( $this->config['host'] ) ) {
			throw new TranslationWebServiceException( 'Cxserver host not set' );
		}

		$service = $this->service;

		$text = trim( $text );
		$text = $this->wrapUntranslatable( $text );

		$options = array();
		$options['timeout'] = $this->config['timeout'];
		$options['method'] = 'POST';
		$options['postData'] = $text;

		$url = $this->config['host'] . "/mt/$from/$to";
		$req = MWHttpRequest::factory( $url, $options );
		wfProfileIn( 'TranslateWebServiceRequest-' . $service );
		$status = $req->execute();
		wfProfileOut( 'TranslateWebServiceRequest-' . $service );

		if ( !$status->isOK() ) {
			$error = $req->getContent();
			// Most likely a timeout or other general error
			throw new TranslationWebServiceException( "Http::get failed:\n" .
					"* " . serialize( $error ) . "\n" .
					"* " . serialize( $status )
			);
		}

		$response = FormatJson::decode( $req->getContent() );
		if ( !is_object( $response ) ) {
			throw new TranslationWebServiceException( serialize( $req->getContent() ) );
		}

		$sug = preg_replace( '~^<div>(.*)</div>$~', '\1', $response->contents );
		$sug = $this->unwrapUntranslatable( $sug );

		return trim( $sug );
	}
}
