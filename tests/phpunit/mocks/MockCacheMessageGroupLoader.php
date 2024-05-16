<?php

class MockCacheMessageGroupLoader extends MessageGroupLoader
	implements CachedMessageGroupLoader {

	public function getGroups() {
		return [];
	}

	public function recache(): array {
		return [];
	}

	public function clearCache() {
	}

	public static function registerLoader( array &$groupLoader, array $deps ) {
		$groupLoader[] = new self();
	}
}
