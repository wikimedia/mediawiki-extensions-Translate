<?php
declare( strict_types=1 );

namespace MediaWiki\Extension\Translate\FileFormatSupport;

/**
 * Converts internal MediaWiki language codes to Android resource qualifiers.
 *
 * Android supports two syntaxes:
 *  - Legacy `xx-rYY`: two-letter ISO 639-1 base with an optional two-letter
 *    region. Used only when nothing richer is required.
 *  - BCP 47 `b+xx+YY+...`: required for three-letter base codes, script
 *    subtags and variants, i.e. anything the legacy form cannot express.
 *
 * Any four-letter subtag is assumed to be an ISO 15924 script and is
 * title-cased (e.g. `cyrl` -> `Cyrl`). The one known exception is `tara` in
 * `roa-tara`, which is a variant, not a script; that code must be listed as an
 * explicit codeMap override in any group that uses this algorithm.
 *
 * @author Siebrand Mazeland
 * @license GPL-2.0-or-later
 * @since 2026.09
 */
class AndroidCodeMapper {

	/**
	 * Convert an internal MediaWiki language code to an Android resource
	 * qualifier, following Android's rules for locale-qualified directories.
	 *
	 * Codes that require a policy decision (e.g. `zh-hant` -> `zh-rTW`) are
	 * left to the explicit codeMap and return null here.
	 *
	 * @param string $code Internal MediaWiki language code (lower-case, hyphenated).
	 * @return string|null The Android qualifier, or null if the code needs no change.
	 */
	public function map( string $code ): ?string {
		$parts = explode( '-', $code );
		$base = array_shift( $parts );

		// A bare base code needs no transformation.
		if ( $parts === [] ) {
			return null;
		}

		$script = null;
		$region = null;
		$variants = [];
		foreach ( $parts as $part ) {
			if ( preg_match( '/^[a-z]{4}$/', $part ) ) {
				// Four-letter subtag: assumed to be an ISO 15924 script, e.g. "cyrl" -> "Cyrl".
				$script = ucfirst( $part );
			} elseif ( preg_match( '/^([a-z]{2}|[0-9]{3})$/', $part ) ) {
				// Region subtag, e.g. "br" -> "BR", "419" -> "419".
				$region = strtoupper( $part );
			} elseif ( preg_match( '/^[a-z0-9]{2,8}$/', $part ) ) {
				// Any remaining subtag is kept verbatim as a lower-case variant,
				// e.g. "zam" in "cbk-zam".
				$variants[] = $part;
			} else {
				// Unrecognised subtag shape: leave to explicit codeMap.
				return null;
			}
		}

		// Legacy form: two-letter base with only a region, nothing else.
		if ( strlen( $base ) === 2 && $script === null && $variants === [] && $region !== null ) {
			return "$base-r$region";
		}

		$segments = [ $base ];
		if ( $script !== null ) {
			$segments[] = $script;
		}
		if ( $region !== null ) {
			$segments[] = $region;
		}
		foreach ( $variants as $variant ) {
			$segments[] = $variant;
		}

		// A lone base needs no change; anything richer uses BCP 47 syntax.
		return count( $segments ) === 1 ? null : 'b+' . implode( '+', $segments );
	}
}
