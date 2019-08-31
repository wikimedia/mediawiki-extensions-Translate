<?php

/**
 * @file
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 */

namespace MediaWiki\Extensions\Translate\MessageValidator\Validators;

use MediaWiki\Extensions\Translate\MessageValidator\Validator;
use TMessage;

/**
 * Ensures that the translation has the same number of newlines as the source
 * message at the beginning of the string.
 * @since 2019.09
 */
class NewlineValidator implements Validator {
	public function validate( TMessage $message, $code, array &$notices ) {
		$key = $message->key();
		$translation = $message->translation();
		$definition = $message->definition();

		$definitionStartNewline = $this->getStartingNewLinesCount( $definition );
		$translationStartNewline = $this->getStartingNewLinesCount( $translation );

		$failingChecks = $this->validateStartingNewline(
			$definitionStartNewline, $translationStartNewline
		);

		$this->addNotices( $failingChecks, $notices, $key, $code );
	}

	protected function getStartingNewLinesCount( $str ) {
		return strspn( $str, "\n" );
	}

	protected function getEndingNewLineCount( $str ) {
		return strspn( strrev( $str ), "\n" );
	}

	protected function validateStartingNewline(
		$definitionStartNewline, $translationStartNewline
	) {
		$failingChecks = [];
		if ( $definitionStartNewline < $translationStartNewline ) {
			// Extra whitespace at beginning
			$failingChecks[] = [
				'extra-start',
				$translationStartNewline - $definitionStartNewline
			];
		} elseif ( $definitionStartNewline > $translationStartNewline ) {
			// Missing whitespace at beginnning
			$failingChecks[] = [
				'missing-start',
				$definitionStartNewline - $translationStartNewline
			];
		}

		return $failingChecks;
	}

	protected function validateEndingNewline( $definitionEndNewline, $translationEndNewline ) {
		$failingChecks = [];
		if ( $definitionEndNewline < $translationEndNewline ) {
			// Extra whitespace at end
			$failingChecks[] = [
				'extra-end',
				$translationEndNewline - $definitionEndNewline
			];
		} elseif ( $definitionEndNewline > $translationEndNewline ) {
			// Missing whitespace at end
			$failingChecks[] = [
				'missing-end',
				$definitionEndNewline - $translationEndNewline
			];
		}

		return $failingChecks;
	}

	protected function addNotices( $failingChecks, &$notices, $key, $code ) {
		foreach ( $failingChecks as $subcheck ) {
			$notices[$key][] = [
				[ 'newline', $subcheck[0], $key, $code ],
				'translate-checks-newline-' . $subcheck[0],
				[ 'COUNT', $subcheck[1] ]
			];
		}
	}
}
