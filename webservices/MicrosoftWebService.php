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
			'zh' => 'zh-CHS',
			'zh-cn' => 'zh-CHS',
			'zh-hk' => 'zh-CHT',
			'zh-tw' => 'zh-CHT',
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
			$exception = 'Http request failed:' . serialize( $error ) . serialize( $status );
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
		// add a few more hacks 
		$pairs['en']['zh-cn'] = true;
		$pairs['en']['zh-tw'] = true;
		$pairs['en']['zh-hk'] = true;
		$pairs['en']['zh-hans'] = true;
		$pairs['en']['zh-hant'] = true;
		$pairs['en']['zh'] = true;
                $pairs['es']['zh-cn'] = true;
                $pairs['es']['zh-tw'] = true;
                $pairs['es']['zh-hk'] = true;
                $pairs['es']['zh-hans'] = true;
                $pairs['es']['zh-hant'] = true;
                $pairs['es']['zh'] = true;
                $pairs['jp']['zh-cn'] = true;
                $pairs['jp']['zh-tw'] = true;
                $pairs['jp']['zh-hk'] = true;
                $pairs['jp']['zh-hans'] = true;
                $pairs['jp']['zh-hant'] = true;
                $pairs['jp']['zh'] = true;
                $pairs['kr']['zh-cn'] = true;
                $pairs['kr']['zh-tw'] = true;
                $pairs['kr']['zh-hk'] = true;
                $pairs['kr']['zh-hans'] = true;
                $pairs['kr']['zh-hant'] = true;
                $pairs['kr']['zh'] = true;
		$pairs['fr']['zh-cn'] = true;
                $pairs['fr']['zh-tw'] = true;
                $pairs['fr']['zh-hk'] = true;
                $pairs['fr']['zh-hans'] = true;
                $pairs['fr']['zh-hant'] = true;
                $pairs['fr']['zh'] = true;
                $pairs['ru']['zh-cn'] = true;
                $pairs['ru']['zh-tw'] = true;
                $pairs['ru']['zh-hk'] = true;
                $pairs['ru']['zh-hans'] = true;
                $pairs['ru']['zh-hant'] = true;
                $pairs['ru']['zh'] = true;		
                $pairs['zh-cn']['en'] = true;
                $pairs['zh-tw']['en'] = true;
                $pairs['zh-hk']['en'] = true;
                $pairs['zh-hans']['en'] = true;
                $pairs['zh-hant']['en'] = true;
                $pairs['zh']['en'] = true;
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

		$url = 'http://api.microsofttranslator.com/V2/Http.svc/Translate?';
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
