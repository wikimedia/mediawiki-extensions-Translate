<?php
/**
 * Contains code related to web service support.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0+
 */

/**
 * Multipurpose class:
 *  - 1) Interface for web services.
 *  - 2) Source text picking logic.
 *  - 3) Factory class.
 *  - 4) Service failure tracking and suspending.
 * @since 2013-01-01
 * @defgroup TranslationWebService Translation Web Services
 */
abstract class TranslationWebService {
	/* Public api */

	/**
	 * Get a webservice handler.
	 *
	 * @see $wgTranslateTranslationServices
	 * @param string $name Name of the service.
	 * @param array $config
	 * @return TranslationWebService|null
	 */
	public static function factory( $name, $config ) {
		$handlers = array(
			'microsoft' => 'MicrosoftWebService',
			'apertium' => 'ApertiumWebService',
			'yandex' => 'YandexWebService',
			'remote-ttmserver' => 'RemoteTTMServerWebService',
			'cxserver' => 'CxserverWebService',
		);

		if ( !isset( $config['timeout'] ) ) {
			$config['timeout'] = 3;
		}

		// Alter local ttmserver instance to appear as remote
		// to take advantage of the query aggregator. But only
		// if they are public.
		if (
			isset( $config['class'] ) &&
			$config['class'] === 'ElasticSearchTTMServer' &&
			isset( $config['public'] ) &&
			$config['public'] === true
		) {
			$config['type'] = 'remote-ttmserver';
			$config['service'] = $name;
			$config['url'] = wfExpandUrl( wfScript( 'api' ), PROTO_CANONICAL );
		}

		if ( isset( $handlers[$config['type']] ) ) {
			$class = $handlers[$config['type']];

			return new $class( $name, $config );
		}

		return null;
	}

	/**
	 * Gets the name of this service, for example to display it for the user.
	 *
	 * @return string Plain text name for this service.
	 * @since 2014.02
	 */
	public function getName() {
		return $this->service;
	}

	/**
	 * Get queries for this service. Queries from multiple services can be
	 * collected and run asynchronously with QueryAggregator.
	 *
	 * @param string $text Source text
	 * @param string $from Source language
	 * @param string $to Target language
	 * @return TranslationQuery[]
	 * @since 2015.12
	 */
	public function getQueries( $text, $from, $to ) {
		try {
			return array( $this->getQuery( $text, $from, $to ) );
		} catch ( Exception $e ) {
			$this->reportTranslationServiceFailure( $e->getMessage() );
			return array();
		}
	}

	/**
	 * Get the web service specific response returned by QueryAggregator.
	 *
	 * @param TranslationQueryResponse $response
	 * @return mixed
	 * @since 2015.12
	 */
	public function getResultData( TranslationQueryResponse $response ) {
		if ( $response->getStatusCode() !== 200 ) {
			$this->reportTranslationServiceFailure( $response->getStatusMessage() );
			return array();
		}

		try {
			return $this->parseResponse( $response );
		} catch ( Exception $e ) {
			$this->reportTranslationServiceFailure( $e->getMessage() );
			return array();
		}
	}

	/**
	 * Returns the type of this web service.
	 * @see TranslationAid::getTypes
	 * @return string
	 */
	abstract public function getType();

	/* Service api */

	/**
	 * Map a MediaWiki (almost standard) language code to the code used by the
	 * translation service.
	 *
	 * @param string $code MediaWiki language code.
	 * @return string Translation service language code.
	 */
	abstract protected function mapCode( $code );

	/**
	 * Get the list of supported language pairs for the web service. The codes
	 * should be the ones used by the service. Caching is handled by the public
	 * getSupportedLanguagePairs.
	 *
	 * @return array $list[source language][target language] = true
	 */
	abstract protected function doPairs();

	/**
	 * Get the query. See getQueries for the public method.
	 *
	 * @param string $text Text to translate.
	 * @param string $from Language code of the text, as used by the service.
	 * @param string $to Language code of the translation, as used by the service.
	 * @return TranslationQuery
	 * @since 2015.02
	 */
	abstract protected function getQuery( $text, $from, $to );

	/**
	 * Get the response. See getResultData for the public method.
	 *
	 * @param TranslationQueryResponse $response
	 * @return mixed
	 * @since 2015.02
	 */
	abstract protected function parseResponse( TranslationQueryResponse $response );

