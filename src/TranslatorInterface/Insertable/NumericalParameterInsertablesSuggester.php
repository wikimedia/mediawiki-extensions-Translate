<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TranslatorInterface\Insertable;

/**
 * Insertables suggester for numerical parameters such as $1, $2, $3
 * @author Geoffrey Mon
 * @license GPL-2.0-or-later
 * @since 2020.12
 */
class NumericalParameterInsertablesSuggester implements InsertablesSuggester {
	public function getInsertables( string $text ): array {
		$insertables = [];

		// $1, $2, $3 etc.
		$matches = [];
		preg_match_all(
			'/\$\d+/',
			$text,
			$matches,
			PREG_SET_ORDER
		);
		$new = array_map( static function ( $match ) {
			return new Insertable( $match[0], $match[0] );
		}, $matches );
		$insertables = array_merge( $insertables, $new );

		return $insertables;
	}
}
