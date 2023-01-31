<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\WebService;

use Exception;
use MediaWiki\Logger\LoggerFactory;
use MediaWiki\MediaWikiServices;
use ObjectCache;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

/**
 * Multipurpose class:
 *  - 1) Interface for web services.
 *  - 2) Source text picking logic.
 *  - 3) Factory class.
 *  - 4) Service failure tracking and suspending.
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @since 2013-01-01
 * @defgroup TranslationWebService Translation Web Services
 */
abstract class TranslationWebService implements LoggerAwareInterface {
	/* Public api */

	/**
	 * Get a webservice handler.
	 * @see $wgTranslateTranslationServices
	 */
	public static function factory( string $serviceName, array $config ): ?TranslationWebService {
		$handlers = [
			'microsoft' => [
				'class' => MicrosoftWebService::class,
				'deps' => [ 'HttpRequestFactory' ]
			],
			'apertium' => [
				'class' => ApertiumWebService::class,
				'deps' => [ 'HttpRequestFactory' ]
			],
			'yandex' => [
				'class' => YandexWebService::class,
				'deps' => [ 'HttpRequestFactory' ]
			],
			'google' => [
				'class' => GoogleTranslateWebService::class,
				'deps' => [ 'HttpRequestFactory' ]
			],
			'remote-ttmserver' => [
				'class' => RemoteTTMServerWebService::class
			],
			'cxserver' => [
				'class' => CxserverWebService::class,
				'deps' => [ 'HttpRequestFactory' ]
			],
			'restbase' => [
				'class' => RESTBaseWebService::class,
				'deps' => [ 'HttpRequestFactory' ]
			],
			'caighdean' => [
				'class' => CaighdeanWebService::class
			],
		];

		if ( !isset( $config['timeout'] ) ) {
			$config['timeout'] = 3;
		}

		$serviceDetails = $handlers[$config['type']] ?? null;
		if ( $serviceDetails ) {
			$objectFactory = MediaWikiServices::getInstance()->getObjectFactory();
			$spec = [
				'class' => $serviceDetails['class'],
				'args' => [ $serviceName, $config ],
				'services' => $serviceDetails['deps'] ?? [],
			];

			// @phan-suppress-next-line PhanTypeInvalidCallableArraySize due to annotations on createObject?
			$serviceObject = $objectFactory->createObject( $spec );
			if ( $serviceObject instanceof LoggerAwareInterface ) {
				$serviceObject->setLogger( LoggerFactory::getInstance( 'translationservices' ) );
			}

			return $serviceObject;
		}

		return null;
	}

	/**
	 * Gets the name of this service, for example to display it for the user.
	 * @since 2014.02
	 */
	public function getName(): string {
		return $this->service;
	}

	/**
	 * Get queries for this service. Queries from multiple services can be
	 * collected and run asynchronously with QueryAggregator.
	 * @return TranslationQuery[]
	 * @since 2015.12
	 * @throws TranslationWebServiceConfigurationException
	 */
	public function getQueries( string $text, string $sourceLanguage, string $targetLanguage ): array {
		$from = $this->mapCode( $sourceLanguage );
		$to = $this->mapCode( $targetLanguage );

		try {
			return [ $this->getQuery( $text, $from, $to ) ];
		} catch ( TranslationWebServiceException $e ) {
			$this->reportTranslationServiceFailure( $e->getMessage() );
			return [];
		} catch ( TranslationWebServiceInvalidInputException $e ) {
			// Not much we can do about this, just ignore.
			return [];
		}
	}

	/**
	 * Get the web service specific response returned by QueryAggregator.
	 * @return mixed|null Returns null on error.
	 * @since 2015.12
	 */
	public function getResultData( TranslationQueryResponse $response ) {
		if ( $response->getStatusCode() !== 200 ) {
			$this->reportTranslationServiceFailure(
				'STATUS: ' . $response->getStatusMessage() . "\n" .
				'BODY: ' . $response->getBody()
			);
			return null;
		}

		try {
			return $this->parseResponse( $response );
		} catch ( TranslationWebServiceException $e ) {
			$this->reportTranslationServiceFailure( $e->getMessage() );
			return null;
		}
	}

	/**
	 * Returns the type of this web service.
	 * @see \MediaWiki\Extension\Translate\TranslatorInterface\Aid\TranslationAid::getTypes
	 */
	abstract public function getType(): string;

	/* Service api */

	/**
	 * Map a MediaWiki (almost standard) language code to the code used by the
	 * translation service.
	 */
	abstract protected function mapCode( string $code ): string;

	/**
	 * Get the list of supported language pairs for the web service. The codes
	 * should be the ones used by the service. Caching is handled by the public
	 * getSupportedLanguagePairs.
	 * @return array $list[source language][target language] = true
	 * @throws TranslationWebServiceException
	 * @throws TranslationWebServiceConfigurationException
	 */
	abstract protected function doPairs(): array;

	/**
	 * Get the query. See getQueries for the public method.
	 * @param string $text Text to translate.
	 * @param string $sourceLanguage Language code of the text, as used by the service.
	 * @param string $targetLanguage Language code of the translation, as used by the service.
	 * @since 2015.02
	 * @throws TranslationWebServiceException
	 * @throws TranslationWebServiceConfigurationException
	 * @throws TranslationWebServiceInvalidInputException
	 */
	abstract protected function getQuery(
		string $text, string $sourceLanguage, string $targetLanguage
	): TranslationQuery;

