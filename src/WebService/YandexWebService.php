<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\WebService;

use MediaWiki\Http\HttpRequestFactory;
use MediaWiki\Json\FormatJson;
use MediaWiki\Parser\Sanitizer;

/**
 * Implements support for Yandex translation API v1.
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @since 2013.01
 * @ingroup TranslationWebService
 * @see https://tech.yandex.com/translate/
 */
class YandexWebService extends TranslationWebService {
	private HttpRequestFactory $httpRequestFactory;

	public function __construct(
		HttpRequestFactory $httpRequestFactory,
		string $serviceName,
		array $config
	) {
		parent::__construct( $serviceName, $config );
		$this->httpRequestFactory = $httpRequestFactory;

		if ( !isset( $this->config['key'] ) ) {
			throw new TranslationWebServiceConfigurationException( 'API key is not set' );
		}
	}

	/** @inheritDoc */
	public function getType(): string {
		return 'mt';
	}

	/** @inheritDoc */
	protected function mapCode( string $code ): string {
		if ( $code === 'be-tarask' ) {
			$code = 'be';
		}
		return $code;
	}

	/** @inheritDoc */
	protected function doPairs(): array {
		$url = $this->config['pairs'] . '?' . wfArrayToCgi( [ 'key' => $this->config['key'] ] );
		$json = $this->httpRequestFactory->get( $url, [ 'timeout' => $this->config['timeout'] ], __METHOD__ );
		if ( $json === null ) {
			throw new TranslationWebServiceException( 'Failure encountered when contacting remote server' );
		}

		$response = FormatJson::decode( $json );
		if ( !is_object( $response ) ) {
			throw new TranslationWebServiceException( 'Malformed reply from remote server: ' . $json );
		}

		$pairs = [];
		foreach ( $response->dirs as $pair ) {
			[ $source, $target ] = explode( '-', $pair );
			$pairs[$source][$target] = true;
		}

		return $pairs;
	}

	/** @inheritDoc */
	protected function getQuery( string $text, string $sourceLanguage, string $targetLanguage ): TranslationQuery {
		# https://tech.yandex.com/translate/doc/dg/reference/translate-docpage/
		if ( strlen( $text ) > 10000 ) {
			throw new TranslationWebServiceInvalidInputException( 'Source text too long' );
		}

		$text = $this->wrapUntranslatable( trim( $text ) );

		return TranslationQuery::factory( $this->config['url'] )
			->timeout( intval( $this->config['timeout'] ) )
			->postWithData( wfArrayToCgi(
				[
					'key' => $this->config['key'],
					'text' => $text,
					'lang' => "$sourceLanguage-$targetLanguage",
					'format' => 'html',
				]
			) );
	}

	/** @inheritDoc */
	protected function parseResponse( TranslationQueryResponse $response ): string {
		$body = $response->getBody();
		$responseBody = FormatJson::decode( $body );
		if ( !is_object( $responseBody ) ) {
			throw new TranslationWebServiceException( 'Invalid json: ' . serialize( $body ) );
		} elseif ( $responseBody->code !== 200 ) {
			throw new TranslationWebServiceException( $responseBody->message );
		}

		$text = Sanitizer::decodeCharReferences( $responseBody->text[0] );
		$text = $this->unwrapUntranslatable( $text );

		return trim( $text );
	}
}
