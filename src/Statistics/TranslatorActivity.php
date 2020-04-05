<?php
/**
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */

namespace MediaWiki\Extensions\Translate\Statistics;

use BagOStuff;
use InvalidArgumentException;
use JobQueueGroup;
use Language;
use PoolCounterWorkViaCallback;
use Wikimedia\Timestamp\ConvertibleTimestamp;

/**
 * Handles caching of translator activity.
 *
 * @since 2020.04
 */
class TranslatorActivity {
	public const CACHE_TIME = 3 * 24 * 3600;
	// 25 hours so that it's easy to configure the maintenance script run daily
	public const CACHE_STALE = 25 * 3600;
	private $cache;
	private $query;
	private $jobQueue;
	private $languageValidator;

	public function __construct(
		BagOStuff $cache,
		TranslatorActivityQuery $query,
		JobQueueGroup $jobQueue,
		callable $languageValidator
	) {
		$this->cache = $cache;
		$this->query = $query;
		$this->jobQueue = $jobQueue;
		// FIXME: use LanguageNameUtils once 1.33 is no longer supported
		$this->languageValidator = $languageValidator;
	}

	/**
	 * Get translations activity for a given language.
	 *
	 * @param string $language Language tag.
	 * @return array Array with keys users and asOfTime
	 * @throws StatisticsUnavailable If loading statistics is temporarily not possible.
	 */
	public function inLanguage( string $language ): array {
		if ( !$this->isValidLanguage( $language ) ) {
			throw new InvalidArgumentException( "Invalid language tag '$language'" );
		}

		$cachedValue = $this->getFromCache( $language );

		if ( is_array( $cachedValue ) ) {
			if ( $this->isStale( $cachedValue ) ) {
				$this->queueCacheRefresh( $language );
			}

			return $cachedValue;
		}

		$queriedValue = $this->doQueryAndCache( $language );
		if ( !$queriedValue ) {
			throw new StatisticsUnavailable( "Unable to load stats" );
		}

		return $queriedValue;
	}

	private function getFromCache( string $language ) {
		$cacheKey = $this->getCacheKey( $language );
		return $this->cache->get( $cacheKey );
	}

	private function getCacheKey( string $language ): string {
		return $this->cache->makeKey( 'translate-translator-activity-v1', $language );
	}

	private function isStale( array $value ): bool {
		$age = ConvertibleTimestamp::now( TS_UNIX ) - $value['asOfTime'];
		return $age >= self::CACHE_STALE;
	}

	private function queueCacheRefresh( string $language ): void {
		$job = UpdateTranslatorActivityJob::newJobForLanguage( $language );
		$this->jobQueue->push( $job );
	}

	private function doQueryAndCache( string $language ) {
		$now = ConvertibleTimestamp::now( TS_UNIX );

		$work = new PoolCounterWorkViaCallback(
			'TranslateFetchTranslators', "TranslateFetchTranslators-$language", [
				'doWork' => function () use ( $language, $now ) {
					$users = $this->query->inLanguage( $language );
					$data = [ 'users' => $users, 'asOfTime' => $now ];
					$this->addToCache( $data, $language );
					return $data;
				},
				'doCachedWork' => function () use ( $language ) {
					$data = $this->getFromCache( $language );
					// Use new cache value from other thread
					return is_array( $data ) ? $data : false;
				},
			]
		);

		return $work->execute();
	}

	private function addToCache( array $value, string $language ): void {
		$cacheKey = $this->getCacheKey( $language );
		$this->cache->set( $cacheKey, $value, self::CACHE_TIME );
	}

	/**
	 * Update cache for all languages, even if not stale.
	 */
	public function updateAllLanguages(): void {
		$now = ConvertibleTimestamp::now( TS_UNIX );
		foreach ( $this->query->inAllLanguages() as $language => $users ) {
			if ( !Language::isKnownLanguageTag( $language ) ) {
				continue;
			}

			$data = [ 'users' => $users, 'asOfTime' => $now ];
			$this->addToCache( $data, $language );
		}
	}

	/**
	 * Update cache for one language, even if not stale.
	 *
	 * @param string $language Language tag
	 * @throws StatisticsUnavailable If loading statistics is temporarily not possible.
	 */
	public function updateLanguage( string $language ): void {
		if ( !$this->isValidLanguage( $language ) ) {
			throw new InvalidArgumentException( "Invalid language tag '$language'" );
		}

		$queriedValue = $this->doQueryAndCache( $language );
		if ( !$queriedValue ) {
			throw new StatisticsUnavailable( "Unable to load stats" );
		}
	}

	private function isValidLanguage( string $language ): bool {
		return call_user_func( $this->languageValidator, $language );
	}
}
