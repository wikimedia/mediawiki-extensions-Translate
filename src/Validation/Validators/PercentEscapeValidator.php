<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Validation\Validators;

use MediaWiki\Extension\Translate\MessageLoading\Message;
use MediaWiki\Extension\Translate\Validation\MessageValidator;
use MediaWiki\Extension\Translate\Validation\ValidationIssue;
use MediaWiki\Extension\Translate\Validation\ValidationIssues;

/**
 * Validates that every % in a translation is followed by a printable ASCII
 * character (0x21–0x7E, i.e. excluding space) or another % (i.e. the %%
 * escape sequence). A bare % before a non-ASCII character, a space, a newline,
 * or at end-of-string will cause build failures in projects that use
 * %-based string interpolation. Space (0x20) is intentionally excluded: it is
 * not a valid interpolation specifier in any supported format.
 *
 * Known limitation: the space flag (e.g. `% d`) is a valid printf modifier in
 * Python, C/POSIX, and PHP, but is rejected by this validator. This is
 * acceptable because space-flagged conversions are not used in practice in
 * translateable strings.
 * @license GPL-2.0-or-later
 * @since 2026.09
 */
class PercentEscapeValidator implements MessageValidator {
	public function getIssues( Message $message, string $targetLanguage ): ValidationIssues {
		$issues = new ValidationIssues();
		$translation = str_replace( '%%', '', $message->translation() ?? '' );
		if ( preg_match( '/%(?![!-~])/u', $translation ) === 1 ) {
			$issues->add( new ValidationIssue( 'percent', 'invalid', 'translate-checks-percent-invalid' ) );
		}
		return $issues;
	}
}
