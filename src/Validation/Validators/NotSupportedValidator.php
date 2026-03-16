<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Validation\Validators;

use InvalidArgumentException;
use MediaWiki\Extension\Translate\MessageLoading\Message;
use MediaWiki\Extension\Translate\Utilities\Utilities;
use MediaWiki\Extension\Translate\Validation\MessageValidator;
use MediaWiki\Extension\Translate\Validation\ValidationIssue;
use MediaWiki\Extension\Translate\Validation\ValidationIssues;

/**
 * Prevents use of unsupported features or patterns in translations.
 * @license GPL-2.0-or-later
 * @since 2025.03
 */
class NotSupportedValidator implements MessageValidator {
	private readonly string $regex;
	private readonly ?string $display;

	public function __construct( array $params ) {
		$this->regex = $params['regex'] ?? throw new InvalidArgumentException( '`regex` is required' );
		$this->display = $params['display'] ?? null;

		if ( !Utilities::isValidRegex( $this->regex, $error ) ) {
			throw new InvalidArgumentException(
				"`regex` value `{$this->regex}` is not a valid regular expression: $error"
			);
		}
	}

	public function getIssues( Message $message, string $targetLanguage ): ValidationIssues {
		$issues = new ValidationIssues();
		if ( preg_match( $this->regex, $message->translation(), $matches ) === 1 ) {
			$issue = new ValidationIssue(
				'notsupported',
				'notsupported',
				'translate-checks-notsupported',
				[
					[ 'PLAIN', $this->display ?? $matches[0] ],
				]
			);

			$issues->add( $issue );
		}

		return $issues;
	}
}
