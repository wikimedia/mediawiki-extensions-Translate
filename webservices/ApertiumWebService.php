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
 * Implements support Apetrium translator api.
 * @see http://wiki.apertium.org/wiki/Apertium_web_service
 * @ingroup TranslationWebService
 * @since 2013-01-01
 */
class ApertiumWebService extends TranslationWebService {
	protected function mapCode( $code ) {
		return str_replace( '-', '_', wfBCP47( $code ) );
	}

	protected function doPairs() {
		$pairs = array();
		$json = Http::get( $this->config['pairs'], $this->config['timeout'] );
		$response = FormatJson::decode( $json );

		if ( !is_object( $response ) ) {
			$error = 'Malformed reply from remote server: ' . strval( $json );
			throw new TranslationWebServiceException( $error );
		}

		foreach ( $response->responseData as $pair ) {
			$source = $pair->sourceLanguage;
			$target = $pair->targetLanguage;
			$pairs[$source][$target] = true;
		}

		return $pairs;
	}

	protected function doRequest( $text, $from, $to ) {
		$service = $this->service;

		$text = trim( $text );
		$text = $this->wrapUntranslatable( $text );

		$options = array();
		$options['timeout'] = $this->config['timeout'];
		$params = array(
			'q' => $text,
			'langpair' => "$from|$to",
			'x-application' => "Translate " . TRANSLATE_VERSION . ")",
		);

		if ( $this->config['key'] ) {
			$params['key'] = $this->config['key'];
		}

		$url = $this->config['url'] . '?' . wfArrayToCgi( $params );

		$req = MWHttpRequest::factory( $url, $options );
		wfProfileIn( 'TranslateWebServiceRequest-' . $this->service );
		$status = $req->execute();
		wfProfileOut( 'TranslateWebServiceRequest-' . $this->service );

		if ( !$status->isOK() ) {
			$error = $req->getContent();
			// Most likely a timeout or other general error
			throw new TranslationWebServiceException(
				"Http::get failed:\n" .
					"* " . serialize( $error ) . "\n" .
					"* " . serialize( $status )
			);
		}

		$response = FormatJson::decode( $req->getContent() );
		if ( !is_object( $response ) ) {
			throw new TranslationWebServiceException( serialize( $req->getContent() ) );
		} elseif ( $response->responseStatus !== 200 ) {
			$error = "(HTTP {$response->responseStatus}) with ($service ($from|$to)): " .
				$response->responseDetails;
			throw new TranslationWebServiceException( $error );
		}

		$sug = Sanitizer::decodeCharReferences( $response->responseData->translatedText );
		$sug = $this->unwrapUntranslatable( $sug );

		return trim( $sug );
	}
}
