<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\WebService;

use MediaWiki\Http\HttpRequestFactory;

/**
 * Implements support for Microsoft translation api v3.
 * @author Niklas Laxström
 * @author Ulrich Strauss
 * @license GPL-2.0-or-later
 * @since 2013-01-01
 * @see https://docs.microsoft.com/fi-fi/azure/cognitive-services/Translator/reference/v3-0-reference
 * @ingroup TranslationWebService
 */
class MicrosoftWebService extends TranslationWebService {
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
		$map = [
			'tl' => 'fil',
			'zh-hant' => 'zh-Hant',
			'zh-hans' => 'zh-Hans',
			'sr-ec' => 'sr-Cyrl',
			'sr-el' => 'sr-Latn',
			'pt-br' => 'pt',
		];

		return $map[$code] ?? $code;
	}

	/** @inheritDoc */
	protected function doPairs(): array {
		if ( !isset( $this->config['key'] ) ) {
			throw new TranslationWebServiceConfigurationException( 'key is not set' );
		}

		$key = $this->config['key'];

		$options = [];
		$options['method'] = 'GET';
		$options['timeout'] = $this->config['timeout'];

		$url = $this->config['url'] . '/languages?api-version=3.0';

		$req = $this->httpRequestFactory->create( $url, $options, __METHOD__ );
		$req->setHeader( 'Ocp-Apim-Subscription-Key', $key );

		$status = $req->execute();
		if ( !$status->isOK() ) {
			$error = $req->getContent();
			// Most likely a timeout or other general error
			throw new TranslationWebServiceException(
				'HttpRequestFactory::get failed:' . serialize( $error ) . serialize( $status )
			);
		}

		$json = $req->getContent();
		$response = json_decode( $json, true );
		if ( !isset( $response[ 'translation' ] ) ) {
			throw new TranslationWebServiceException(
				'Unable to fetch list of available languages: ' . $json
			);
		}

		$languages = array_keys( $response[ 'translation' ] );

		// Let's make a cartesian product, assuming we can translate from any language to any language
		$pairs = [];
		foreach ( $languages as $from ) {
			foreach ( $languages as $to ) {
				$pairs[$from][$to] = true;
			}
		}

		return $pairs;
	}

	/** @inheritDoc */
	protected function getQuery( string $text, string $sourceLanguage, string $targetLanguage ): TranslationQuery {
		if ( !isset( $this->config['key'] ) ) {
			throw new TranslationWebServiceConfigurationException( 'key is not set' );
		}

		$key = $this->config['key'];
		$text = trim( $text );
		$text = $this->wrapUntranslatable( $text );

		$url = $this->config['url'] . '/translate';
		$params = [
			'api-version' => '3.0',
			'from' => $sourceLanguage,
			'to' => $targetLanguage,
			'textType' => 'html',
		];
		$headers = [
			'Ocp-Apim-Subscription-Key' => $key,
			'Content-Type' => 'application/json',
		];
		$body = json_encode( [ [ 'Text' => $text ] ] );

		if ( $body === false ) {
			throw new TranslationWebServiceInvalidInputException( 'Could not JSON encode source text' );
		}

		if ( strlen( $body ) > 5000 ) {
			throw new TranslationWebServiceInvalidInputException( 'Source text too long' );
		}

		return TranslationQuery::factory( $url )
			->timeout( intval( $this->config['timeout'] ) )
			->queryParameters( $params )
			->queryHeaders( $headers )
			->postWithData( $body );
	}

	/** @inheritDoc */
	protected function parseResponse( TranslationQueryResponse $reply ): string {
		$body = $reply->getBody();

		$response = json_decode( $body, true );
		if ( !isset( $response[ 0 ][ 'translations' ][ 0 ][ 'text' ] ) ) {
			throw new TranslationWebServiceException(
				'Unable to parse translation response: ' . $body
			);
		}

		$text = $response[ 0 ][ 'translations' ][ 0 ][ 'text' ];
		$text = $this->unwrapUntranslatable( $text );

		return $text;
	}

	/** @inheritDoc */
	protected function wrapUntranslatable( string $text ): string {
		$pattern = '~%[^% ]+%|\$\d|{VAR:[^}]+}|{?{(PLURAL|GRAMMAR|GENDER):[^|]+\||%(\d\$)?[sd]~';
		$wrap = '<span class="notranslate">\0</span>';
		return preg_replace( $pattern, $wrap, $text );
	}

	/** @inheritDoc */
	protected function unwrapUntranslatable( string $text ): string {
		$pattern = '~<span class="notranslate">\s*(.*?)\s*</span>~';
		return preg_replace( $pattern, '\1', $text );
	}
}
