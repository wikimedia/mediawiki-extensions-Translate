<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\WebService;

use FormatJson;
use LanguageCode;
use MediaWiki\Http\HttpRequestFactory;
use Sanitizer;
use TranslateUtils;

/**
 * Implements support Apetrium translator api.
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @ingroup TranslationWebService
 * @since 2013-01-01
 * @see https://wiki.apertium.org/wiki/Apertium_web_service
 */
class ApertiumWebService extends TranslationWebService {
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
		return str_replace( '-', '_', LanguageCode::bcp47( $code ) );
	}

	/** @inheritDoc */
	protected function doPairs(): array {
		$pairs = [];
		$json = $this->httpRequestFactory->get(
			$this->config['pairs'],
			[ 'timeout' => $this->config['timeout'] ],
			__METHOD__
		);
		$response = FormatJson::decode( $json );

		if ( !is_object( $response ) ) {
			$error = 'Malformed reply from remote server: ' . (string)$json;
			throw new TranslationWebServiceException( $error );
		}

		foreach ( $response->responseData as $pair ) {
			$source = $pair->sourceLanguage;
			$target = $pair->targetLanguage;
			$pairs[$source][$target] = true;
		}

		return $pairs;
	}

	/** @inheritDoc */
	protected function getQuery( string $text, string $sourceLanguage, string $targetLanguage ): TranslationQuery {
		if ( !isset( $this->config['key'] ) ) {
			throw new TranslationWebServiceConfigurationException( 'API key is not set' );
		}

		$text = trim( $text );
		$text = $this->wrapUntranslatable( $text );

		$params = [
			'q' => $text,
			'langpair' => "$sourceLanguage|$targetLanguage",
			'x-application' => 'MediaWiki Translate extension ' . TranslateUtils::getVersion(),
		];

		return TranslationQuery::factory( $this->config['url'] )
			->timeout( intval( $this->config['timeout'] ) )
			->queryParameters( $params );
	}

	/** @inheritDoc */
	protected function parseResponse( TranslationQueryResponse $reply ): string {
		$body = $reply->getBody();
		$response = FormatJson::decode( $body );
		if ( !is_object( $response ) ) {
			throw new TranslationWebServiceException( 'Invalid json: ' . serialize( $body ) );
		} elseif ( $response->responseStatus !== 200 ) {
			throw new TranslationWebServiceException( $response->responseDetails );
		}

		$text = Sanitizer::decodeCharReferences( $response->responseData->translatedText );
		$text = $this->unwrapUntranslatable( $text );

		return trim( $text );
	}
}
