<?php
/**
 * This file contains a wrapper around WANObjectCache
 *
 * @file
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 */

/**
 * Wrapper around WANObjectCache providing a simpler interface for
 * MessageGroups to use the cache.
 * @since 2019.05
 */
class MessageGroupWANCache {

	/** @var WANObjectCache */
	protected $cache;
	/**
	 * Cache key
	 *
	 * @var string
	 */
	protected $cacheKey;
	/**
	 * Cache version
	 *
	 * @var int
	 */
	protected $cacheVersion;
	/**
	 * To be called when the cache is empty or expired to get the data
	 * to repopulate the cache
	 * @var Closure
	 */
	protected $regenerator;
	/**
	 * @see @https://doc.wikimedia.org/mediawiki-core/master/php/classWANObjectCache.html
	 * @var int
	 */
	protected $lockTSE;
	/**
	 * @see @https://doc.wikimedia.org/mediawiki-core/master/php/classWANObjectCache.html
	 * @var array
	 */
	protected $checkKeys;
	/**
	 * @see @https://doc.wikimedia.org/mediawiki-core/master/php/classWANObjectCache.html
	 * @var Closure
	 */
	protected $touchedCallback;
	/**
	 * @see @https://doc.wikimedia.org/mediawiki-core/master/php/classWANObjectCache.html
	 * @var int
	 */
	protected $ttl;

	/**
	 * A prefix for all keys saved by this cache
	 * @var string
	 */
	private const KEY_PREFIX = 'translate-mg';

	public function __construct( WANObjectCache $cache ) {
		$this->cache = $cache;
	}

	/**
	 * Fetches value from cache for a message group.
	 *
	 * @param bool $recache
	 * @return mixed
	 */
	public function getValue( $recache = false ) {
		$this->checkConfig();

		$cacheData = $this->cache->getWithSetCallback(
			$this->cacheKey,
			$this->ttl,
			$this->regenerator,
			[
				'lockTSE' => $this->lockTSE, // avoid stampedes (mutex)
				'checkKeys' => $this->checkKeys,
				'touchedCallback' => function ( $value ) {
					if ( $this->touchedCallback && call_user_func( $this->touchedCallback, $value ) ) {
						// treat value as if it just expired (for "lockTSE")
						return time();
					}

					return null;
				},
				// "miss" on recache
				'minAsOf' => $recache ? INF : WANObjectCache::MIN_TIMESTAMP_NONE,
			]
		);

		return $cacheData;
	}

	/**
	 * Sets value in the cache for the message group
	 *
	 * @param mixed $cacheData
	 */
	public function setValue( $cacheData ) {
		$this->checkConfig();
		$this->cache->set( $this->cacheKey, $cacheData, $this->ttl );
	}

	public function touchKey() {
		$this->checkConfig();
		$this->cache->touchCheckKey( $this->cacheKey );
	}

	/**
	 * Deletes the cached value
	 */
	public function delete() {
		$this->checkConfig();
		$this->cache->delete( $this->cacheKey );
	}

	/**
	 * Configure the message group. This must be called before making a call to any other
	 * method.
	 *
	 * @param array $config
	 */
	public function configure( array $config ) {
		$this->cacheKey = $config['key'] ?? null;
		$this->cacheVersion = $config['version'] ?? null;
		$this->regenerator = $config['regenerator'] ?? null;
		$this->lockTSE = $config['lockTSE'] ?? 30;
		$this->checkKeys = $config['checkKeys'] ?? [ $this->cacheKey ];
		$this->touchedCallback = $config['touchedCallback'] ?? null;
		$this->ttl = $config['ttl'] ?? WANObjectCache::TTL_DAY;

		$this->checkConfig();

		if ( $this->cacheVersion ) {
			$this->cacheKey = $this->cache->makeKey( self::KEY_PREFIX,
				strtolower( $this->cacheKey ), 'v' . $this->cacheVersion );
		} else {
			$this->cacheKey = $this->cache->makeKey(
				self::KEY_PREFIX, strtolower( $this->cacheKey )
			);
		}
	}

	/**
	 * Check to see if the instance is configured properly.
	 */
	protected function checkConfig() {
		if ( $this->cacheKey === null ) {
			throw new InvalidArgumentException( "Invalid cache key set. " .
				"Ensure you have called the configure function before get / setting values." );
		}

		if ( !is_callable( $this->regenerator ) ) {
			throw new InvalidArgumentException( "Invalid regenerator set. " .
				"Ensure you have called the configure function before get / setting values." );
		}

		if ( $this->touchedCallback && !is_callable( $this->touchedCallback ) ) {
			throw new InvalidArgumentException( "touchedCallback is not callable. " );
		}
	}
}
