<?php
/**
 * Contains a class for querying external translation service.
 *
 * @file
 * @author Niklas Laxström
 * @author Ulrich Strauss
 * @license GPL-2.0-or-later
 */

/**
 * Implements support for Microsoft translation api v3.
 * @see https://docs.microsoft.com/fi-fi/azure/cognitive-services/Translator/reference/v3-0-reference
 * @ingroup TranslationWebService
 * @since 2013-01-01
 */
class MicrosoftWebService extends TranslationWebService {
	public function getType() {
		return 'mt';
	}

	protected function mapCode( $code ) {
		$map = [
			'tl' => 'fil',
			'zh-hant' => 'zh-Hant',
			'zh-hans' => 'zh-Hans',
			'sr-ec' => 'sr-Cyrl',
			'sr-el' => 'sr-Latn',
			'pt-br' => 'pt',
		];

		return $map[$code] ?? $code;
	}

	protected function doPairs() {
		if ( !isset( $this->config['key'] ) ) {
			throw new TranslationWebServiceConfigurationException( 'key is not set' );
		}

		$key = $this->config['key'];

		$options = [];
		$options['method']  = 'GET';
		$options['timeout'] = $this->config['timeout'];

		$url = $this->config['url'] . '/languages?api-version=3.0';

		$req = MWHttpRequest::factory( $url, $options );
		$req->setHeader( 'Ocp-Apim-Subscription-Key', $key );

		$status = $req->execute();
		if ( !$status->isOK() ) {
			$error = $req->getContent();
			// Most likely a timeout or other general error
			throw new TranslationWebServiceException(
				'Http::get failed:' . serialize( $error ) . serialize( $status )
			);
		}

		$json = $req->getContent();
		$response = json_decode( $json, true );
		if ( !isset( $response[ 'translation' ] ) ) {
			throw new TranslationWebServiceException(
				'Unable to fetch list of available languages: ' . $json
			);
		}

		$languages = array_keys( $response[ 'translation' ] );

		// Let's make a cartesian product, assuming we can translate from any language to any language
		$pairs = [];
		foreach ( $languages as $from ) {
			foreach ( $languages as $to ) {
				$pairs[$from][$to] = true;
			}
		}

		return $pairs;
	}

	protected function getQuery( $text, $from, $to ) {
		if ( !isset( $this->config['key'] ) ) {
			throw new TranslationWebServiceConfigurationException( 'key is not set' );
		}

		$key = $this->config['key'];
		$text = trim( $text );
		$text = $this->wrapUntranslatable( $text );

		$url = $this->config['url'] . '/translate';
		$params = [
			'api-version' => '3.0',
			'from' => $from,
			'to' => $to,
			'textType' => 'html',
		];
		$headers = [
			'Ocp-Apim-Subscription-Key' => $key,
			'Content-Type' => 'application/json',
		];
		$body = json_encode( [ [ 'Text' => $text ] ] );

		if ( strlen( $body ) > 5000 ) {
			throw new TranslationWebServiceInvalidInputException( 'Source text too long' );
		}

		return TranslationQuery::factory( $url )
			->timeout( $this->config['timeout'] )
			->queryParameters( $params )
			->queryHeaders( $headers )
			->postWithData( $body );
	}

	protected function parseResponse( TranslationQueryResponse $reply ) {
		$body = $reply->getBody();

		$response = json_decode( $body, true );
		if ( !isset( $response[ 0 ][ 'translations' ][ 0 ][ 'text' ] ) ) {
			throw new TranslationWebServiceException(
				'Unable to parse translation response: ' . $body
			);
		}

		$text = $response[ 0 ][ 'translations' ][ 0 ][ 'text' ];
		$text = $this->unwrapUntranslatable( $text );

		return $text;
	}

	/// Override from parent
	protected function wrapUntranslatable( $text ) {
		$pattern = '~%[^% ]+%|\$\d|{VAR:[^}]+}|{?{(PLURAL|GRAMMAR|GENDER):[^|]+\||%(\d\$)?[sd]~';
		$wrap = '<span class="notranslate">\0</span>';
		return preg_replace( $pattern, $wrap, $text );
	}

	/// Override from parent
	protected function unwrapUntranslatable( $text ) {
		$pattern = '~<span class="notranslate">\s*(.*?)\s*</span>~';
		return preg_replace( $pattern, '\1', $text );
	}
}
