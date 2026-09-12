<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use MediaWiki\Extension\Translate\MessageGroups\MessageGroup;

/**
 * Interface for MessageGroupFactories that use caching
 * @since 2019.05
 * @author Abijeet Patro
 * @author Siebrand Mazeland
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

/** @deprecated class alias since 2026.09 */
class_alias( CachedMessageGroupLoader::class, 'CachedMessageGroupLoader' );
