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
 */
class MessageGroupWANCache {

	/**
	 * @var WANObjectCache
	 */
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
	 * @var \Closure
	 */
	protected $regenerator;

	/**
	 * See @https://doc.wikimedia.org/mediawiki-core/master/php/classWANObjectCache.html
	 *
	 * @var int
	 */
	protected $lockTSE;

	/**
	 * See @https://doc.wikimedia.org/mediawiki-core/master/php/classWANObjectCache.html
	 *
	 * @var array
	 */
	protected $checkKeys;

	/**
	 * See @https://doc.wikimedia.org/mediawiki-core/master/php/classWANObjectCache.html
	 *
	 * @var \Closure
	 */
	protected $touchedCallback;

	/**
	 * See @https://doc.wikimedia.org/mediawiki-core/master/php/classWANObjectCache.html
	 *
	 * @var int
	 */
	protected $ttl;

	/**
	 * A prefix for all keys saved by this cache
	 *
	 * @var string
	 */
	const KEY_PREFIX = 'translate-mg-';

	public function __construct( WANObjectCache $cache ) {
		$this->cache = $cache;
	}

	/**
	 * Fetches value from cache for a message group.
	 *
	 * @param bool $recache
	 * @return void
	 */
	public function getValue( $recache = false ) {
		global $wgVersion;

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
				'minAsOf' => $recache ? INF : $this->cache::MIN_TIMESTAMP_NONE,
			]
		);

		// B/C for "touchedCallback" param not existing
		if ( version_compare( $wgVersion, '1.33', '<' ) &&
			call_user_func( $this->touchedCallback, $cacheData ) ) {
			$cacheData = call_user_func( $this->regenerator );
			$this->set( $cacheData );
		}

		return $cacheData;
	}

	/**
	 * Sets value in the cache for the message group
	 *
	 * @param mixed $cacheData
	 * @return void
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
	 * @return void
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
	 * @return void
	 */
	public function configure( array $config ) {
		$this->cacheKey = $config['key'] ?? null;
		$this->cacheVersion = $config['version'] ?? null;
		$this->regenerator = $config['regenerator'] ?? null;
		$this->lockTSE = $config['lockTSE'] ?? 30;
		$this->checkKeys = $config['checkKeys'] ?? [ $this->cacheKey ];
		$this->touchedCallback = $config['touchedCallback'] ?? null;
		$this->ttl = $config['ttl'] ?? $this->cache::TTL_DAY;

		$this->checkConfig();

		if ( $this->cacheVersion ) {
			$this->cacheKey = $this->cache->makeKey( self::KEY_PREFIX .
				strtolower( $this->cacheKey ), 'v' . $this->cacheVersion );
		} else {
			$this->cacheKey = self::KEY_PREFIX . strtolower( $this->cacheKey );
		}
	}

	/**
	 * Check to see if the class is configured properly.
	 *
	 * @return void
	 */
	protected function checkConfig() {
		if ( !$this->cacheKey || !is_callable( $this->regenerator ) ) {
			throw new \InvalidArgumentException( "Invalid cacheKey and regenerator set. " .
			"Ensure you have called the setConfig function before get / setting values." );
		}

		if ( $this->touchedCallback && !is_callable( $this->touchedCallback ) ) {
			throw new \InvalidArgumentException( "touchedCallback is not callable. " );
		}
	}
}
