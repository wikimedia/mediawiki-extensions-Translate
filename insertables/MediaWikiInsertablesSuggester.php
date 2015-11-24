<?php
/**
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0+
 */

/**
 * Insertable is a string that usually does not need translation and is
 * difficult to type manually.
 * @since 2013.09
 */
class MediaWikiInsertablesSuggester {
	public function getInsertables( $text ) {
		$insertables = array();

		$matches = array();
		preg_match_all( '/\$(1[a-z]+|[0-9]+)/', $text, $matches, PREG_SET_ORDER );
		$new = array_map( function( $match ) {
			return new Insertable( $match[0], $match[0] );
		}, $matches );
		$insertables = array_merge( $insertables, $new );

		$matches = array();
		preg_match_all(
			'/({{((?:PLURAL|GENDER|GRAMMAR):[^|]*)\|).*?(}})/i',
			$text,
			$matches,
			PREG_SET_ORDER
		);
		$new = array_map( function( $match ) {
			return new Insertable( $match[2], $match[1], $match[3] );
		}, $matches );
		$insertables = array_merge( $insertables, $new );

		return $insertables;
	}
}
