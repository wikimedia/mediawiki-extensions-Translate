<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use CachedMessageGroupLoader;
use DependencyWrapper;
use MessageGroup;
use MessageGroupLoader;
use Wikimedia\LightweightObjectStore\ExpirationAwareness;
use Wikimedia\ObjectCache\WANObjectCache;
use Wikimedia\Rdbms\IConnectionProvider;

/**
 * Loads and manages message group factory loaders
 * @since 2024.05
 * @license GPL-2.0-or-later
 * @author Niklas Laxström
 */
class CachedMessageGroupFactoryLoader implements CachedMessageGroupLoader, MessageGroupLoader {

	private string $cacheKey;

	private const CACHE_TTL = ExpirationAwareness::TTL_DAY;

	public function __construct(
		private readonly WANObjectCache $cache,
		private readonly IConnectionProvider $dbProvider,
		private readonly CachedMessageGroupFactory $factory,
	) {
		$this->cacheKey = $cache->makeKey( 'translate-mg', $factory->getCacheKey() );
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
			fn () => $this->getCacheData(),
			[
				// avoid stampedes (mutex)
				'lockTSE' => 30,
				'checkKeys' => [ $this->cacheKey ],
				'touchedCallback' => static fn ( DependencyWrapper $value ) => $value->isExpired() ? time() : null,
				'version' => $this->factory->getCacheVersion(),
			]
		);
	}

	private function getCacheData(): DependencyWrapper {
		$dbr = $this->dbProvider->getReplicaDatabase();
		$wrapper = new DependencyWrapper(
			$this->factory->getData( $dbr ),
			$this->factory->getDependencies()
		);
		$wrapper->initialiseDeps();
		return $wrapper;
	}
}
