<?php
/**
 * A trait containing helper methods for validation purpose.
 * @file
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 */

namespace MediaWiki\Extensions\Translate\MessageValidator;

/**
 * A helper trait that can be used by custom validators to reuse code.
 * @since 2019.04
 */
trait ValidationHelper {

	/**
	 * Checks for missing and unknown printf formatting characters in
	 * translations.
	 * @param TMessage[] $messages Iterable list of TMessage objects.
	 * @param string $code Language code
	 * @param array &$validationOutput Array where validation outputs are appended.
	 * @param string $pattern Regular expression for matching variables.
	 */
	protected static function parameterCheck( $messages, $code, &$validationOutput, $pattern ) {
		foreach ( $messages as $message ) {
			$key = $message->key();
			$definition = $message->definition();
			$translation = $message->translation();

			preg_match_all( $pattern, $definition, $defVars );
			preg_match_all( $pattern, $translation, $transVars );

			// Check for missing variables in the translation
			$subcheck = 'missing';
			$params = self::compareArrays( $defVars[0], $transVars[0] );

			if ( count( $params ) ) {
				$validationOutput[$key][] = [
					[ 'variable', $subcheck, $key, $code ],
					'translate-checks-parameters',
					[ 'PARAMS', $params ],
					[ 'COUNT', count( $params ) ],
				];
			}

			// Check for unknown variables in the translation
			$subcheck = 'unknown';
			$params = self::compareArrays( $transVars[0], $defVars[0] );

			if ( count( $params ) ) {
				$validationOutput[$key][] = [
					[ 'variable', $subcheck, $key, $code ],
					'translate-checks-parameters-unknown',
					[ 'PARAMS', $params ],
					[ 'COUNT', count( $params ) ],
				];
			}
		}
	}

	/**
	 * Compares two arrays and return items that don't exist in the latter.
	 * @param array $defs
	 * @param array $trans
	 * @return array Items of $defs that are not in $trans.
	 */
	protected static function compareArrays( array $defs, array $trans ) {
		$missing = [];

		foreach ( $defs as $defVar ) {
			if ( !in_array( $defVar, $trans ) ) {
				$missing[] = $defVar;
			}
		}

		return $missing;
	}

	/**
	 * @param string $source
	 * @param string $str1
	 * @param string $str2
	 * @return bool whether $source has an equal number of occurences of $str1 and $str2
	 */
	protected static function checkStringCountEqual( $source, $str1, $str2 ) {
		return substr_count( $source, $str1 ) === substr_count( $source, $str2 );
	}
}
