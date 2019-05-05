<?php
/**
 * This file contains an interface to be implemented by group stores / loaders that
 * have to use the cache.
 *
 * @file
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 */

interface CachedMessageGroupLoader {
	public function recache();

	public function clearCache();
}
