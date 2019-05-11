<?php
class MockCacheDatabaseMessageGroupLoader extends MessageGroupLoader
	implements DatabaseMessageGroupLoader, CachedMessageGroupLoader {

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

	public static function registerLoader( array &$groupLoader ) {
		$groupLoader[] = new self();
	}
}
