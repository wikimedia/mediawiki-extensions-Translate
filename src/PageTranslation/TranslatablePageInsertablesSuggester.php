<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

use MediaWiki\Extension\Translate\TranslatorInterface\Insertable\Insertable;
use MediaWiki\Extension\Translate\TranslatorInterface\Insertable\MediaWikiInsertablesSuggester;

/**
 * Insertables for translation variables in translatable pages.
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @since 2013.11
 */
class TranslatablePageInsertablesSuggester extends MediaWikiInsertablesSuggester {
	/**
	 * Translatable pages allow naming the variables. Almost anything is
	 * allowed in a variable name, but here we are stricter to avoid too many
	 * incorrect matches when variable name is followed by non-space characters.
	 * @internal For use in this namespace only
	 */
	public const NAME_PATTERN = '\$[\pL\pN_$-]+';

	public function getInsertables( string $text ): array {
		$insertables = parent::getInsertables( $text );

		$matches = [];
		$pattern = '/' . self::NAME_PATTERN . '/';
		preg_match_all( $pattern, $text, $matches, PREG_SET_ORDER );

		$new = array_map( static function ( $match ) {
			// Numerical ones are already handled by parent
			if ( ctype_digit( substr( $match[0], 1 ) ) ) {
				return null;
			}

			return new Insertable( $match[0], $match[0] );
		}, $matches );

		$new = array_filter( $new );
		return array_merge( $insertables, $new );
	}
}
