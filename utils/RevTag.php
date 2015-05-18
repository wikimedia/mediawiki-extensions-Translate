<?php
/**
 * Code related to revtag database table
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0+
 */

/**
 * Abstraction for revtag table to handle new and old schemas during migration.
 */
class RevTag {
	/**
	 * Returns value suitable for rt_type field.
	 * @param string $tag Tag name
	 * @return string
	 */
	public static function getType( $tag ) {
		return $tag;
	}

	/**
	 * Converts rt_type field back to the tag name.
	 * @param $tag int rt_type value
	 * @return string
	 */
	public static function typeToTag( $tag ) {
		return $tag;
	}
}
