<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\WebService;

use FormatJson;
use MediaWiki\Http\HttpRequestFactory;
use Sanitizer;

/**
 * Implements support for Yandex translation api v1.
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @since 2013-01-01
 * @ingroup TranslationWebService
 * @see https://tech.yandex.com/translate/
 */
class YandexWebService extends TranslationWebService {
	/** @var HttpRequestFactory */
	private $httpRequestFactory;

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
		if ( $code === 'be-tarask' ) {
			$code = 'be';
		}
		return $code;
	}

	/** @inheritDoc */
	protected function doPairs(): array {
		if ( !isset( $this->config['key'] ) ) {
			throw new TranslationWebServiceConfigurationException( 'API key is not set' );
		}

		$pairs = [];

		$params = [
			'key' => $this->config['key'],
		];

		$url = $this->config['pairs'] . '?' . wfArrayToCgi( $params );
		$json = $this->httpRequestFactory->get( $url, [ 'timeout' => $this->config['timeout'] ], __METHOD__ );
		$response = FormatJson::decode( $json );

		if ( !is_object( $response ) ) {
			$exception = 'Malformed reply from remote server: ' . (string)$json;
			throw new TranslationWebServiceException( $exception );
		}

		foreach ( $response->dirs as $pair ) {
			list( $source, $target ) = explode( '-', $pair );
			$pairs[$source][$target] = true;
		}

		return $pairs;
	}

	/** @inheritDoc */
	protected function getQuery( string $text, string $sourceLanguage, string $targetLanguage ): TranslationQuery {
		if ( !isset( $this->config['key'] ) ) {
			throw new TranslationWebServiceConfigurationException( 'API key is not set' );
		}

		# https://tech.yandex.com/translate/doc/dg/reference/translate-docpage/
		if ( strlen( $text ) > 10000 ) {
			throw new TranslationWebServiceInvalidInputException( 'Source text too long' );
		}

		$text = trim( $text );
		$text = $this->wrapUntranslatable( $text );

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
	protected function parseResponse( TranslationQueryResponse $reply ): string {
		$body = $reply->getBody();
		$response = FormatJson::decode( $body );
		if ( !is_object( $response ) ) {
			throw new TranslationWebServiceException( 'Invalid json: ' . serialize( $body ) );
		} elseif ( $response->code !== 200 ) {
			throw new TranslationWebServiceException( $response->message );
		}

		$text = Sanitizer::decodeCharReferences( $response->text[0] );
		$text = $this->unwrapUntranslatable( $text );

		return trim( $text );
	}
}
