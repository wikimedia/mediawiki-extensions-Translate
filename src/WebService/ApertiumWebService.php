<?php

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

	public function getType() {
		return 'mt';
	}

	protected function mapCode( $code ) {
		return str_replace( '-', '_', LanguageCode::bcp47( $code ) );
	}

	protected function doPairs() {
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

	protected function getQuery( $text, $from, $to ) {
		if ( !isset( $this->config['key'] ) ) {
			throw new TranslationWebServiceConfigurationException( 'API key is not set' );
		}

		$text = trim( $text );
		$text = $this->wrapUntranslatable( $text );

		$params = [
			'q' => $text,
			'langpair' => "$from|$to",
			'x-application' => 'MediaWiki Translate extension ' . TranslateUtils::getVersion(),
		];

		return TranslationQuery::factory( $this->config['url'] )
			->timeout( $this->config['timeout'] )
			->queryParameters( $params );
	}

	protected function parseResponse( TranslationQueryResponse $reply ) {
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
