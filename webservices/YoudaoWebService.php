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
	public function getType() {
		return 'mt';
	}
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
		$pairs = array(
			'en' => 'zh',
			'jp' => 'zh',
			'fr' => 'zh',
			'kr' => 'zh',
			'ru' => 'zh',
			'es' => 'zh',
			'zh' => 'en',
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
		$body = $reply->getBody();
		$text = preg_replace( '~<paragraph>(.*)</paragraph>~', '\\1', $body );
		$text = Sanitizer::decodeCharReferences( $text );
		$text = $this->unwrapUntranslatable( $text );
		return $text;
	}
}
