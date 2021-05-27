<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Cache;

/** Defines what method should be provided by a class implementing a persistent cache */
interface PersistentCache {
	/** @return PersistentCacheEntry[] */
	public function get( string ...$keynames ): array;

	public function getWithLock( string $keyname ): ?PersistentCacheEntry;

	public function has( string $keyname ): bool;

	public function hasEntryWithTag( string $tag ): bool;

	public function hasExpiredEntry( string $keyname ): bool;

	public function setExpiry( string $keyname, int $expiryTime ): void;

	/** @return PersistentCacheEntry[] */
	public function getByTag( string $tag ): array;

	public function set( PersistentCacheEntry ...$cacheEntry ): void;

	public function delete( string ...$keyname ): void;

	public function deleteEntriesWithTag( string $tag ): void;

	public function clear(): void;
}
