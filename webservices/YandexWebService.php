<?php
/**
 * Contains a class for querying external translation service.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2010-2013 Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

/**
 * Implements support for Yandex translation api v1.
 * @see http://api.yandex.com/translate/
 * @ingroup TranslationWebService
 * @since 2013-01-01
 */
class YandexWebService extends TranslationWebService {
	protected function mapCode( $code ) {
		return $code;
	}

	protected function doPairs() {
		if ( !isset( $this->config['key'] ) ) {
			throw new TranslationWebServiceException( 'API key is not set' );
		}

		$service = $this->service;
		$pairs = array();

		$params = array(
			'key' => $this->config['key'],
		);

		$url = $this->config['pairs'] . '?' . wfArrayToCgi( $params );
		$json = Http::get( $url, $this->config['timeout'] );
		$response = FormatJson::decode( $json );

		if ( !is_object( $response ) ) {
			$exception = 'Malformed reply from remote server: ' . strval( $json );
			throw new TranslationWebServiceException( $exception );
		}

		foreach ( $response->dirs as $pair ) {
			list( $source, $target ) = explode( '-', $pair );
			$pairs[$source][$target] = true;
		}

		return $pairs;
	}

	protected function doRequest( $text, $from, $to ) {
		if ( !isset( $this->config['key'] ) ) {
			throw new TranslationWebServiceException( 'API key is not set' );
		}

		$service = $this->service;

		$text = trim( $text );
		$text = $this->wrapUntranslatable( $text );

		$options = array();
		$options['timeout'] = $this->config['timeout'];
		$options['method'] = 'POST';
		$options['postData'] = array(
			'key' => $this->config['key'],
			'text' => $text,
			'lang' => "$from-$to",
		);

		$url = $this->config['url'];
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
		} elseif ( $response->code !== 200 ) {
			$exception = "(HTTP {$response->code}) with ($service ($from|$to)): " .
				$response->message;
			throw new TranslationWebServiceException( $exception );
		}

		$sug = Sanitizer::decodeCharReferences( $response->text[0] );
		$sug = $this->unwrapUntranslatable( $sug );

		return trim( $sug );
	}
}
