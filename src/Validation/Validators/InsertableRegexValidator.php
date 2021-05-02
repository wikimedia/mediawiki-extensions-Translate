<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Validation\Validators;

use InvalidArgumentException;
use MediaWiki\Extension\Translate\TranslatorInterface\Insertable\RegexInsertablesSuggester;
use MediaWiki\Extension\Translate\Validation\MessageValidator;
use MediaWiki\Extension\Translate\Validation\ValidationIssue;
use MediaWiki\Extension\Translate\Validation\ValidationIssues;
use TMessage;

/**
 * A generic regex validator and insertable that can be reused by other classes.
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 * @since 2019.06
 */
class InsertableRegexValidator extends RegexInsertablesSuggester implements MessageValidator {
	/** @var string */
	private $validationRegex;

	public function __construct( $params ) {
		parent::__construct( $params );

		if ( is_string( $params ) ) {
			$this->validationRegex = $params;
		} elseif ( is_array( $params ) ) {
			$this->validationRegex = $params['regex'] ?? null;
		}

		if ( $this->validationRegex === null ) {
			throw new InvalidArgumentException( 'The configuration for InsertableRegexValidator does not ' .
				'specify a regular expression.' );
		}
	}

	public function getIssues( TMessage $message, string $targetLanguage ): ValidationIssues {
		$issues = new ValidationIssues();

		preg_match_all( $this->validationRegex, $message->definition(), $definitionMatch );
		preg_match_all( $this->validationRegex, $message->translation(), $translationMatch );
		$definitionVariables = $definitionMatch[0];
		$translationVariables = $translationMatch[0];

		$missingVariables = array_diff( $definitionVariables, $translationVariables );
		if ( $missingVariables ) {
			$issue = new ValidationIssue(
				'variable',
				'missing',
				'translate-checks-parameters',
				[
					[ 'PLAIN-PARAMS', $missingVariables ],
					[ 'COUNT', count( $missingVariables ) ]
				]
			);

			$issues->add( $issue );
		}

		$unknownVariables = array_diff( $translationVariables, $definitionVariables );
		if ( $unknownVariables ) {
			$issue = new ValidationIssue(
				'variable',
				'unknown',
				'translate-checks-parameters-unknown',
				[
					[ 'PLAIN-PARAMS', $unknownVariables ],
					[ 'COUNT', count( $unknownVariables ) ]
				]
			);

			$issues->add( $issue );
		}

		return $issues;
	}
}
