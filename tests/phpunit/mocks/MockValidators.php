<?php
/**
 * Contains mock validators used for testing purpose.
 *
 * @file
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 */

use MediaWiki\Extensions\Translate\Validation\MessageValidator;
use MediaWiki\Extensions\Translate\Validation\ValidationIssue;
use MediaWiki\Extensions\Translate\Validation\ValidationIssues;

class MockTranslateValidator implements MessageValidator {
	public function getIssues( TMessage $message, string $targetLanguage ): ValidationIssues {
		$issues = new ValidationIssues();
		$issues->add( new ValidationIssue( 'plural', 'missing', 'translate-checks-plural' ) );
		$issues->add( new ValidationIssue( 'pagename', 'namespace', 'translate-checks-pagename' ) );

		return $issues;
	}
}

class AnotherMockTranslateValidator implements MessageValidator {
	public function getIssues( TMessage $message, string $targetLanguage ): ValidationIssues {
		$issues = new ValidationIssues();
		$issues->add( new ValidationIssue( 'plural', 'dupe', 'translate-checks-plural-dupe' ) );

		return $issues;
	}
}
