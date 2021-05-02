<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Validation\Validators;

use MediaWiki\Extension\Translate\Validation\MessageValidator;
use MediaWiki\Extension\Translate\Validation\ValidationIssue;
use MediaWiki\Extension\Translate\Validation\ValidationIssues;
use TMessage;

/**
 * Ensures that the translation has the same number of newlines as the source
 * message at the beginning of the string.
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 * @since 2019.09
 */
class NewlineValidator implements MessageValidator {
	public function getIssues( TMessage $message, string $targetLanguage ): ValidationIssues {
		$translation = $message->translation();
		$definition = $message->definition();

		$definitionStartNewline = $this->getStartingNewLinesCount( $definition );
		$translationStartNewline = $this->getStartingNewLinesCount( $translation );

		$failingChecks = $this->validateStartingNewline(
			$definitionStartNewline, $translationStartNewline
		);

		return $this->createIssues( $failingChecks );
	}

	protected function getStartingNewLinesCount( string $str ): int {
		return strspn( $str, "\n" );
	}

	protected function getEndingNewLineCount( string $str ): int {
		return strspn( strrev( $str ), "\n" );
	}

	protected function validateStartingNewline(
		int $definitionStartNewline,
		int $translationStartNewline
	): array {
		$failingChecks = [];
		if ( $definitionStartNewline < $translationStartNewline ) {
			// Extra whitespace at beginning
			$failingChecks[] = [
				'extra-start',
				$translationStartNewline - $definitionStartNewline
			];
		} elseif ( $definitionStartNewline > $translationStartNewline ) {
			// Missing whitespace at beginning
			$failingChecks[] = [
				'missing-start',
				$definitionStartNewline - $translationStartNewline
			];
		}

		return $failingChecks;
	}

	protected function validateEndingNewline(
		int $definitionEndNewline,
		int $translationEndNewline
	): array {
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

	protected function createIssues( array $failingChecks ): ValidationIssues {
		$issues = new ValidationIssues();
		foreach ( $failingChecks as [ $subType, $count ] ) {
			$issue = new ValidationIssue(
				'newline',
				$subType,
				"translate-checks-newline-$subType",
				[ 'COUNT', $count ]
			);

			$issues->add( $issue );
		}

		return $issues;
	}
}
