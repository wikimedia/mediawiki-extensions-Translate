<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Validation\Validators;

use MediaWiki\Extension\Translate\MessageLoading\Message;
use MediaWiki\Extension\Translate\Validation\MessageValidator;
use MediaWiki\Extension\Translate\Validation\ValidationIssue;
use MediaWiki\Extension\Translate\Validation\ValidationIssues;

/**
 * Validates that every % in a translation is followed by a non-zero argument
 * index in the form N$ (e.g. %1$s, %2$d). Field widths without an explicit
 * index (e.g. %10s, %2d) are rejected. The %% escape sequence is accepted by
 * stripping all %% pairs before checking. Use this for projects that require
 * explicit argument indices and forbid positional or bare % signs.
 * @license GPL-2.0-or-later
 * @since 2026.09
 */
class PrintfIndexedParameterValidator implements MessageValidator {
	public function getIssues( Message $message, string $targetLanguage ): ValidationIssues {
		$issues = new ValidationIssues();
		$translation = str_replace( '%%', '', $message->translation() ?? '' );
		if ( preg_match( '/%(?![1-9]\d*\$)/u', $translation ) === 1 ) {
			$issues->add( new ValidationIssue( 'percent', 'not-indexed', 'translate-checks-percent-not-indexed' ) );
		}
		return $issues;
	}
}
