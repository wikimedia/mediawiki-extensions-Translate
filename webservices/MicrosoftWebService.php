<?php
/**
 * Contains a class for querying external translation service.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0+
 */

/**
 * Implements support for Microsoft translation api v2.
 * @see http://msdn.microsoft.com/en-us/library/ff512421.aspx
 * @ingroup TranslationWebService
 * @since 2013-01-01
 */
class MicrosoftWebService extends TranslationWebService {
	public function getType() {
		return 'mt';
	}

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
		$status = $req->execute();

		if ( !$status->isOK() ) {
			$error = $req->getContent();
			// Most likely a timeout or other general error
			$exception = 'Http request failed:' . serialize( $error ) . serialize( $status );
			throw new TranslationWebServiceException( $exception );
		}

		$xml = simplexml_load_string( $req->getContent() );

		$languages = array();
		foreach ( $xml->string as $language ) {
			$languages[] = (string)$language;
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

	protected function getQuery( $text, $from, $to ) {
		if ( !isset( $this->config['key'] ) ) {
			throw new TranslationWebServiceException( 'API key is not set' );
		}

		$text = trim( $text );
		$text = $this->wrapUntranslatable( $text );

		$params = array(
			'text' => $text,
			'from' => $from,
			'to' => $to,
			'appId' => $this->config['key'],
		);

		return TranslationQuery::factory( $this->config['url'] )
			->timeout( $this->config['timeout'] )
			->queryParamaters( $params );
	}

	protected function parseResponse( TranslationQueryResponse $reply ) {
		$body = $reply->getBody();

		$text = preg_replace( '~<string.*>(.*)</string>~', '\\1', $body );
		$text = Sanitizer::decodeCharReferences( $text );
		$text = $this->unwrapUntranslatable( $text );

		return $text;
	}
}
