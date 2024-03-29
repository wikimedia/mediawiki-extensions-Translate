<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Validation\Validators;

use MediaWiki\Extension\Translate\MessageLoading\Message;
use MediaWiki\Extension\Translate\Validation\MessageValidator;
use MediaWiki\Extension\Translate\Validation\ValidationIssue;
use MediaWiki\Extension\Translate\Validation\ValidationIssues;

class NotEmptyValidator implements MessageValidator {
	public function getIssues( Message $message, string $targetLanguage ): ValidationIssues {
		$translation = $message->translation();
		$issues = new ValidationIssues();

		if ( $translation !== null && trim( $translation ) === '' ) {
			$issues->add(
				new ValidationIssue(
					'empty',
					'empty',
					'translate-checks-empty'
				)
			);
		}

		return $issues;
	}
}
