<?php
/**
 * This file contains an interface
 *
 * @file
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 */

/**
 * Interface to be implemented by MessageGroup's that want a custom cache
 */
interface CustomCacheMessageGroup {
	public static function getCacheData();

	public static function getCacheKey();

	public static function getCacheVersion();
}
