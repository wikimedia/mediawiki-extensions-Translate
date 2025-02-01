<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Validation\Validators;

use MediaWiki\Extension\Translate\MessageLoading\Message;
use MediaWiki\Extension\Translate\Utilities\UnicodePlural;
use MediaWiki\Extension\Translate\Validation\MessageValidator;
use MediaWiki\Extension\Translate\Validation\ValidationIssue;
use MediaWiki\Extension\Translate\Validation\ValidationIssues;

/**
 * This is a very strict validator class for Unicode CLDR based plural markup.
 *
 * It requires all forms to be present and in correct order. Whitespace around keywords
 * and values is trimmed. The keyword `other` is left out, though it is allowed in input.
 * @since 2019.09
 * @license GPL-2.0-or-later
 */
class UnicodePluralValidator implements MessageValidator {
	public function getIssues( Message $message, string $targetLanguage ): ValidationIssues {
		$issues = new ValidationIssues();

		// We skip certain validations if we don't know the expected keywords
		$expectedKeywords = UnicodePlural::getPluralKeywords( $targetLanguage );

		$definition = $message->definition();
		$translation = $message->translation();
		$definitionHasPlural = UnicodePlural::hasPlural( $definition );
		$translationHasPlural = UnicodePlural::hasPlural( $translation );

		$presence = $this->pluralPresenceCheck(
			$definitionHasPlural,
			$translationHasPlural
		);

		// Using same check keys as MediaWikiPluralValidator
		if ( $presence === 'missing' && $expectedKeywords !== null ) {
			$issue = new ValidationIssue( 'plural', 'missing', 'translate-checks-unicode-plural-missing' );
			$issues->add( $issue );
		} elseif ( $presence === 'unsupported' ) {
			$issue = new ValidationIssue( 'plural', 'unsupported', 'translate-checks-unicode-plural-unsupported' );
			$issues->add( $issue );
		} elseif ( $presence === 'ok' ) {
			[ $msgcode, $actualKeywords ] = $this->pluralFormCheck( $translation, $expectedKeywords );
			if ( $msgcode === 'invalid' ) {
				$formatter = static fn ( string $x ) => [ $x, '…' ];
				$expectedExample = UnicodePlural::flattenList(
					array_map( $formatter, $expectedKeywords ?? UnicodePlural::KEYWORDS )
				);
				$actualExample = UnicodePlural::flattenList(
					array_map( $formatter, $actualKeywords )
				);

				$issue = new ValidationIssue(
					'plural',
					'forms',
					'translate-checks-unicode-plural-invalid',
					[
						[ 'PLAIN', $expectedExample ],
						[ 'PLAIN', $actualExample ],
					]
				);
				$issues->add( $issue );
			}
		}
		// else: not-applicable

		return $issues;
	}

	private function pluralPresenceCheck(
		bool $definitionHasPlural,
		bool $translationHasPlural
	): string {
		if ( !$definitionHasPlural && $translationHasPlural ) {
			return 'unsupported';
		} elseif ( $definitionHasPlural && !$translationHasPlural ) {
			return 'missing';
		} elseif ( !$definitionHasPlural && !$translationHasPlural ) {
			return 'not-applicable';
		}

		// Both have plural
		return 'ok';
	}

	private function pluralFormCheck( string $text, ?array $expectedKeywords ): array {
		[ , $instanceMap ] = UnicodePlural::parsePluralForms( $text );

		foreach ( $instanceMap as $forms ) {
			$actualKeywords = [];
			foreach ( $forms as [ $keyword, ] ) {
				$actualKeywords[] = $keyword;
			}

			if ( $expectedKeywords !== null ) {
				if ( $actualKeywords !== $expectedKeywords ) {
					return [ 'invalid', $actualKeywords ];
				}
			} elseif ( array_diff( $actualKeywords, UnicodePlural::KEYWORDS ) !== [] ) {
				// We don't know the actual forms for the language, but complain about those
				// that are not valid in any language
				return [ 'invalid', $actualKeywords ];
			}
		}

		return [ 'ok', [] ];
	}
}
