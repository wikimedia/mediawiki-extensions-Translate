<?php
/**
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */

/**
 * InsertablesSuggester implementation for the MediaWiki message group.
 * @since 2013.09
 */
class MediaWikiInsertablesSuggester implements InsertablesSuggester {
	public function getInsertables( $text ) {
		$insertables = [];

		$matches = [];
		preg_match_all( '/\$(1[a-z]+|[0-9]+)/', $text, $matches, PREG_SET_ORDER );
		$new = array_map( function ( $match ) {
			return new Insertable( $match[0], $match[0] );
		}, $matches );
		$insertables = array_merge( $insertables, $new );

		$matches = [];
		preg_match_all(
			'/({{((?:PLURAL|GENDER|GRAMMAR):[^|]*)\|).*?(}})/i',
			$text,
			$matches,
			PREG_SET_ORDER
		);
		$new = array_map( function ( $match ) {
			return new Insertable( $match[2], $match[1], $match[3] );
		}, $matches );
		$insertables = array_merge( $insertables, $new );

		$matches = [];
		preg_match_all( '/<\/?[a-z]+>/', $text, $matches, PREG_SET_ORDER );
		$new = array_map( function ( $match ) {
			return new Insertable( $match[0], $match[0] );
		}, $matches );
		$insertables = array_merge( $insertables, $new );

		return $insertables;
	}
}
