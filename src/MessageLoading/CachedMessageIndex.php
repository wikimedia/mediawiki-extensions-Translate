<?php
declare ( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageLoading;

use BagOStuff;
use MessageIndex;
use ObjectCache;

/**
 * Storage on the object cache.
 *
 * This can be faster than DatabaseMessageIndex, but it doesn't
 * provide random access, and the data is not guaranteed to be persistent.
 *
 * This is unlikely to be the best backend for you, so don't use it.
 * @deprecated since MLEB 2024.04
 */
class CachedMessageIndex extends MessageIndex {
	private $key = 'translate-messageindex';
	private BagOStuff $cache;
	private ?array $index = null;

	protected function __construct() {
		parent::__construct();
		wfDeprecated( __CLASS__, 'MLEB 2024.04', 'Translate' );
		$this->cache = ObjectCache::getInstance( CACHE_ANYTHING );
	}

	public function retrieve( bool $readLatest = false ): array {
		if ( $this->index !== null ) {
			return $this->index;
		}

		$key = $this->cache->makeKey( $this->key );
		$data = $this->cache->get( $key );
		if ( is_array( $data ) ) {
			$this->index = $data;
		} else {
			$this->index = $this->rebuild();
		}

		return $this->index;
	}

	protected function store( array $array, array $diff ): void {
		$key = $this->cache->makeKey( $this->key );
		$this->cache->set( $key, $array );

		$this->index = $array;
	}
}
