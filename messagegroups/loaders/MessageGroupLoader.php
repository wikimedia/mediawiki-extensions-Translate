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

	abstract public static function registerLoader( array &$groupLoader, array $deps );

	/**
	 * Safely merges first array to second array, throwing warning on duplicates and removing
	 * duplicates from the first array.
	 * @param array $additions Things to append
	 * @param array &$to Where to append
	 */
	protected static function appendAutoloader( array $additions, array &$to ) {
		foreach ( $additions as $class => $file ) {
			if ( isset( $to[$class] ) && $to[$class] !== $file ) {
				$msg = "Autoload conflict for $class: {$to[$class]} !== $file";
				trigger_error( $msg, E_USER_WARNING );
				continue;
			}

			$to[$class] = $file;
		}
	}

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
