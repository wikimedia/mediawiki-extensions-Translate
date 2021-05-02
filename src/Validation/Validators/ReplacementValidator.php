<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Validation\Validators;

use InvalidArgumentException;
use MediaWiki\Extension\Translate\Validation\MessageValidator;
use MediaWiki\Extension\Translate\Validation\ValidationIssue;
use MediaWiki\Extension\Translate\Validation\ValidationIssues;
use TMessage;

/**
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @since 2020.07
 */
class ReplacementValidator implements MessageValidator {
	private $search;
	private $replace;

	public function __construct( array $params ) {
		$this->search = $params['search'] ?? null;
		$this->replace = $params['replace'] ?? null;
		if ( !is_string( $this->search ) ) {
			throw new InvalidArgumentException( '`search` is not a string' );
		}

		if ( !is_string( $this->replace ) ) {
			throw new InvalidArgumentException( '`replace` is not a string' );
		}
	}

	public function getIssues( TMessage $message, string $targetLanguage ): ValidationIssues {
		$issues = new ValidationIssues();

		if ( strpos( $message->translation(), $this->search ) !== false ) {
			$issue = new ValidationIssue(
				'replacement',
				'replacement',
				'translate-checks-replacement',
				[
					[ 'PLAIN', $this->search ],
					[ 'PLAIN', $this->replace ],
				]
			);

			$issues->add( $issue );
		}

		return $issues;
	}
}
