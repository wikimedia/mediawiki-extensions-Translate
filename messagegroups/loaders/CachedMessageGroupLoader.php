<?php
declare( strict_types = 1 );

/**
 * Interface for MessageGroupFactories that use caching
 * @since 2019.05
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 */
interface CachedMessageGroupLoader {
	/**
	 * Invalidate caches and return uncached data
	 * @return MessageGroup[]
	 */
	public function recache(): array;

	/** Clear values from the cache */
	public function clearCache(): void;
}
