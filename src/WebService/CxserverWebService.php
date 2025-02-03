<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\WebService;

use MediaWiki\Http\HttpRequestFactory;
use MediaWiki\Json\FormatJson;

/**
 * Used for interacting with translation services supported by Cxserver
 * @ingroup TranslationWebService
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 * @since 2023.06
 */
abstract class CxserverWebService extends TranslationWebService {
	private HttpRequestFactory $httpRequestFactory;

	public function __construct(
		HttpRequestFactory $httpRequestFactory,
		string $service,
		array $config
	) {
		parent::__construct( $service, $config );
		if ( !isset( $this->config['host'] ) ) {
			throw new TranslationWebServiceConfigurationException( 'Cxserver host not set' );
		}
		$this->httpRequestFactory = $httpRequestFactory;
	}

	public function getType(): string {
		return 'mt';
	}

	protected function mapCode( string $code ): string {
		return $code;
	}

	protected function doPairs(): array {
		$url = $this->config['host'] . '/v2/list/mt';
		$json = $this->httpRequestFactory->get( $url, [ $this->config['timeout'] ], __METHOD__ );
		if ( $json === null ) {
			throw new TranslationWebServiceException( 'Failure encountered when contacting remote server' );
		}

		$response = FormatJson::decode( $json, true );
		if ( !is_array( $response ) ) {
			throw new TranslationWebServiceException( 'Malformed reply from remote server: ' . $json );
		}

		return $this->handlePairsForService( $response );
	}

	protected function getQuery( string $text, string $sourceLanguage, string $targetLanguage ): TranslationQuery {
		$text = trim( $text );
		$text = $this->wrapUntranslatable( $text );
		$url = $this->config['host'] . "/v2/mt/$sourceLanguage/$targetLanguage/{$this->getServiceName()}";

		return TranslationQuery::factory( $url )
			->timeout( intval( $this->config['timeout'] ) )
			->postWithData( wfArrayToCgi( [ 'html' => $text ] ) );
	}

	protected function parseResponse( TranslationQueryResponse $response ): string {
		$body = $response->getBody();
		$parsedBody = FormatJson::decode( $body, true );
		if ( !is_array( $parsedBody ) ) {
			throw new TranslationWebServiceException( 'Invalid json: ' . serialize( $body ) );
		}

		return $this->handleServiceResponse( $parsedBody );
	}

	abstract protected function handlePairsForService( array $response ): array;

	abstract protected function getServiceName(): string;

	abstract protected function handleServiceResponse( array $responseBody ): string;
}
