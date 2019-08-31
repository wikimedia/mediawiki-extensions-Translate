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
 * message at the beginning and end of the string. This works specifically
 * for GetText FFS.
 * @since 2019.09
 */
class GettextNewlineValidator extends NewlineValidator implements Validator {
	public function validate( TMessage $message, $code, array &$notices ) {
		$key = $message->key();
		$translation = $message->translation();
		$definition = $message->definition();

		// ending newlines in GetText are bounded by a "\"
		$definition = $this->removeTrailingSlash( $definition );
		$translation = $this->removeTrailingSlash( $translation );

		$definitionStartNewline = $this->getStartingNewLinesCount( $definition );
		$definitionEndNewline = $this->getEndingNewLineCount( $definition );

		$translationStartNewline = $this->getStartingNewLinesCount( $translation );
		$translationEndNewline = $this->getEndingNewLineCount( $translation );

		$failingChecks = array_merge(
			$this->validateStartingNewline( $definitionStartNewline, $translationStartNewline ),
			$this->validateEndingNewline( $definitionEndNewline, $translationEndNewline )
		);

		$this->addNotices( $failingChecks, $notices, $key, $code );
	}

	protected function removeTrailingSlash( $str ) {
		if ( substr( $str, -strlen( '\\' ) ) === '\\' ) {
			return substr( $str, 0, -1 );
		}

		return $str;
	}
}
