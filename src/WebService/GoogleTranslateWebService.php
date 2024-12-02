<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\WebService;

use MediaWiki\Http\HttpRequestFactory;
use MediaWiki\Json\FormatJson;
use MediaWiki\Parser\Sanitizer;

/**
 * Implements support for Google Translate API
 * @author Carsten Schmitz / LimeSurvey GmbH
 * @license GPL-2.0-or-later
 * @since 2020.05
 * @ingroup TranslationWebService
 * @see https://cloud.google.com/translate/docs/reference/rest
 */
class GoogleTranslateWebService extends TranslationWebService {
	private const PUBLIC_API = 'https://translation.googleapis.com/language/translate/v2';
	private HttpRequestFactory $httpRequestFactory;

	public function __construct(
		HttpRequestFactory $httpRequestFactory,
		string $serviceName,
		array $config
	) {
		parent::__construct( $serviceName, $config );
		$this->httpRequestFactory = $httpRequestFactory;
	}

	/** @inheritDoc */
	public function getType(): string {
		return 'mt';
	}

	/** @inheritDoc */
	protected function mapCode( string $code ): string {
		/** @phpcs-require-sorted-array */
		$map = [
			'be-tarask' => 'be',
			'nb' => 'no',
			'tw' => 'ak',
			'zh-cn' => 'zh-CN',
			'zh-hans' => 'zh-CN',
			'zh-hant' => 'zh-TW',
			'zh-tw' => 'zh-TW',
		];

		return $map[$code] ?? $code;
	}

	/** @inheritDoc */
	public function isSupportedLanguagePair( string $sourceLanguage, string $targetLanguage ): bool {
		$pairs = $this->getSupportedLanguagePairs();
		$from = $this->mapCode( $sourceLanguage );
		$to = $this->mapCode( $targetLanguage );

		// As long as the source & target language exist at Google it is fine
		return isset( $pairs[$from] ) && isset( $pairs[$to] ) && $from !== $to;
	}

	/** @inheritDoc */
	protected function doPairs(): array {
		if ( !isset( $this->config['key'] ) ) {
			throw new TranslationWebServiceConfigurationException( 'API key is not set' );
		}

		$api = $this->config['pairs'] ?? self::PUBLIC_API . '/languages';
		$json = $this->httpRequestFactory->get(
			wfAppendQuery( $api, [ 'key' => $this->config['key'], ] ),
			[ 'timeout' => $this->config['timeout'] ],
			__METHOD__
		);
		if ( $json === null ) {
			throw new TranslationWebServiceException( 'Failure encountered when contacting remote server' );
		}

		$response = FormatJson::decode( $json );
		if ( !is_object( $response ) ) {
			throw new TranslationWebServiceException( 'Malformed reply from remote server: ' . $json );
		}

		$pairs = [];
		foreach ( $response->data->languages as $language ) {
			// Google can translate from any language to any language
			$pairs[$language->language] = true;
		}

		return $pairs;
	}

	/** @inheritDoc */
	protected function getQuery( string $text, string $sourceLanguage, string $targetLanguage ): TranslationQuery {
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
			->timeout( intval( $this->config['timeout'] ?? 3 ) )
			->postWithData( wfArrayToCgi( [
				'key' => $this->config['key'],
				'q' => $text,
				'target' => $targetLanguage,
				'source' => $sourceLanguage,
				'format' => 'html',
			] ) );
	}

	/** @inheritDoc */
	protected function parseResponse( TranslationQueryResponse $response ): string {
		$body = $response->getBody();
		$responseBody = FormatJson::decode( $body );
		if ( !is_object( $responseBody ) ) {
			throw new TranslationWebServiceException( 'Invalid json: ' . serialize( $body ) );
		}
		$text = Sanitizer::decodeCharReferences( $responseBody->data->translations[0]->translatedText );
		$text = $this->unwrapUntranslatable( $text );

		return trim( $text );
	}
}
