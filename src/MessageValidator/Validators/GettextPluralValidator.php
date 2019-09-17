<?php
/**
 * @file
 * @license GPL-2.0-or-later
 */

namespace MediaWiki\Extensions\Translate\MessageValidator\Validators;

use MediaWiki\Extensions\Translate\MessageValidator\Validator;
use MediaWiki\Extensions\Translate\Utilities\GettextPlural;
use TMessage;

/**
 * @since 2019.09
 */
class GettextPluralValidator implements Validator {
	public function validate( TMessage $message, $code, array &$notices ) {
		$pluralRule = GettextPlural::getPluralRule( $code );
		// Skip validation for languages for which we do not know the plural rule
		if ( !$pluralRule ) {
			return;
		}

		$key = $message->key();
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

		if ( $presence === 'not-applicable' ) {
			// Plural is not present in translation, but that is fine

			return;
		} elseif ( $presence === 'missing' ) {
			$notices[$key][] = [
				// Using same check keys as MediaWikiPluralValidator
				[ 'plural', 'missing', $key, $code ],
				'translate-checks-gettext-plural-missing'
			];

			return;
		} elseif ( $presence === 'unsupported' ) {
			$notices[$key][] = [
				[ 'plural', 'unsupported', $key, $code ],
				'translate-checks-gettext-plural-unsupported'
			];

			return;
		}

		list( $msgcode, $data ) = $this->pluralFormCountCheck( $translation, $expectedPluralCount );
		if ( $msgcode === 'invalid-count' ) {
			$notices[$key][] = [
				// Using same check keys as MediaWikiPluralValidator
				[ 'plural', 'forms', $key, $code ],
				'translate-checks-gettext-plural-count',
				[ 'COUNT', $expectedPluralCount ],
				[ 'COUNT', $data[ 'count' ] ],
			];
		}
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
		list( , $instanceMap ) = GettextPlural::parsePluralForms( $text );

		foreach ( $instanceMap as $forms ) {
			$formsCount = count( $forms );
			if ( $formsCount !== $expectedPluralCount ) {
				return [ 'invalid-count', [ 'count' => $formsCount ] ];
			}
		}

		return [ 'ok', [] ];
	}
}
