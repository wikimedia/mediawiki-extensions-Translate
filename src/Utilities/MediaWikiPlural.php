<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Utilities;

/**
 * Helpers for MediaWiki {{PLURAL:}} syntax.
 * @license GPL-2.0-or-later
 * @since 2026.11
 */
class MediaWikiPlural {
	/**
	 * Quick way to check if the text contains MediaWiki plural syntax.
	 *
	 * Matching is case-insensitive like the parser function. Gettext plurals
	 * ({{PLURAL:GETTEXT|...}}) are not MediaWiki plurals and do not count.
	 */
	public static function hasPlural( string $text ): bool {
		return preg_match( '/{{PLURAL:(?!GETTEXT\|)/i', $text ) === 1;
	}
}
