<?php
/**
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0+
 */

/**
 * Special insertables for translatable pages.
 * @since 2013.11
 */
class TranslatablePageInsertablesSuggester extends MediaWikiInsertablesSuggester {
	public function getInsertables( $text ) {
		$insertables = parent::getInsertables( $text );

		// Translatable pages allow naming the variables. Basically anything is
		// allowed in a variable name, but here we are stricter to avoid too many
		// false positives.
		$matches = array();
		preg_match_all( '/\$([a-zA-Z0-9-_]+)/', $text, $matches, PREG_SET_ORDER );

		$new = array_map( function ( $match ) {
			// Numerical ones are already handled by parent
			if ( ctype_digit( $match[1] ) ) {
				return null;
			}

			return new Insertable( $match[0], $match[0] );
		}, $matches );

		$new = array_filter( $new );
		$insertables = array_merge( $insertables, $new );

		return $insertables;
	}
}
