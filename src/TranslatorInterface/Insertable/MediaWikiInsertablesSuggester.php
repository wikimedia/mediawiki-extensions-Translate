<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TranslatorInterface\Insertable;

/**
 * InsertablesSuggester implementation for MediaWiki message translations.
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @since 2020.12
 */
class MediaWikiInsertablesSuggester implements InsertablesSuggester {
	public function getInsertables( string $text ): array {
		$insertables = [];

		$matches = [];
		// MediaWiki apihelp messages often have parameters like $1user, which should
		// be unchanged in translation.
		preg_match_all( '/\$(1[a-z]+|[0-9]+)/', $text, $matches, PREG_SET_ORDER );
		$new = array_map( static function ( $match ) {
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
		$new = array_map( static function ( $match ) {
			return new Insertable( $match[2], $match[1], $match[3] );
		}, $matches );
		$insertables = array_merge( $insertables, $new );

		return array_merge(
			$insertables,
			( new HtmlTagInsertablesSuggester() )->getInsertables( $text )
		);
	}
}
