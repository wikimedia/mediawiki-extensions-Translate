<?php
/**
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */

declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Validation;

use MediaWiki\Extension\Translate\TranslatorInterface\Insertable\InsertablesSuggester;
use TMessage;

/**
 * Object adapter for message validators that implement the deprecated interface.
 *
 * @since 2020.06
 */
class LegacyValidatorAdapter implements MessageValidator, InsertablesSuggester {
	/** @var Validator */
	private $validator;

	public function __construct( Validator $validator ) {
		$this->validator = $validator;
	}

	/** @inheritDoc */
	public function getIssues( TMessage $message, string $targetLanguage ): ValidationIssues {
		$notices = [];
		$this->validator->validate( $message, $targetLanguage, $notices );
		return $this->convertNoticesToValidationIssues( $notices, $message->key() );
	}

	private function convertNoticesToValidationIssues(
		array $notices,
		string $messageKey
	): ValidationIssues {
		$issues = new ValidationIssues();
		foreach ( $notices[$messageKey] ?? [] as $notice ) {
			$issue = new ValidationIssue(
				$notice[0][0],
				$notice[0][1],
				$notice[1],
				array_slice( $notice, 2 )
			);
			$issues->add( $issue );
		}

		return $issues;
	}

	/** @inheritDoc */
	public function getInsertables( string $text ): array {
		if ( $this->validator instanceof InsertablesSuggester ) {
			return $this->validator->getInsertables( $text );
		}

		return [];
	}
}

class_alias( LegacyValidatorAdapter::class, '\MediaWiki\Extensions\Translate\LegacyValidatorAdapter' );
