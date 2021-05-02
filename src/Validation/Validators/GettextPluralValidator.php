<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Validation\Validators;

use MediaWiki\Extension\Translate\Utilities\GettextPlural;
use MediaWiki\Extension\Translate\Validation\MessageValidator;
use MediaWiki\Extension\Translate\Validation\ValidationIssue;
use MediaWiki\Extension\Translate\Validation\ValidationIssues;
use TMessage;

/**
 * @license GPL-2.0-or-later
 * @since 2019.09
 */
class GettextPluralValidator implements MessageValidator {
	public function getIssues( TMessage $message, string $targetLanguage ): ValidationIssues {
		$issues = new ValidationIssues();

		$pluralRule = GettextPlural::getPluralRule( $targetLanguage );
		// Skip validation for languages for which we do not know the plural rule
		if ( !$pluralRule ) {
			return $issues;
		}

		$definition = $message->definition();
		$translation = $message->translation();
		$expectedPluralCount = GettextPlural::getPluralCount( $pluralRule );
		$definitionHasPlural = GettextPlural::hasPlural( $definition );
		$translationHasPlural = GettextPlural::hasPlural( $translation );

		$presence = $this->pluralPresenceCheck(
			$definitionHasPlural,
			$translationHasPlural,
			$expectedPluralCount
		);

		if ( $presence === 'ok' ) {
			[ $msgcode, $data ] = $this->pluralFormCountCheck( $translation, $expectedPluralCount );
			if ( $msgcode === 'invalid-count' ) {
				$issue = new ValidationIssue(
					'plural',
					'forms',
					'translate-checks-gettext-plural-count',
					[
						[ 'COUNT', $expectedPluralCount ],
						[ 'COUNT', $data[ 'count' ] ],
					]
				);
				$issues->add( $issue );
			}
		} elseif ( $presence === 'missing' ) {
			$issue = new ValidationIssue(
				'plural',
				'missing',
				'translate-checks-gettext-plural-missing'
			);
			$issues->add( $issue );
		} elseif ( $presence === 'unsupported' ) {
			$issue = new ValidationIssue(
				'plural',
				'unsupported',
				'translate-checks-gettext-plural-unsupported'
			);
			$issues->add( $issue );
		}
		// else not-applicable: Plural is not present in translation, but that is fine

		return $issues;
	}

	private function pluralPresenceCheck(
		$definitionHasPlural,
		$translationHasPlural,
		$expectedPluralCount
	) {
		if ( !$definitionHasPlural && $translationHasPlural ) {
			return 'unsupported';
		} elseif ( $definitionHasPlural && !$translationHasPlural ) {
			if ( $expectedPluralCount > 1 ) {
				return 'missing';
			} else {
				// It's okay to omit plural completely for languages without variance
				return 'not-applicable';
			}
		} elseif ( !$definitionHasPlural && !$translationHasPlural ) {
			return 'not-applicable';
		}

		// Both have plural
		return 'ok';
	}

	private function pluralFormCountCheck( $text, $expectedPluralCount ) {
		[ , $instanceMap ] = GettextPlural::parsePluralForms( $text );

		foreach ( $instanceMap as $forms ) {
			$formsCount = count( $forms );
			if ( $formsCount !== $expectedPluralCount ) {
				return [ 'invalid-count', [ 'count' => $formsCount ] ];
			}
		}

		return [ 'ok', [] ];
	}
}
