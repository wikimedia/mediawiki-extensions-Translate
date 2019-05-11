<?php
class MockCacheDbMessageGroupLoader extends MessageGroupLoader
	implements DbMessageGroupLoader, CachedMessageGroupLoader {

	public function getGroups() {
		return [];
	}

	public function setCache( \MessageGroupWANCache $cache ) {
	}

	public function setDatabase( \Wikimedia\Rdbms\IDatabase $db ) {
	}

	public function recache() {
	}

	public function clearCache() {
	}

	public function isExpired( $cacheData ) {
	}

	public static function registerLoader( array &$groupLoader ) {
		$groupLoader[] = new self();
	}
}
