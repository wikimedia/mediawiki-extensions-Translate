<?php
/**
 * This file contains an interface to be implemented by group stores / loaders that
 * use the cache.
 *
 * @file
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 */

/**
 * To be implemented by MessageGroupLoaders that use the MessageGroupWANCache
 * @since 2019.05
 */
interface CachedMessageGroupLoader {
	public function recache();

	public function clearCache();
}
