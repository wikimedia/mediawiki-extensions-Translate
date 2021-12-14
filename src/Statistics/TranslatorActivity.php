<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Statistics;

use BagOStuff;
use InvalidArgumentException;
use JobQueueGroup;
use PoolCounterWorkViaCallback;
use TranslateUtils;
use Wikimedia\Timestamp\ConvertibleTimestamp;

/**
 * Handles caching of translator activity.
 *
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @since 2020.04
 */
class TranslatorActivity {
	public const CACHE_TIME = 3 * 24 * 3600;
	// 25 hours so that it's easy to configure the maintenance script run daily
	public const CACHE_STALE = 25 * 3600;
	private $cache;
	private $query;
	private $jobQueue;

	public function __construct(
		BagOStuff $cache,
		TranslatorActivityQuery $query,
		JobQueueGroup $jobQueue
	) {
		$this->cache = $cache;
		$this->query = $query;
		$this->jobQueue = $jobQueue;
	}

	/**
	 * Get translations activity for a given language.
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
		return $this->cache->makeKey( 'translate-translator-activity-v4', $language );
	}

	private function isStale( array $value ): bool {
		$age = intval( ConvertibleTimestamp::now( TS_UNIX ) ) - $value['asOfTime'];
		return $age >= self::CACHE_STALE;
	}

	private function queueCacheRefresh( string $language ): void {
		$job = UpdateTranslatorActivityJob::newJobForLanguage( $language );
		$this->jobQueue->push( $job );
	}

	private function doQueryAndCache( string $language ) {
		$now = (int)ConvertibleTimestamp::now( TS_UNIX );

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

	/** Update cache for all languages, even if not stale. */
	public function updateAllLanguages(): void {
		$now = (int)ConvertibleTimestamp::now( TS_UNIX );

		$data = $this->query->inAllLanguages();
		// In case there is no activity for a supported languages, cache empty results
		$validLanguages = TranslateUtils::getLanguageNames( null );
		foreach ( $validLanguages as $language ) {
			$data[$language] = $data[$language] ?? [];
		}

		foreach ( $data as $language => $users ) {
			if ( !$this->isValidLanguage( $language ) ) {
				continue;
			}

			$data = [ 'users' => $users, 'asOfTime' => $now ];
			$this->addToCache( $data, $language );
		}
	}

	/**
	 * Update cache for one language, even if not stale.
	 * @throws StatisticsUnavailable If loading statistics is temporarily not possible.
	 */
	public function updateLanguage( string $language ): void {
		if ( !$this->isValidLanguage( $language ) ) {
			throw new InvalidArgumentException( "Invalid language tag '$language'" );
		}

		$queriedValue = $this->doQueryAndCache( $language );
		if ( !$queriedValue ) {
			throw new StatisticsUnavailable( 'Unable to load stats' );
		}
	}

	private function isValidLanguage( string $language ): bool {
		return TranslateUtils::isSupportedLanguageCode( $language );
	}
}
