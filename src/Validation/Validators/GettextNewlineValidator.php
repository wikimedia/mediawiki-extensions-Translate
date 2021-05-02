<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Validation\Validators;

use MediaWiki\Extension\Translate\Validation\ValidationIssues;
use TMessage;

/**
 * Ensures that the translation has the same number of newlines as the source
 * message at the beginning and end of the string. This works specifically
 * for GettextFFS.
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 * @since 2019.09
 */
class GettextNewlineValidator extends NewlineValidator {
	public function getIssues( TMessage $message, string $targetLanguage ): ValidationIssues {
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

		return $this->createIssues( $failingChecks );
	}

	private function removeTrailingSlash( string $str ): string {
		if ( substr( $str, -strlen( '\\' ) ) === '\\' ) {
			return substr( $str, 0, -1 );
		}

		return $str;
	}
}
