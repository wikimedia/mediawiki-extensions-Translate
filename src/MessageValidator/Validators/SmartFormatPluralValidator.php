<?php
/**
 * @file
 * @license GPL-2.0-or-later
 */

namespace MediaWiki\Extensions\Translate\MessageValidator\Validators;

use Insertable;
use InsertablesSuggester;
use MediaWiki\Extensions\Translate\MessageValidator\Validator;
use MediaWiki\Extensions\Translate\Utilities\SmartFormatPlural;
use MediaWiki\Extensions\Translate\Utilities\UnicodePlural;
use TMessage;

/**
 * @since 2019.11
 */
class SmartFormatPluralValidator implements Validator, InsertablesSuggester {
	public function validate( TMessage $message, $code, array &$notices ) : void {
		$expectedKeywords = UnicodePlural::getPluralKeywords( $code );
		// Skip validation for languages for which we do not know the plural rule
		if ( $expectedKeywords === null ) {
			return;
		}

		$key = $message->key();
		$definition = $message->definition();
		$translation = $message->translation();
		$expectedPluralCount = count( $expectedKeywords );
		$definitionPlurals = SmartFormatPlural::getPluralInstances( $definition );
		$translationPlurals = SmartFormatPlural::getPluralInstances( $translation );

		$unsupportedVariables = array_diff(
			array_keys( $translationPlurals ), array_keys( $definitionPlurals )
		);

		foreach ( $unsupportedVariables as $unsupportedVariable ) {
			$notices[$key][] = [
				[ 'plural', 'unsupported', $key, $code ],
				'translate-checks-smartformat-plural-unsupported',
				[ 'PLAIN', '{' . $unsupportedVariable . '}' ],
			];
		}

		if ( $expectedPluralCount > 1 ) {
			$missingVariables = array_diff(
				array_keys( $definitionPlurals ), array_keys( $translationPlurals )
			);

			foreach ( $missingVariables as $missingVariable ) {
				$notices[$key][] = [
					[ 'plural', 'missing', $key, $code ],
					'translate-checks-smartformat-plural-missing',
					[ 'PLAIN', '{' . $missingVariable . '}' ],
				];
			}
		}

		// This returns only translation plurals for variables that exists in source
		$commonVariables = array_intersect_key( $translationPlurals, $definitionPlurals );
		foreach ( $commonVariables as $pluralInstances ) {
			foreach ( $pluralInstances as $pluralInstance ) {
				$actualPluralCount = count( $pluralInstance[ 'forms' ] );
				if ( $actualPluralCount !== $expectedPluralCount ) {
					$notices[$key][] = [
						// Using same check keys as MediaWikiPluralValidator
						[ 'plural', 'forms', $key, $code ],
						'translate-checks-smartformat-plural-count',
						[ 'COUNT', $expectedPluralCount ],
						[ 'COUNT', $actualPluralCount ],
						[ 'PLAIN', $pluralInstance[ 'original' ] ],
					];
				}
			}
		}
	}

	/**
	 * @inheritDoc
	 */
	public function getInsertables( $text ) : array {
		$definitionPlurals = SmartFormatPlural::getPluralInstances( $text );
		$insertables = [];

		// This could be more language specific if we were given more information, but
		// we only have text.
		foreach ( array_keys( $definitionPlurals ) as $variable ) {
			$pre = '{' . "$variable:";
			$post = '|}';
			$insertables[] = new Insertable( "$pre$post", $pre, $post );
		}

		return $insertables;
	}
}
