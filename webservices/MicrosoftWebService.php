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
 * Implements support for Microsoft translation api v2.
 * @see http://msdn.microsoft.com/en-us/library/ff512421.aspx
 * @ingroup TranslationWebService
 * @since 2013-01-01
 */
class MicrosoftWebService extends TranslationWebService {
	protected function mapCode( $code ) {
		$map = array(
			'zh-hant' => 'zh-CHT',
			'zh-hans' => 'zh-CHS',
		);

		return isset( $map[$code] ) ? $map[$code] : $code;
	}

	protected function doPairs() {
		if ( !isset( $this->config['key'] ) ) {
			throw new TranslationWebServiceException( 'API key is not set' );
		}

		$options = array();
		$options['method'] = 'GET';
		$options['timeout'] = $this->config['timeout'];

		$params = array(
			'appId' => $this->config['key'],
		);

		$url = 'http://api.microsofttranslator.com/V2/Http.svc/GetLanguagesForTranslate?';
		$url .= wfArrayToCgi( $params );

		$req = MWHttpRequest::factory( $url, $options );
		wfProfileIn( 'TranslateWebServiceRequest-' . $this->service . '-pairs' );
		$status = $req->execute();
		wfProfileOut( 'TranslateWebServiceRequest-' . $this->service . '-pairs' );

		if ( !$status->isOK() ) {
			$error = $req->getContent();
			// Most likely a timeout or other general error
			$exception = 'Http::get failed:' . serialize( $error ) . serialize( $status );
			throw new TranslationWebServiceException( $exception );
		}

		$xml = simplexml_load_string( $req->getContent() );

		$languages = array();
		foreach ( $xml->string as $language ) {
			$languages[] = strval( $language );
		}

		// Let's make a cartesian product, assuming we can translate from any
		// language to any language
		$pairs = array();
		foreach ( $languages as $from ) {
			foreach ( $languages as $to ) {
				$pairs[$from][$to] = true;
			}
		}

		return $pairs;
	}

	protected function doRequest( $text, $from, $to ) {
		if ( !isset( $this->config['key'] ) ) {
			throw new TranslationWebServiceException( 'API key is not set' );
		}

		$text = trim( $text );
		$text = $this->wrapUntranslatable( $text );

		$options = array();
		$options['timeout'] = $this->config['timeout'];

		$params = array(
			'text' => $text,
			'from' => $from,
			'to' => $to,
			'appId' => $this->config['key'],
		);

		$url = 'http://api.microsofttranslator.com/V2/Http.svc/Translate?';
		$url .= wfArrayToCgi( $params );

		$req = MWHttpRequest::factory( $url, $options );
		wfProfileIn( 'TranslateWebServiceRequest-' . $this->service );
		$status = $req->execute();
		wfProfileOut( 'TranslateWebServiceRequest-' . $this->service );

		if ( !$status->isOK() ) {
			$error = $req->getContent();
			// Most likely a timeout or other general error
			$exception = 'Http::get failed: ' . $url . serialize( $error ) . serialize( $status );
			throw new TranslationWebServiceException( $exception );
		}

		$ret = $req->getContent();
		$text = preg_replace( '~<string.*>(.*)</string>~', '\\1', $ret );
		$text = Sanitizer::decodeCharReferences( $text );

		return $this->unwrapUntranslatable( $text );
	}
}
