<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Validation\Validators;

use MediaWiki\Extension\Translate\Utilities\UnicodePlural;
use MediaWiki\Extension\Translate\Validation\MessageValidator;
use MediaWiki\Extension\Translate\Validation\ValidationIssue;
use MediaWiki\Extension\Translate\Validation\ValidationIssues;
use TMessage;

/**
 * This is a very strict validator class for Unicode CLDR based plural markup.
 *
 * It requires all forms to be present and in correct order. Whitespace around keywords
 * and values is trimmed. The keyword `other` is left out, though it is allowed in input.
 * @since 2019.09
 * @license GPL-2.0-or-later
 */
class UnicodePluralValidator implements MessageValidator {
	public function getIssues( TMessage $message, string $targetLanguage ): ValidationIssues {
		$issues = new ValidationIssues();

		$expectedKeywords = UnicodePlural::getPluralKeywords( $targetLanguage );
		// Skip validation for languages for which we do not know the plural rule
		if ( $expectedKeywords === null ) {
			return $issues;
		}

		$definition = $message->definition();
		$translation = $message->translation();
		$definitionHasPlural = UnicodePlural::hasPlural( $definition );
		$translationHasPlural = UnicodePlural::hasPlural( $translation );

		$presence = $this->pluralPresenceCheck(
			$definitionHasPlural,
			$translationHasPlural
		);

		// Using same check keys as MediaWikiPluralValidator
		if ( $presence === 'missing' ) {
			$issue = new ValidationIssue( 'plural', 'missing', 'translate-checks-unicode-plural-missing' );
			$issues->add( $issue );
		} elseif ( $presence === 'unsupported' ) {
			$issue = new ValidationIssue( 'plural', 'unsupported', 'translate-checks-unicode-plural-unsupported' );
			$issues->add( $issue );
		} elseif ( $presence === 'ok' ) {
			[ $msgcode, $actualKeywords ] =
				$this->pluralFormCheck( $translation, $expectedKeywords );
			if ( $msgcode === 'invalid' ) {
				$expectedExample = UnicodePlural::flattenList(
					array_map( [ $this, 'createFormExample' ], $expectedKeywords )
				);
				$actualExample = UnicodePlural::flattenList(
					array_map( [ $this, 'createFormExample' ], $actualKeywords )
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
		} // else: not-applicable

		return $issues;
	}

	private function createFormExample( string $keyword ): array {
		return [ $keyword, '…' ];
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

	private function pluralFormCheck( string $text, array $expectedKeywords ): array {
		[ , $instanceMap ] = UnicodePlural::parsePluralForms( $text );

		foreach ( $instanceMap as $forms ) {
			$actualKeywords = [];
			foreach ( $forms as [ $keyword, ] ) {
				$actualKeywords[] = $keyword;
			}

			if ( $actualKeywords !== $expectedKeywords ) {
				return [ 'invalid', $actualKeywords ];
			}
		}

		return [ 'ok', [] ];
	}
}
