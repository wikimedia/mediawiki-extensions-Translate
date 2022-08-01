<?php
/**
 * Code related to revtag database table
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */
/**
 * Abstraction for revtag table to handle new and old schemas during migration.
 * TODO: Remove class after I3315009bb86196fd5f8652f8fcaef1ebab0a89d8 is merged & deployed
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
}
