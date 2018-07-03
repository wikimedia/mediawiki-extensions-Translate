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
 * Implements support for Microsoft translation api v2.
 * @see https://msdn.microsoft.com/en-us/library/ff512421.aspx
 * @ingroup TranslationWebService
 * @since 2013-01-01
 */
class MicrosoftWebService extends TranslationWebService {
	public function getType() {
		return 'mt';
	}

	protected function mapCode( $code ) {
		$map = [
			'zh-hant' => 'zh-CHT',
			'zh-hans' => 'zh-CHS',
		];

		return isset( $map[$code] ) ? $map[$code] : $code;
	}

	protected function doPairs() {
		if ( !isset( $this->config['key'] ) ) {
			throw new TranslationWebServiceConfigurationException( 'key is not set' );
		}

		$key = $this->config['key'];

		$options = [];
		$options['method']  = 'GET';
		$options['timeout'] = $this->config['timeout'];

		$url = 'http://api.microsofttranslator.com/V2/Http.svc/GetLanguagesForTranslate?';

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
		$xml = simplexml_load_string( $req->getContent() );

		$languages = [];
		foreach ( $xml->string as $language ) {
			$languages[] = (string)$language;
		}

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

		$params = [
			'text' => $text,
			'from' => $from,
			'to' => $to,
		];
		$headers = [
			'Ocp-Apim-Subscription-Key' => $key,
		];

		return TranslationQuery::factory( $this->config['url'] )
			->timeout( $this->config['timeout'] )
			->queryParameters( $params )
			->queryHeaders( $headers );
	}

	protected function parseResponse( TranslationQueryResponse $reply ) {
		$body = $reply->getBody();

		$text = preg_replace( '~<string.*>(.*)</string>~s', '\\1', $body );
		$text = Sanitizer::decodeCharReferences( $text );
		$text = $this->unwrapUntranslatable( $text );

		return $text;
	}

	/// Override from parent
	protected function wrapUntranslatable( $text ) {
		$pattern = '~%[^% ]+%|\$\d|{VAR:[^}]+}|{?{(PLURAL|GRAMMAR|GENDER):[^|]+\||%(\d\$)?[sd]~';
		$wrap = '<span translate="no">\0</span>';
		return preg_replace( $pattern, $wrap, $text );
	}

	/// Override from parent
	protected function unwrapUntranslatable( $text ) {
		$pattern = '~<span translate="no">(.*?)</span>~';
		return preg_replace( $pattern, '\1', $text );
	}
}
