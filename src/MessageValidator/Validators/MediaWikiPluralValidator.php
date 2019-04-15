<?php
/**
 * @file
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 */

namespace MediaWiki\Extensions\Translate\MessageValidator\Validators;

use MediaWiki\Extensions\Translate\MessageValidator\Validator;

/**
 * Handles plural validation for MediaWiki text
 * @since 2019.05
 */
class MediaWikiPluralValidator implements Validator {
	public function validate( $messages, $code, array &$notices ) {
		$this->pluralCheck( $messages, $code, $notices );
		$this->pluralFormsCheck( $messages, $code, $notices );
	}

	/**
	 * Checks if the translation doesn't use plural while the definition has one.
	 *
	 * @param TMessage[] $messages Iterable list of TMessage objects.
	 * @param string $code Language code of the translations.
	 * @param array &$notices Array where warnings / errors are appended to.
	 */
	protected function pluralCheck( $messages, $code, &$notices ) {
		foreach ( $messages as $message ) {
			$key = $message->key();
			$definition = $message->definition();
			$translation = $message->translation();

			$subcheck = 'missing';
			if (
				stripos( $definition, '{{plural:' ) !== false &&
				stripos( $translation, '{{plural:' ) === false
			) {
				$notices[$key][] = [
					[ 'plural', $subcheck, $key, $code ],
					'translate-checks-plural',
				];
			}
		}
	}

	/**
	 * Checks if the translation uses too many plural forms
	 * @param TMessage[] $messages
	 * @param string $code
	 * @param array &$notices
	 */
	protected function pluralFormsCheck( $messages, $code, &$notices ) {
		foreach ( $messages as $message ) {
			$key = $message->key();
			$translation = $message->translation();

			// Are there any plural forms for this language in this message?
			if ( stripos( $translation, '{{plural:' ) === false ) {
				return;
			}

			$plurals = self::getPluralForms( $translation );
			$allowed = self::getPluralFormCount( $code );

			foreach ( $plurals as $forms ) {
				$forms = self::removeExplicitPluralForms( $forms );
				$provided = count( $forms );

				if ( $provided > $allowed ) {
					$notices[$key][] = [
						[ 'plural', 'forms', $key, $code ],
						'translate-checks-plural-forms', $provided, $allowed
					];
				}

				// Are the last two forms identical?
				if ( $provided > 1 && $forms[$provided - 1] === $forms[$provided - 2] ) {
					$notices[$key][] = [
						[ 'plural', 'dupe', $key, $code ],
						'translate-checks-plural-dupe'
					];
				}
			}
		}
	}

	/**
	 * Returns the number of plural forms %MediaWiki supports
	 * for a language.
	 * @param string $code Language code
	 * @return int
	 */
	public static function getPluralFormCount( $code ) {
		$forms = \Language::factory( $code )->getPluralRules();

		// +1 for the 'other' form
		return count( $forms ) + 1;
	}

	/**
	 * Ugly home made probably awfully slow looping parser
	 * that parses {{PLURAL}} instances from message and
	 * returns array of invokations having array of forms.
	 * @param string $translation
	 * @return array[]
	 */
	public static function getPluralForms( $translation ) {
		// Stores the forms from plural invocations
		$plurals = [];

		$cb = function ( $parser, $frame, $args ) use ( &$plurals ) {
			$forms = [];

			foreach ( $args as $index => $form ) {
				// The first arg is the number, we skip it
				if ( $index !== 0 ) {
					// Collect the raw text
					$forms[] = $frame->expand( $form, \PPFrame::RECOVER_ORIG );
					// Expand the text to process embedded plurals
					$frame->expand( $form );
				}
			}
			$plurals[] = $forms;

			return '';
		};

		// Setup parser
		$parser = new \Parser();
		// Load the default magic words etc now.
		$parser->firstCallInit();
		// So that they don't overrider our own callback
		$parser->setFunctionHook( 'plural', $cb, \Parser::SFH_NO_HASH | \Parser::SFH_OBJECT_ARGS );

		// Setup things needed for preprocess
		$title = null;
		$options = new \ParserOptions( new \User(), \Language::factory( 'en' ) );

		$parser->preprocess( $translation, $title, $options );

		return $plurals;
	}

	/**
	 * Imitiates the core plural form handling by removing
	 * plural forms that start with explicit number.
	 * @param array $forms
	 * @return array
	 */
	public static function removeExplicitPluralForms( array $forms ) {
		// Handle explicit 0= and 1= forms
		foreach ( $forms as $index => $form ) {
			if ( preg_match( '/^[0-9]+=/', $form ) ) {
				unset( $forms[$index] );
			}
		}

		return array_values( $forms );
	}
}
