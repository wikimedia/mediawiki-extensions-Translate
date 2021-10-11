<?php
/**
 * Contains a class for querying external translation service.
 *
 * @file
 * @author Carsten Schmitz / LimeSurvey GmbH
 * @license GPL-2.0-or-later
 */

use MediaWiki\MediaWikiServices;

/**
 * Implements support for Google Translate API
 * @see https://cloud.google.com/translate/docs/reference/rest
 * @ingroup TranslationWebService
 * @since 2020.05
 */
class GoogleTranslateWebService extends TranslationWebService {
	private const PUBLIC_API = 'https://translation.googleapis.com/language/translate/v2';

	/** @inheritDoc */
	public function getType() {
		return 'mt';
	}

	/** @inheritDoc */
	protected function mapCode( $code ) {
		/** @phpcs-require-sorted-array */
		$map = [
			'be-tarask' => 'be',
			'zh-cn' => 'zh-CN',
			'zh-hans' => 'zh-CN',
			'zh-hant' => 'zh-TW',
			'zh-tw' => 'zh-TW',
		];

		return $map[$code] ?? $code;
	}

	/** @inheritDoc */
	public function isSupportedLanguagePair( $from, $to ) {
		$pairs = $this->getSupportedLanguagePairs();
		$from = $this->mapCode( $from );
		$to = $this->mapCode( $to );

		// As long as the source & target language exist at Google it is fine
		return isset( $pairs[$from] ) && isset( $pairs[$to] ) && $from !== $to;
	}

	/** @inheritDoc */
	protected function doPairs() {
		if ( !isset( $this->config['key'] ) ) {
			throw new TranslationWebServiceConfigurationException( 'API key is not set' );
		}

		$api = $this->config['pairs'] ?? self::PUBLIC_API . '/languages';
		$params = [
			'key' => $this->config['key'],
		];

		$json = MediaWikiServices::getInstance()->getHttpRequestFactory()->get(
			wfAppendQuery( $api, wfArrayToCgi( $params ) ),
			[ 'timeout' => $this->config['timeout'] ],
			__METHOD__
		);
		$response = FormatJson::decode( $json );

		if ( !is_object( $response ) ) {
			$exception = 'Malformed reply from remote server: ' . (string)$json;
			throw new TranslationWebServiceException( $exception );
		}

		$pairs = [];
		foreach ( $response->data->languages as $language ) {
			// Google can translate from any language to any language
			$pairs[$language->language] = true;
		}

		return $pairs;
	}

	/** @inheritDoc */
	protected function getQuery( $text, $from, $to ) {
		if ( !isset( $this->config['key'] ) ) {
			throw new TranslationWebServiceConfigurationException( 'API key is not set' );
		}
		# https://cloud.google.com/translate/docs/reference/translate
		if ( strlen( $text ) > 10000 ) {
			// There is no limitation but we don't want the translation service to be abused, don't we?
			throw new TranslationWebServiceInvalidInputException( 'Source text too long' );
		}

		$url = $this->config['url'] ?? self::PUBLIC_API;
		$text = trim( $text );
		$text = $this->wrapUntranslatable( $text );

		return TranslationQuery::factory( $url )
			->timeout( $this->config['timeout'] ?? 3 )
			->postWithData( wfArrayToCgi( [
				'key' => $this->config['key'],
				'q' => $text,
				'target' => $to,
				'source' => $from,
				'format' => 'html',
			] ) );
	}

	/** @inheritDoc */
	protected function parseResponse( TranslationQueryResponse $reply ) {
		$body = $reply->getBody();
		$response = FormatJson::decode( $body );
		if ( !is_object( $response ) ) {
			throw new TranslationWebServiceException( 'Invalid json: ' . serialize( $body ) );
		}
		$text = Sanitizer::decodeCharReferences( $response->data->translations[0]->translatedText );
		$text = $this->unwrapUntranslatable( $text );

		return trim( $text );
	}
}
