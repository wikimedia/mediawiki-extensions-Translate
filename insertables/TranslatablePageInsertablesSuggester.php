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
		// * Variable names containing only letters (e.g. $example)
		// Variable names containing only numbers are already handled by parent.
		$matches = array();
		preg_match_all(
			'/\$(([a-z]+[0-9-]+[a-z0-9-]*)|([0-9]+[a-z-]+[a-z0-9-]*)|([a-z]+))/',
			$text,
			$matches,
			PREG_SET_ORDER
		);
		$new = array_map( function ( $match ) {
			return new Insertable( $match[0], $match[0] );
		}, $matches );

		$new = array_filter( $new );
		$insertables = array_merge( $insertables, $new );

		return $insertables;
	}
}
