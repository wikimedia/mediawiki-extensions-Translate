<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use CachedMessageGroupLoader;
use DependencyWrapper;
use MessageGroup;
use MessageGroupLoader;
use Wikimedia\LightweightObjectStore\ExpirationAwareness;
use Wikimedia\ObjectCache\WANObjectCache;
use Wikimedia\Rdbms\Database;
use Wikimedia\Rdbms\IConnectionProvider;

/**
 * Loads and manages message group factory loaders
 * @since 2024.05
 * @license GPL-2.0-or-later
 * @author Niklas Laxström
 */
class CachedMessageGroupFactoryLoader implements CachedMessageGroupLoader, MessageGroupLoader {
	private WANObjectCache $cache;
	private IConnectionProvider $dbProvider;
	private string $cacheKey;
	private CachedMessageGroupFactory $factory;
	private const CACHE_TTL = ExpirationAwareness::TTL_DAY;

	public function __construct(
		WANObjectCache $cache,
		IConnectionProvider $dbProvider,
		CachedMessageGroupFactory $factory
	) {
		$this->cache = $cache;
		$this->cacheKey = $cache->makeKey( 'translate-mg', $factory->getCacheKey() );
		$this->factory = $factory;
		$this->dbProvider = $dbProvider;
	}

	/** @return MessageGroup[] */
	public function getGroups(): array {
		return $this->factory->createGroups( $this->getCachedValue()->getValue() );
	}

	/** @return MessageGroup[] */
	public function recache(): array {
		$this->cache->touchCheckKey( $this->cacheKey );
		return $this->factory->createGroups(
			$this->factory->getData( $this->dbProvider->getPrimaryDatabase() )
		);
	}

	public function clearCache(): void {
		$this->cache->delete( $this->cacheKey );
	}

	private function getCachedValue(): DependencyWrapper {
		return $this->cache->getWithSetCallback(
			$this->cacheKey,
			self::CACHE_TTL,
			fn ( $oldValue, &$ttl, array &$setOpts ) => $this->getCacheData( $setOpts ),
			[
				// avoid stampedes (mutex)
				'lockTSE' => 30,
				'checkKeys' => [ $this->cacheKey ],
				'touchedCallback' => static fn ( DependencyWrapper $value ) => $value->isExpired() ? time() : null,
				'version' => $this->factory->getCacheVersion(),
			]
		);
	}

	private function getCacheData( array &$setOpts ): DependencyWrapper {
		$dbr = $this->dbProvider->getReplicaDatabase();

		// Some factories may not use the database, in which case this is superflous.
		// Having it here for simplicity.
		$setOpts += Database::getCacheSetOptions( $dbr );

		$wrapper = new DependencyWrapper(
			$this->factory->getData( $dbr ),
			$this->factory->getDependencies()
		);
		$wrapper->initialiseDeps();
		return $wrapper;
	}
}
