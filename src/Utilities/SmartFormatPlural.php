<?php
/**
 * @file
 * @license GPL-2.0-or-later
 */

namespace MediaWiki\Extension\Translate\Utilities;

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
	 * @return array
	 */
	public static function getPluralInstances( string $text ): array {
		// ldns = Large Deeply-Nested Structure
		$ldns = [];

		// Named variables seem to be supported by the spec, but we limit ourselves
		// only to numbers. Example syntax {0:message|messages}
		$regex = '/\{(\d+):([^}]+)\}/Us';
		$matches = [];
		preg_match_all( $regex, $text, $matches, PREG_SET_ORDER );

		foreach ( $matches as $instance ) {
			$original = $instance[ 0 ];
			$variable = $instance[ 1 ];
			$forms = explode( '|', $instance[ 2 ] );
			$ldns[ $variable ] = $ldns[ $variable ] ?? [];
			$ldns[ $variable ][] = [
				'forms' => $forms,
				'original' => $original,
			];
		}

		return $ldns;
	}
}
