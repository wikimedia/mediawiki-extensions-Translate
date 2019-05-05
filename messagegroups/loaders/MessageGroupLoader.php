<?php
/**
 * This file contains an interface to be implemented by group stores / loaders.
 *
 * @file
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 */

interface MessageGroupLoader {
	public function getGroups();

	public static function registerLoader( array &$groupLoaderNames );
}
