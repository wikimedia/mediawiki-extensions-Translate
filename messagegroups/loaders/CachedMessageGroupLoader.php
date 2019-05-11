<?php
/**
 * This file contains an interface to be implemented by group stores / loaders that
 * use the cache.
 *
 * @file
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 */

interface CachedMessageGroupLoader {
	public function setCache( MessageGroupWANCache $cache );

	public function recache();

	public function clearCache();
}
