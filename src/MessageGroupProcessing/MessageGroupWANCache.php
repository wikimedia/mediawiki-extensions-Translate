<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use Closure;
use InvalidArgumentException;
use TypeError;
use WANObjectCache;

/**
 * A wrapper around WANObjectCache providing a simpler interface for
 * MessageGroups to use the cache.
 *
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 */
class MessageGroupWANCache {
	private WANObjectCache $cache;
	private string $cacheKey;
	/**
	 * To be called when the cache is empty or expired to get the data
	 * to repopulate the cache
	 */
	private Closure $regenerator;
	/** @see @https://doc.wikimedia.org/mediawiki-core/master/php/classWANObjectCache.html */
	private int $lockTSE;
	/** @see @https://doc.wikimedia.org/mediawiki-core/master/php/classWANObjectCache.html */
	private array $checkKeys;
	/** @see @https://doc.wikimedia.org/mediawiki-core/master/php/classWANObjectCache.html */
	private ?Closure $touchedCallback;
	/** @see @https://doc.wikimedia.org/mediawiki-core/master/php/classWANObjectCache.html */
	private int $ttl;

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
	 * @param string|false $recache Either "recache" or false
	 * @return mixed
	 */
	public function getValue( $recache = false ) {
		$this->checkConfig();

		return $this->cache->getWithSetCallback(
			$this->cacheKey,
			$this->ttl,
			$this->regenerator,
			[
				'lockTSE' => $this->lockTSE, // avoid stampedes (mutex)
				'checkKeys' => $this->checkKeys,
				'touchedCallback' => function ( $value ) {
					if ( isset( $this->touchedCallback ) && call_user_func( $this->touchedCallback, $value ) ) {
						// treat value as if it just expired (for "lockTSE")
						return time();
					}

					return null;
				},
				// "miss" on recache
				'minAsOf' => $recache ? INF : WANObjectCache::MIN_TIMESTAMP_NONE,
			]
		);
	}

	/**
	 * Sets value in the cache for the message group
	 * @param mixed $cacheData
	 */
	public function setValue( $cacheData ): void {
		$this->checkConfig();
		$this->cache->set( $this->cacheKey, $cacheData, $this->ttl );
	}

	public function touchKey(): void {
		$this->checkConfig();
		$this->cache->touchCheckKey( $this->cacheKey );
	}

	/** Deletes the cached value */
	public function delete(): void {
		$this->checkConfig();
		$this->cache->delete( $this->cacheKey );
	}

	/** Configure the message group. This must be called before making a call to any other method. */
	public function configure( array $config ): void {
		if ( !isset( $config['key'] ) ) {
				throw new InvalidArgumentException( '$config[\'key\'] not set' );
		}
		$cacheKey = $config['key'];

		if ( !isset( $config['regenerator'] ) ) {
				throw new InvalidArgumentException( '$config[\'regenerator\'] not set' );
		}
		$this->regenerator = $this->toClosure( 'regenerator', $config['regenerator'] );

		$cacheVersion = $config['version'] ?? null;
		$this->lockTSE = $config['lockTSE'] ?? 30;
		$this->checkKeys = $config['checkKeys'] ?? [ $cacheKey ];
		$this->touchedCallback = isset( $config['touchedCallback'] )
			? $this->toClosure( 'touchedCallback', $config['touchedCallback'] )
			: null;

		$this->ttl = $config['ttl'] ?? WANObjectCache::TTL_DAY;

		if ( $cacheVersion ) {
			$this->cacheKey = $this->cache->makeKey(
				self::KEY_PREFIX,
				strtolower( $cacheKey ),
				'v' . $cacheVersion
			);
		} else {
			$this->cacheKey = $this->cache->makeKey(
				self::KEY_PREFIX, strtolower( $cacheKey )
			);
		}
	}

	/** Check to see if the instance is configured properly. */
	private function checkConfig(): void {
		// Attempt to cast $this->regenerator of type \Closure to isset
		// @phan-suppress-next-line PhanRedundantCondition
		if ( !isset( $this->cacheKey ) || !isset( $this->regenerator ) ) {
			throw new InvalidArgumentException(
				"Required data not set."
				. " Ensure you have called the configure function before get / setting values."
			);
		}
	}

	/**
	 * @param string $key key of the config
	 * @param mixed $potentialCallable The potential callable to convert.
	 */
	private function toClosure( string $key, $potentialCallable ): Closure {
		try {
			return Closure::fromCallable( $potentialCallable );
		} catch ( TypeError $e ) {
			throw new InvalidArgumentException(
				"\$config['$key'] is not callable: " . $e->getMessage(), 0, $e
			);
		}
	}
}