	/**
	 * Get the response. See getResultData for the public method.
	 * @since 2015.02
	 * @throws TranslationWebServiceException
	 */
	abstract protected function parseResponse( TranslationQueryResponse $response );

	/* Default implementation */

	/** @var string Name of this webservice. */
	protected $service;
	/** @var array */
	protected $config;
	/** @var LoggerInterface */
	protected $logger;

	public function __construct( string $service, array $config ) {
		$this->service = $service;
		$this->config = $config;
	}

	/**
	 * Test whether given language pair is supported by the service.
	 * @since 2015.12
	 * @throws TranslationWebServiceConfigurationException
	 */
	public function isSupportedLanguagePair( string $sourceLanguage, string $targetLanguage ): bool {
		$pairs = $this->getSupportedLanguagePairs();
		$from = $this->mapCode( $sourceLanguage );
		$to = $this->mapCode( $targetLanguage );

		return isset( $pairs[$from][$to] );
	}

	/**
	 * @see self::doPairs
	 * @throws TranslationWebServiceConfigurationException
	 */
	protected function getSupportedLanguagePairs(): array {
		$cache = ObjectCache::getInstance( CACHE_ANYTHING );

		return $cache->getWithSetCallback(
			$cache->makeKey( 'translate-tmsug-pairs-' . $this->service ),
			$cache::TTL_DAY,
			function ( &$ttl ) use ( $cache ) {
				try {
					$pairs = $this->doPairs();
				} catch ( Exception $e ) {
					$pairs = [];
					$this->reportTranslationServiceFailure( $e->getMessage() );
					$ttl = $cache::TTL_UNCACHEABLE;
				}

				return $pairs;
			}
		);
	}

	/**
	 * Some mangling that tries to keep some parts of the message unmangled
	 * by the translation service. Most of them support either class=notranslate
	 * or translate=no.
	 */
	protected function wrapUntranslatable( string $text ): string {
		$text = str_replace( "\n", '!N!', $text );
		$pattern = '~%[^% ]+%|\$\d|{VAR:[^}]+}|{?{(PLURAL|GRAMMAR|GENDER):[^|]+\||%(\d\$)?[sd]~';
		$wrap = '<span class="notranslate" translate="no">\0</span>';
		return preg_replace( $pattern, $wrap, $text );
	}

	/** Undo the hopyfully untouched mangling done by wrapUntranslatable. */
	protected function unwrapUntranslatable( string $text ): string {
		$text = str_replace( '!N!', "\n", $text );
		$pattern = '~<span class="notranslate" translate="no">(.*?)</span>~';
		return preg_replace( $pattern, '\1', $text );
	}

	/* Failure handling and suspending */

	public function setLogger( LoggerInterface $logger ): void {
		$this->logger = $logger;
	}

	/**
	 * @var int How many failures during failure period need to happen to
	 * consider the service being temporarily off-line.
	 */
	protected $serviceFailureCount = 5;
	/**
	 * @var int How long after the last detected failure we clear the status and
	 * try again.
	 */
	protected $serviceFailurePeriod = 900;

	/** Checks whether the service has exceeded failure count */
	public function checkTranslationServiceFailure(): bool {
		$service = $this->service;
		$cache = ObjectCache::getInstance( CACHE_ANYTHING );

		$key = $cache->makeKey( "translate-service-$service" );
		$value = $cache->get( $key );
		if ( !is_string( $value ) ) {
			return false;
		}

		list( $count, $failed ) = explode( '|', $value, 2 );

		if ( $failed + ( 2 * $this->serviceFailurePeriod ) < wfTimestamp() ) {
			if ( $count >= $this->serviceFailureCount ) {
				$this->logger->warning( "Translation service $service (was) restored" );
			}
			$cache->delete( $key );

			return false;
		} elseif ( $failed + $this->serviceFailurePeriod < wfTimestamp() ) {
			/* We are in suspicious mode and one failure is enough to update
			 * failed timestamp. If the service works however, let's use it.
			 * Previous failures are forgotten after another failure period
			 * has passed */
			return false;
		}

		// Check the failure count against the limit
		return $count >= $this->serviceFailureCount;
	}

	/** Increases the failure count for this service */
	protected function reportTranslationServiceFailure( string $msg ): void {
		$service = $this->service;
		$this->logger->warning( "Translation service $service problem: $msg" );

		$cache = ObjectCache::getInstance( CACHE_ANYTHING );
		$key = $cache->makeKey( "translate-service-$service" );

		$value = $cache->get( $key );
		if ( !is_string( $value ) ) {
			$count = 0;
		} else {
			list( $count, ) = explode( '|', $value, 2 );
		}

		$count++;
		$failed = wfTimestamp();
		$cache->set(
			$key,
			"$count|$failed",
			$this->serviceFailurePeriod * 5
		);

		if ( $count === $this->serviceFailureCount ) {
			$this->logger->error( "Translation service $service suspended" );
		} elseif ( $count > $this->serviceFailureCount ) {
			$this->logger->warning( "Translation service $service still suspended" );
		}
	}
}
