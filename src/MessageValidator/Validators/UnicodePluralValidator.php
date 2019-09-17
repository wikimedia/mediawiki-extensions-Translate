<?php
/**
 * @file
 * @license GPL-2.0-or-later
 */

namespace MediaWiki\Extensions\Translate\MessageValidator\Validators;

use MediaWiki\Extensions\Translate\MessageValidator\Validator;
use MediaWiki\Extensions\Translate\Utilities\UnicodePlural;
use TMessage;

/**
 * This is a very strict validator class for Unicode CLDR based plural markup.
 *
 * It requires all forms to be present and in correct order. Whitespace around keywords
 * and values are trimmed. The keyword `other` is left out, though it is allowed in input.
 * @since 2019.09
 */
class UnicodePluralValidator implements Validator {
	public function validate( TMessage $message, $code, array &$notices ) {
		global $wgTranslateDocumentationLanguageCode;

		if ( $code === $wgTranslateDocumentationLanguageCode ) {
			return;
		}

		$expectedKeywords = UnicodePlural::getPluralKeywords( $code );
		// Skip validation for languages for which we do not know the plural rule
		if ( $expectedKeywords === null ) {
			return;
		}

		$key = $message->key();
		$definition = $message->definition();
		$translation = $message->translation();
		$definitionHasPlural = UnicodePlural::hasPlural( $definition );
		$translationHasPlural = UnicodePlural::hasPlural( $translation );

		$presence = $this->pluralPresenceCheck(
			$definitionHasPlural,
			$translationHasPlural
		);

		if ( $presence === 'not-applicable' ) {
			// Plural is not present in translation, but that is fine

			return;
		} elseif ( $presence === 'missing' ) {
			$notices[$key][] = [
				// Using same check keys as MediaWikiPluralValidator
				[ 'plural', 'missing', $key, $code ],
				'translate-checks-unicode-plural-missing'
			];

			return;
		} elseif ( $presence === 'unsupported' ) {
			$notices[$key][] = [
				[ 'plural', 'unsupported', $key, $code ],
				'translate-checks-unicode-plural-unsupported'
			];

			return;
		}

		list( $msgcode, $actualKeywords ) = $this->pluralFormCheck( $translation, $expectedKeywords );
		if ( $msgcode === 'invalid' ) {
			$expectedExample = UnicodePlural::flattenList(
				array_map( [ $this, 'createFormExample' ], $expectedKeywords )
			);
			$actualExample = UnicodePlural::flattenList(
				array_map( [ $this, 'createFormExample' ], $actualKeywords )
			);

			$notices[$key][] = [
				// Using same check keys as MediaWikiPluralValidator
				[ 'plural', 'forms', $key, $code ],
				'translate-checks-unicode-plural-invalid',
				[ 'PLAIN',  $expectedExample ],
				[ 'PLAIN', $actualExample ],
			];
		}
	}

	private function createFormExample( $keyword ) {
		return [ $keyword, '…' ];
	}

	private function pluralPresenceCheck(
		$definitionHasPlural,
		$translationHasPlural
	) {
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

	private function pluralFormCheck( $text, $expectedKeywords ) {
		list( , $instanceMap ) = UnicodePlural::parsePluralForms( $text );

		foreach ( $instanceMap as $forms ) {
			$actualKeywords = [];
			foreach ( $forms as list( $keyword, ) ) {
				$actualKeywords[] = $keyword;
			}

			if ( $actualKeywords !== $expectedKeywords ) {
				return [ 'invalid', $actualKeywords ];
			}
		}

		return [ 'ok', [] ];
	}
}
