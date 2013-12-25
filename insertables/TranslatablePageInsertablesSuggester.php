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

		// Translatable pages allow naming the variables. What the regex matches is:
		// * Variable names starting with letter and then containing numbers or 
		//   hyphen (e.g. $a1, $mw-example)
		// * Variable names starting with number and then containing letters or
		//   hyphen (e.g. $1a, $123ex-ample)
		// * Variable names containing only numbers, but not shorter than 6 
		//   characters (e.g. $123456)
		// * Variable names containing only letters (e.g. $example)
		$matches = array();
		preg_match_all( '/\$(([a-z]+[0-9-]+[a-z0-9-]*)|([0-9]+[a-z-]+[a-z0-9-]*)'
		                . '|([0-9]{6,})|([a-z]+))/', $text, $matches, PREG_SET_ORDER );
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
