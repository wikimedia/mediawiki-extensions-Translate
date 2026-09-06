<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Validation\Validators;

use MediaWiki\Extension\Translate\MessageLoading\Message;
use MediaWiki\Extension\Translate\Validation\MessageValidator;
use MediaWiki\Extension\Translate\Validation\ValidationIssue;
use MediaWiki\Extension\Translate\Validation\ValidationIssues;

/**
 * Validates that every % in a translation is followed by ( (named parameter).
 * The %% escape sequence is accepted by stripping all %% pairs before checking.
 * Use this for projects that require Python-style named parameters such as
 * %(name)s and forbid positional or bare % signs.
 * @license GPL-2.0-or-later
 * @since 2026.09
 */
class PythonNamedParameterValidator implements MessageValidator {
	public function getIssues( Message $message, string $targetLanguage ): ValidationIssues {
		$issues = new ValidationIssues();
		$translation = str_replace( '%%', '', $message->translation() ?? '' );
		if ( preg_match( '/%(?![(])/u', $translation ) === 1 ) {
			$issues->add( new ValidationIssue( 'percent', 'not-named', 'translate-checks-percent-not-named' ) );
		}
		return $issues;
	}
}
