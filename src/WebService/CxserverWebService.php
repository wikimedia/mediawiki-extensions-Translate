<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\WebService;

use FormatJson;
use MediaWiki\Http\HttpRequestFactory;

/**
 * Contains a class for querying external translation service.
 * Implements support for CXServer api
 * @ingroup TranslationWebService
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @since 2015.02
 */
class CxserverWebService extends TranslationWebService {
	// Exclusions per https://phabricator.wikimedia.org/T177434
	private const EXCLUDED_APERTIUM_TARGET_LANGUAGES = [ 'fr', 'es', 'nl' ];
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
		return $code;
	}

	/** @inheritDoc */
	protected function doPairs(): array {
		if ( !isset( $this->config['host'] ) ) {
			throw new TranslationWebServiceConfigurationException( 'Cxserver host not set' );
		}

		$url = $this->config['host'] . '/v1/list/mt';
		$json = $this->httpRequestFactory->get( $url, [ $this->config['timeout'] ], __METHOD__ );
		if ( $json === null ) {
			throw new TranslationWebServiceException( 'Failure encountered when contacting remote server' );
		}

		$response = FormatJson::decode( $json, true );
		if ( !is_array( $response ) ) {
			throw new TranslationWebServiceException( 'Malformed reply from remote server: ' . $json );
		}

		$pairs = [];
		foreach ( $response['Apertium'] as $source => $targets ) {
			$filteredTargets = array_diff( $targets, self::EXCLUDED_APERTIUM_TARGET_LANGUAGES );
			foreach ( $filteredTargets as $target ) {
				$pairs[$source][$target] = true;
			}
		}

		return $pairs;
	}

	/** @inheritDoc */
	protected function getQuery( string $text, string $sourceLanguage, string $targetLanguage ): TranslationQuery {
		if ( !isset( $this->config['host'] ) ) {
			throw new TranslationWebServiceConfigurationException( 'Cxserver host not set' );
		}

		$text = trim( $text );
		$text = $this->wrapUntranslatable( $text );
		$url = $this->config['host'] . "/v1/mt/$sourceLanguage/$targetLanguage/Apertium";

		return TranslationQuery::factory( $url )
			->timeout( intval( $this->config['timeout'] ) )
			->postWithData( wfArrayToCgi( [ 'html' => $text ] ) );
	}

	/** @inheritDoc */
	protected function parseResponse( TranslationQueryResponse $response ): string {
		$body = $response->getBody();
		$responseBody = FormatJson::decode( $body );
		if ( !is_object( $responseBody ) ) {
			throw new TranslationWebServiceException( 'Invalid json: ' . serialize( $body ) );
		}

		$text = $responseBody->contents;
		if ( preg_match( '~^<div>(.*)</div>$~', $text ) ) {
			$text = preg_replace( '~^<div>(.*)</div>$~', '\1', $text );
		}
		$text = $this->unwrapUntranslatable( $text );

		return trim( $text );
	}
}