	/* Default implementation */

	/**
	 * @var string Name of this webservice.
	 */
	protected $service;

	/**
	 * @var array
	 */
	protected $config;

	/**
	 * TranslationWebService constructor.
	 * @param string $service Name of the webservice
	 * @param array $config
	 */
	protected function __construct( $service, $config ) {
		$this->service = $service;
		$this->config = $config;
	}

	/**
	 * Test whether given language pair is supported by the service.
	 *
	 * @param string $from Source language
	 * @param string $to Target language
	 * @return bool
	 * @since 2015.12
	 */
	public function isSupportedLanguagePair( $from, $to ) {
		$pairs = $this->getSupportedLanguagePairs();
		return isset( $pairs[$from][$to] );
	}

	/**
	 * @see doPairs
	 */
	protected function getSupportedLanguagePairs() {
		$key = wfMemcKey( 'translate-tmsug-pairs-' . $this->service );
		$pairs = wfGetCache( CACHE_ANYTHING )->get( $key );
		if ( !is_array( $pairs ) ) {
			try {
				$pairs = $this->doPairs();
			} catch ( Exception $e ) {
				$this->reportTranslationServiceFailure( $e->getMessage() );
				return array();
			}
			// Cache the result for a day
			wfGetCache( CACHE_ANYTHING )->set( $key, $pairs, 60 * 60 * 24 );
		}

		return $pairs;
	}

	/**
	 * Some mangling that tries to keep some parts of the message unmangled
	 * by the translation service. Most of them support either class=notranslate
	 * or translate=no.
	 * @param string $text
	 * @return string
	 */
	protected function wrapUntranslatable( $text ) {
		$text = str_replace( "\n", '!N!', $text );
		$pattern = '~%[^% ]+%|\$\d|{VAR:[^}]+}|{?{(PLURAL|GRAMMAR|GENDER):[^|]+\||%(\d\$)?[sd]~';
		$wrap = '<span class="notranslate" translate="no">\0</span>';
		return preg_replace( $pattern, $wrap, $text );
	}

	/**
	 * Undo the hopyfully untouched mangling done by wrapUntranslatable.
	 * @param string $text
	 * @return string
	 */
	protected function unwrapUntranslatable( $text ) {
		$text = str_replace( '!N!', "\n", $text );
		$pattern = '~<span class="notranslate" translate="no">(.*?)</span>~';
		return preg_replace( $pattern, '\1', $text );
	}

	/* Failure handling and suspending */

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

	/**
	 * Checks whether the service has exceeded failure count
	 * @return bool
	 */
	public function checkTranslationServiceFailure() {
		$service = $this->service;
		$key = wfMemcKey( "translate-service-$service" );
		$value = wfGetCache( CACHE_ANYTHING )->get( $key );
		if ( !is_string( $value ) ) {
			return false;
		}
		list( $count, $failed ) = explode( '|', $value, 2 );

		if ( $failed + ( 2 * $this->serviceFailurePeriod ) < wfTimestamp() ) {
			if ( $count >= $this->serviceFailureCount ) {
				wfDebugLog( 'translationservices', "Translation service $service (was) restored" );
			}
			wfGetCache( CACHE_ANYTHING )->delete( $key );

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

	/**
	 * Increases the failure count for this service
	 * @param string $msg
	 */
	protected function reportTranslationServiceFailure( $msg ) {
		$service = $this->service;
		wfDebugLog( 'translationservices', "Translation service $service problem: $msg" );

		$key = wfMemcKey( "translate-service-$service" );
		$value = wfGetCache( CACHE_ANYTHING )->get( $key );
		if ( !is_string( $value ) ) {
			$count = 0;
		} else {
			list( $count, ) = explode( '|', $value, 2 );
		}

		$count++;
		$failed = wfTimestamp();
		wfGetCache( CACHE_ANYTHING )->set(
			$key,
			"$count|$failed",
			$this->serviceFailurePeriod * 5
		);

		if ( $count === $this->serviceFailureCount ) {
			wfDebugLog( 'translationservices', "Translation service $service suspended" );
		} elseif ( $count > $this->serviceFailureCount ) {
			wfDebugLog( 'translationservices', "Translation service $service still suspended" );
		}
	}
}
