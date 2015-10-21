<?php
/**
 * Contains a class for querying external translation service.
 *
 * @file
 * @author Xi Gu
 * @license GPL-2.0+
 */
/**
 * Implements support for youdao dict.
 * @see http://fanyi.youdao.com/openapi?path=data-mode
 * @ingroup TranslationWebService
 * @since 2013-01-01
 */
class YoudaoWebService extends TranslationWebService {
	protected function mapCode( $code ) {
		$map = array(
			'zh-hant' => 'zh',
			'zh-hans' => 'zh',
			'zh-cn' => 'zh',
			'zh-hk' => 'zh',
			'zh-tw' => 'zh',
		);
		return isset( $map[$code] ) ? $map[$code] : $code;
	}
	protected function doPairs() {
		if ( !isset( $this->config['key'] ) ) {
			throw new TranslationWebServiceException( 'API key is not set' );
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
			'q' => $text,
			'key' => $this->config['key'],
			'keyfrom' => $this->config['keyfrom'],
			'type' => 'data',
			'doctype' => 'xml',
			'version' => '1.1',
		);
		$url = 'http://fanyi.youdao.com/openapi.do?';
		return TranslationQuery::factory( $this->config['url'] )
			->timeout( $this->config['timeout'] )
			->queryParamaters( $params );
	}
	protected function parseResponse( TranslationQueryResponse $reply ) {
		$xml = simplexml_load_string( $reply->getBody() );
		$text = Sanitizer::decodeCharReferences( $xml->translation->paragraph );
		$text = $this->unwrapUntranslatable( $text );
		return $text;
	}
}
