<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Validation\Validators;

use MediaWiki\Extension\Translate\Validation\MessageValidator;
use MediaWiki\Extension\Translate\Validation\ValidationIssue;
use MediaWiki\Extension\Translate\Validation\ValidationIssues;
use TMessage;

/**
 * Handles brace balance validation
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 * @since 2019.06
 */
class BraceBalanceValidator implements MessageValidator {
	public function getIssues( TMessage $message, string $targetLanguage ): ValidationIssues {
		$definition = $message->definition();
		$translation = $message->translation();
		$balanceIssues = [];
		$braceTypes = [
			[ '{', '}' ],
			[ '[', ']' ],
			[ '(', ')' ],
		];

		foreach ( $braceTypes as [ $open, $close ] ) {
			$definitionBalance = $this->getBalance( $definition, $open, $close );
			$translationBalance = $this->getBalance( $translation, $open, $close );

			if ( $definitionBalance === 0 && $translationBalance !== 0 ) {
				$balanceIssues[] = "$open$close: $translationBalance";
			}
		}

		$issues = new ValidationIssues();
		if ( $balanceIssues ) {
			$params = [
				[ 'PARAMS', $balanceIssues ],
				[ 'COUNT', count( $balanceIssues ) ],
			];

			// Create an issue if braces are unbalanced in translation, but balanced in the definition
			$issue = new ValidationIssue( 'balance', 'brace', 'translate-checks-balance', $params );
			$issues->add( $issue );
		}

		return $issues;
	}

	private function getBalance( string $source, string $str1, string $str2 ): int {
		return substr_count( $source, $str1 ) - substr_count( $source, $str2 );
	}
}
