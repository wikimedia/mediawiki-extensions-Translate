<?php
/**
 * Insertables suggester for numerical parameters such as $1, $2, $3
 *
 * @file
 * @author Geoffrey Mon
 * @license GPL-2.0-or-later
 */

class NumericalParameterInsertablesSuggester implements InsertablesSuggester {
	public function getInsertables( $text ) {
		$insertables = [];

		// $1, $2, $3 etc.
		$matches = [];
		preg_match_all(
			'/\$\d+/',
			$text,
			$matches,
			PREG_SET_ORDER
		);
		$new = array_map( function ( $match ) {
			return new Insertable( $match[0], $match[0] );
		}, $matches );
		$insertables = array_merge( $insertables, $new );

		return $insertables;
	}
}
