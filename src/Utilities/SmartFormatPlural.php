<?php
declare( strict_types = 1 );

/**
 * @file
 * @license GPL-2.0-or-later
 */

namespace MediaWiki\Extension\Translate\Utilities;

use function strlen;

/**
 * Implements partial support for SmartFormat plural syntax parsing.
 * @see https://github.com/axuno/SmartFormat/wiki/Pluralization
 * @since 2019.11
 */
class SmartFormatPlural {
	/**
	 * Example input:
	 *   {0} {0:message|messages} older than {1} {1:week|weeks} {0:has|have} been deleted.
	 * Example output:
	 *   [
	 *   	'0' => [
	 *   		[
	 *   			'forms' => [ 'message', 'messages' ],
	 *   			'original' => '{0:message|messages}',
	 *   		],
	 *   		[
	 *   			'forms' => [ 'has', 'have' ],
	 *   			'original' => '{0:has|have}',
	 *   		],
	 *   	],
	 *   	'1' => [
	 *   		[
	 *   			'forms' => [ 'week', 'weeks' ],
	 *   			'original' => '{1:week|weeks}',
	 *   		],
	 *   	],
	 *   ]
	 *
	 * @param string $text
	 * @return array<string,array{forms: string[], original: string}[]>
	 */
	public static function getPluralInstances( string $text ): array {
		// ldns = Large Deeply-Nested Structure
		$ldns = [];

		// Named variables seem to be supported by the spec, but we limit ourselves
		// only to numbers. Example syntax {0:message|messages}
		// Allow nested balanced braces inside forms (e.g. {0:one|{0} many})
		// and escaped characters (e.g. \{ or \}).
		$regex = '/\{(\d+):(?<content>(?:[^{}\\\\]++|\\\\.|\{(?&content)\})*)\}/s';
		$matches = [];
		$matched = preg_match_all( $regex, $text, $matches, PREG_SET_ORDER );
		if ( $matched === false ) {
			return [];
		}

		foreach ( $matches as $instance ) {
			$original = $instance[0];
			$variable = $instance[1];
			$forms = self::splitForms( $instance['content'] );
			$ldns[$variable] ??= [];
			$ldns[$variable][] = [
				'forms' => $forms,
				'original' => $original,
			];
		}

		return $ldns;
	}

	/**
	 * Splits plural forms by pipe '|' at the top level, ignoring pipes inside nested braces.
	 *
	 * @return string[]
	 */
	private static function splitForms( string $content ): array {
		$forms = [];
		$depth = 0;
		$current = '';
		$len = strlen( $content );

		for ( $i = 0; $i < $len; $i++ ) {
			$char = $content[$i];
			if ( $char === '\\' && $i + 1 < $len ) {
				$current .= $char . $content[++$i];
				continue;
			}

			if ( $char === '{' ) {
				$depth++;
				$current .= $char;
			} elseif ( $char === '}' ) {
				if ( $depth > 0 ) {
					$depth--;
				}
				$current .= $char;
			} elseif ( $char === '|' && $depth === 0 ) {
				$forms[] = $current;
				$current = '';
			} else {
				$current .= $char;
			}
		}

		$forms[] = $current;
		return $forms;
	}
}
