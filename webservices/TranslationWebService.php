<?php
/**
 * Contains code related to webs ervice support.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2010-2013 Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
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
			'ttmserver' => 'RemoteTTMServerWebService',
		);

		if ( !isset( $config['timeout'] ) ) {
			$config['timeout'] = 3;
		}

		if ( isset( $handlers[$config['type']] ) ) {
			$class = $handlers[$config['type']];

			return new $class( $name, $config );
		}

		return null;
	}

	/**
	 * Do the only supported thing for web services: get a suggestion for
	 * translation. Prefers source language as input for suggestions.
	 *
	 * @param array $translations List of all translations listed by language code.
	 * @param string $sourceLanguage Language code as used by MediaWiki.
	 * @param string $targetLanguage Language code as used by MediaWiki.
	 * @return array[] The returned suggestion arrays contain the following keys:
	 *  - value: the suggestion
	 *  - language: the language of the suggestion (=$targetLanguage)
	 *  - source_text: the text used as input for the web service
	 *  - source_language: the language of the text used as input
	 *  - service: name of the web service
	 */
	public function getSuggestions( $translations, $sourceLanguage, $targetLanguage ) {
		if ( $this->checkTranslationServiceFailure() ) {
			return array();
		}

		$from = $this->mapCode( $sourceLanguage );
		$to = $this->mapCode( $targetLanguage );

		try {
			$results = array();

			// Try to use the source language when possible.
			$supported = $this->getSupportedLanguagePairs();
			if ( isset( $supported[$from][$to] ) && isset( $translations[$from] ) ) {
				// Delete all the other languages.
				// Use the unmapped code to avoid double mapping
				$translations = array( $sourceLanguage => $translations[$from] );
			}

			// Loop of the the translations we have to see which can be used as source
			// @todo: Support setting priority of languages like Yandex used to have
			foreach ( $translations as $language => $text ) {
				$from = $this->mapCode( $language );

				if ( isset( $supported[$from][$to] ) ) {
					$sug = $this->doRequest( $text, $from, $to );
					if ( strval( $sug === '' ) ) {
						continue;
					}

					$results[] = array(
						'target' => $sug,
						'service' => $this->service,
						'source_language' => $language,
						'source' => $text,
					);
				}

				if ( count( $results ) >= 3 ) {
					break;
				}
			}

			return $results;
		} catch ( Exception $e ) {
			$this->reportTranslationServiceFailure( $e );

			return array();
		}
	}

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
	 * Get the suggestion. See getSuggestions for the public method.
	 *
	 * @param string $text Text to translate.
	 * @param string $from Language code of the text, as used by the service.
	 * @param string $to Language code of the translation, as used by the service.
	 * @return string Translation suggestion.
	 */
	abstract protected function doRequest( $text, $from, $to );

	/* Default implementation */

	/**
	 * @var string Name of this webservice.
	 */
	protected $service;

	/**
	 * @var array
	 */
	protected $config;

	protected function __construct( $service, $config ) {
		$this->service = $service;
		$this->config = $config;
	}

	/**
	 * @see doPairs
	 */
	protected function getSupportedLanguagePairs() {
		$key = wfMemckey( 'translate-tmsug-pairs-' . $this->service );
		$pairs = wfGetCache( CACHE_ANYTHING )->get( $key );
		if ( !is_array( $pairs ) ) {
			$pairs = $this->doPairs();
			// Cache the result for a day
			wfGetCache( CACHE_ANYTHING )->set( $key, $pairs, 60 * 60 * 24 );
		}

		return $pairs;
	}

	/**
	 * Some mangling that tries to keep some parts of the message unmangled
	 * by the translation service. Most of them support either class=notranslate
	 * or translate=no.
	 */
	protected function wrapUntranslatable( $text ) {
		$pattern = '~%[^% ]+%|\$\d|{VAR:[^}]+}|{?{(PLURAL|GRAMMAR|GENDER):[^|]+\||%(\d\$)?[sd]~';
		$text = str_replace( "\n", "!N!", $text );
		$wrap = '<span class="notranslate" translate="no">\0</span>';
		$text = preg_replace( $pattern, $wrap, $text );

		return $text;
	}

	/**
	 * Undo the hopyfully untouched mangling done by wrapUntranslatable.
	 */
	protected function unwrapUntranslatable( $text ) {
		$pattern = '~<span class="notranslate" translate="no">(.*?)</span>~';
		$text = str_replace( '!N!', "\n", $text );
		$text = preg_replace( $pattern, '\1', $text );

		return $text;
	}

	/* Failure handling and suspending */

	/**
	 * How many failures during failure period need to happen to consider
	 * the service being temporarily off-line.
	 */
	protected $serviceFailureCount = 5;
	/**
	 * How long after the last detected failure we clear the status and
	 * try again.
	 */
	protected $serviceFailurePeriod = 900;

	/**
	 * Checks whether the service has exceeded failure count
	 * @return bool
	 */
	public function checkTranslationServiceFailure() {
		$service = $this->service;
		$key = wfMemckey( "translate-service-$service" );
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
	 */
	protected function reportTranslationServiceFailure( Exception $e ) {
		$service = $this->service;
		wfDebugLog(
			'translationservices',
			"Translation service $service problem: " . $e->getMessage()
		);

		$key = wfMemckey( "translate-service-$service" );
		$value = wfGetCache( CACHE_ANYTHING )->get( $key );
		if ( !is_string( $value ) ) {
			$count = 0;
		} else {
			list( $count, ) = explode( '|', $value, 2 );
		}

		$count += 1;
		$failed = wfTimestamp();
		wfGetCache( CACHE_ANYTHING )->set(
			$key,
			"$count|$failed",
			$this->serviceFailurePeriod * 5
		);

		if ( $count == $this->serviceFailureCount ) {
			wfDebugLog( 'translationservices', "Translation service $service suspended" );
		} elseif ( $count > $this->serviceFailureCount ) {
			wfDebugLog( 'translationservices', "Translation service $service still suspended" );
		}
	}
}
