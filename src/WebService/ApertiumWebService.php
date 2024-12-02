<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\WebService;

use MediaWiki\Extension\Translate\Utilities\Utilities;
use MediaWiki\Http\HttpRequestFactory;
use MediaWiki\Json\FormatJson;
use MediaWiki\Language\LanguageCode;
use MediaWiki\Parser\Sanitizer;

/**
 * Implements support Apertium translator API.
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @ingroup TranslationWebService
 * @since 2013.01
 * @see https://wiki.apertium.org/wiki/Apertium_web_service
 */
class ApertiumWebService extends TranslationWebService {
	// Exclusions per https://phabricator.wikimedia.org/T177434
	private const EXCLUDED_TARGET_LANGUAGES = [ 'fr', 'es', 'nl' ];
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
		return str_replace( '-', '_', LanguageCode::bcp47( $code ) );
	}

	/** @inheritDoc */
	protected function doPairs(): array {
		$json = $this->httpRequestFactory->get(
			$this->config['pairs'],
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
		foreach ( $response->responseData as $pair ) {
			$source = $pair->sourceLanguage;
			$target = $pair->targetLanguage;
			if ( !in_array( $target, self::EXCLUDED_TARGET_LANGUAGES ) ) {
				$pairs[$source][$target] = true;
			}
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
			'x-application' => 'MediaWiki Translate extension ' . Utilities::getVersion(),
		];

		return TranslationQuery::factory( $this->config['url'] )
			->timeout( intval( $this->config['timeout'] ) )
			->queryParameters( $params );
	}

	/** @inheritDoc */
	protected function parseResponse( TranslationQueryResponse $response ): string {
		$body = $response->getBody();
		$responseBody = FormatJson::decode( $body );
		if ( !is_object( $responseBody ) ) {
			throw new TranslationWebServiceException( 'Invalid json: ' . serialize( $body ) );
		} elseif ( $responseBody->responseStatus !== 200 ) {
			throw new TranslationWebServiceException( $responseBody->responseDetails );
		}

		$text = Sanitizer::decodeCharReferences( $responseBody->responseData->translatedText );
		$text = $this->unwrapUntranslatable( $text );

		return trim( $text );
	}
}
