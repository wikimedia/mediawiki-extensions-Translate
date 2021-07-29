<?php
/**
 * This file contains an abstract class to be extended by group stores / loaders.
 *
 * @file
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 */

 /**
  * An abstract class to be implemented by group loaders / stores
  * @since 2019.05
  */
abstract class MessageGroupLoader {
	/**
	 * Fetches and returns an array of MessageGroups.
	 * @return MessageGroup[] Array of message groups with group id as the key
	 * @note Do not return an indexed based array as that would cause MessageGroups to
	 * be ovewritten.
	 */
	abstract public function getGroups();

	/**
	 * Determines if dependencies have expired. Called if data in cache is stored
	 * as a dependency wrapper.
	 * @param DependencyWrapper $wrapper
	 * @return bool true if expired, false otherwise.
	 */
	public function isExpired( DependencyWrapper $wrapper ) {
		return $wrapper->isExpired();
	}
}
