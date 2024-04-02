<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use CachedMessageGroupLoader;
use DependencyWrapper;
use MessageGroup;
use MessageGroupLoader;
use WANObjectCache;
use Wikimedia\LightweightObjectStore\ExpirationAwareness;

/**
 * Loads and manages message group factory loaders
 * @since 2024.05
 * @license GPL-2.0-or-later
 * @author Niklas Laxström
 */
class CachedMessageGroupFactoryLoader extends MessageGroupLoader implements CachedMessageGroupLoader {
	private WANObjectCache $cache;
	private string $cacheKey;
	private CachedMessageGroupFactory $factory;
	private const CACHE_TTL = ExpirationAwareness::TTL_DAY;

	public function __construct( WANObjectCache $cache, CachedMessageGroupFactory $factory ) {
		$this->cache = $cache;
		$this->cacheKey = $cache->makeKey( 'translate-mg', $factory->getCacheKey() );
		$this->factory = $factory;
	}

	/** @return MessageGroup[] */
	public function getGroups(): array {
		return $this->factory->createGroups( $this->getCachedValue()->getValue() );
	}

	public function recache(): void {
		$this->getCachedValue( true );
	}

	public function clearCache(): void {
		$this->cache->delete( $this->cacheKey );
	}

	private function getCachedValue( bool $recache = false ): DependencyWrapper {
		if ( $recache ) {
			// Ensure the cache value gets invalidated in other datacenters
			$this->cache->touchCheckKey( $this->cacheKey );
		}
		return $this->cache->getWithSetCallback(
			$this->cacheKey,
			self::CACHE_TTL,
			fn ( $oldValue, &$ttl, array &$setOpts ) => $this->getCacheData( $setOpts ),
			[
				// avoid stampedes (mutex)
				'lockTSE' => 30,
				'touchedCallback' => static fn ( DependencyWrapper $value ) => $value->isExpired() ? time() : null,
				// "miss" on recache
				'minAsOf' => $recache ? INF : WANObjectCache::MIN_TIMESTAMP_NONE,
				'version' => $this->factory->getCacheVersion(),
				// use pcTTL causes stale data to be used even if guarded with !$recache
			]
		);
	}

	private function getCacheData( array &$setOpts ): DependencyWrapper {
		$wrapper = new DependencyWrapper(
			$this->factory->getData( $setOpts ),
			$this->factory->getDependencies()
		);
		$wrapper->initialiseDeps();
		return $wrapper;
	}
}
