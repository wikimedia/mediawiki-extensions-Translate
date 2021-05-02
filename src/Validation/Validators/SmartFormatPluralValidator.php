<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Validation\Validators;

use MediaWiki\Extension\Translate\TranslatorInterface\Insertable\Insertable;
use MediaWiki\Extension\Translate\TranslatorInterface\Insertable\InsertablesSuggester;
use MediaWiki\Extension\Translate\Utilities\SmartFormatPlural;
use MediaWiki\Extension\Translate\Utilities\UnicodePlural;
use MediaWiki\Extension\Translate\Validation\MessageValidator;
use MediaWiki\Extension\Translate\Validation\ValidationIssue;
use MediaWiki\Extension\Translate\Validation\ValidationIssues;
use TMessage;

/**
 * @license GPL-2.0-or-later
 * @since 2019.11
 */
class SmartFormatPluralValidator implements MessageValidator, InsertablesSuggester {
	public function getIssues( TMessage $message, string $targetLanguage ): ValidationIssues {
		$issues = new ValidationIssues();

		$expectedKeywords = UnicodePlural::getPluralKeywords( $targetLanguage );
		// Skip validation for languages for which we do not know the plural rule
		if ( $expectedKeywords === null ) {
			return $issues;
		}

		$definition = $message->definition();
		$translation = $message->translation();
		$expectedPluralCount = count( $expectedKeywords );
		$definitionPlurals = SmartFormatPlural::getPluralInstances( $definition );
		$translationPlurals = SmartFormatPlural::getPluralInstances( $translation );

		$unsupportedVariables = array_diff(
			array_keys( $translationPlurals ), array_keys( $definitionPlurals )
		);

		foreach ( $unsupportedVariables as $unsupportedVariable ) {
			$issue = new ValidationIssue(
				'plural',
				'unsupported',
				'translate-checks-smartformat-plural-unsupported',
				[
					[ 'PLAIN', '{' . $unsupportedVariable . '}' ],
				]
			);

			$issues->add( $issue );
		}

		if ( $expectedPluralCount > 1 ) {
			$missingVariables = array_diff(
				array_keys( $definitionPlurals ), array_keys( $translationPlurals )
			);

			foreach ( $missingVariables as $missingVariable ) {
				$issue = new ValidationIssue(
					'plural',
					'missing',
					'translate-checks-smartformat-plural-missing',
					[
						[ 'PLAIN', '{' . $missingVariable . '}' ],
					]
				);

				$issues->add( $issue );
			}
		}

		// This returns only translation plurals for variables that exists in source
		$commonVariables = array_intersect_key( $translationPlurals, $definitionPlurals );
		foreach ( $commonVariables as $pluralInstances ) {
			foreach ( $pluralInstances as $pluralInstance ) {
				$actualPluralCount = count( $pluralInstance[ 'forms' ] );
				if ( $actualPluralCount !== $expectedPluralCount ) {
					$issue = new ValidationIssue(
						'plural',
						'forms',
						'translate-checks-smartformat-plural-count',
						[
							[ 'COUNT', $expectedPluralCount ],
							[ 'COUNT', $actualPluralCount ],
							[ 'PLAIN', $pluralInstance[ 'original' ] ],
						]
					);

					$issues->add( $issue );
				}
			}
		}

		return $issues;
	}

	public function getInsertables( string $text ): array {
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
