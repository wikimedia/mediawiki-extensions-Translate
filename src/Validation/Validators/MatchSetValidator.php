<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Validation\Validators;

use InvalidArgumentException;
use MediaWiki\Extension\Translate\MessageLoading\Message;
use MediaWiki\Extension\Translate\Validation\MessageValidator;
use MediaWiki\Extension\Translate\Validation\ValidationIssue;
use MediaWiki\Extension\Translate\Validation\ValidationIssues;

/**
 * Ensures that the translation for a message matches a value from a list.
 * @license GPL-2.0-or-later
 * @since 2019.12
 */
class MatchSetValidator implements MessageValidator {
	/** @var string[] */
	private array $possibleValues;
	/** @var string[] */
	private array $normalizedValues;
	private bool $caseSensitive;

	public function __construct( array $params ) {
		$this->possibleValues = $params['values'] ?? [];
		$this->caseSensitive = (bool)( $params['caseSensitive'] ?? true );

		if ( $this->possibleValues === [] ) {
			throw new InvalidArgumentException( 'No values provided for MatchSet validator.' );
		}

		if ( $this->caseSensitive ) {
			$this->normalizedValues = $this->possibleValues;
		} else {
			$this->normalizedValues = array_map( 'strtolower', $this->possibleValues );
		}
	}

	public function getIssues( Message $message, string $targetLanguage ): ValidationIssues {
		$issues = new ValidationIssues();

		$translation = $message->translation();
		if ( $this->caseSensitive ) {
			$translation = strtolower( $translation );
		}

		if ( !in_array( $translation, $this->normalizedValues, true ) ) {
			$issue = new ValidationIssue(
				'value-not-present',
				'invalid',
				'translate-checks-value-not-present',
				[
					[ 'PLAIN-PARAMS', $this->possibleValues ],
					[ 'COUNT', count( $this->possibleValues ) ]
				]
			);

			$issues->add( $issue );
		}

		return $issues;
	}
}
