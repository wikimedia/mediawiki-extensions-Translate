<?php

class MockCacheMessageGroupLoader implements MessageGroupLoader, CachedMessageGroupLoader {

	public function getGroups(): array {
		return [];
	}

	public function recache(): array {
		return [];
	}

	public function clearCache(): void {
	}

	public static function registerLoader( array &$groupLoader, array $deps ) {
		$groupLoader[] = new self();
	}
}
