<?php
/**
 * Message validation framework.
 *
 * @file
 * @defgroup MessageValidator Message Validators
 * @author Abijeet Patro
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */

use MediaWiki\Extensions\Translate\MessageValidator\ValidationResult;
use MediaWiki\Extensions\Translate\MessageValidator\ValidatorFactory;
use MediaWiki\Extensions\Translate\Validation\MessageValidator as MessageValidatorInterface;
use MediaWiki\Extensions\Translate\Validation\ValidationIssue;
use MediaWiki\Extensions\Translate\Validation\ValidationIssues;

/**
 * Message validator is used to run validators to find common mistakes so that
 * translators can fix them quickly. This is an improvement over the old Message
 * Checker framework because it allows maintainers to enforce a validation so
 * that translations that do not pass validation are not saved.
 *
 * To create your own validator, implement the following interface,
 * @see MediaWiki\Extensions\Translate\Validation\MessageValidator
 *
 * In addition you can use the following Trait to reuse some pre-existing methods,
 * @see MediaWiki\Extensions\Translate\MessageValidator\ValidatorHelper
 *
 * There are two types of notices - error and warning.
 *
 * @link https://www.mediawiki.org/wiki/Help:Extension:Translate/Group_configuration#VALIDATORS
 * @link https://www.mediawiki.org/wiki/Help:Extension:Translate/Validators
 *
 * @ingroup MessageValidator
 * @since 2019.06
 */
class MessageValidator {

	/** @var array List of validator data */
	protected $validators = [];

	/** @var string Message group id */
	protected $groupId;

	/** @var string[][] */
	private static $ignorePatterns;

	public function __construct( string $groupId ) {
		if ( self::$ignorePatterns === null ) {
			// TODO: Review if this logic belongs in this class.
			self::reloadIgnorePatterns();
		}

		$this->groupId = $groupId;
	}

	/**
	 * Normalises validator keys.
	 * @param string $value validator key
	 * @return string Normalised validator key
	 */
	protected static function foldValue( $value ) {
		return str_replace( ' ', '_', strtolower( $value ) );
	}

	/**
	 * Set the validators for this group. Removes the existing validators.
	 * @see addValidator()
	 * @param array $validatorConfigs List of Validator configurations
	 */
	public function setValidators( array $validatorConfigs ) {
		$this->validators = [];
		foreach ( $validatorConfigs as $config ) {
			$this->addValidator( $config );
		}
	}

	/**
	 * Adds a validator for this group.
	 * @param array $validatorConfig
	 */
	public function addValidator( array $validatorConfig ) {
		$validatorId = $validatorConfig['id'] ?? null;
		$className = $validatorConfig['class'] ?? null;

		if ( $validatorId !== null ) {
			$validator = ValidatorFactory::get(
				$validatorId,
				$validatorConfig['params'] ?? null
			);
		} elseif ( $className !== null ) {
			$validator = ValidatorFactory::loadInstance( $className,
				$validatorConfig['params'] ?? null );
		} else {
			throw new InvalidArgumentException(
				'Validator configuration does not specify the \'class\' or \'id\'.'
			);
		}

		$isInsertable = $validatorConfig['insertable'] ?? false;
		if ( $isInsertable && !$validator instanceof InsertablesSuggester ) {
			throw new InvalidArgumentException(
				"Insertable validator does not implement InsertablesSuggester interface."
			);
		}

		$this->validators[] = [
			'instance' => $validator,
			'insertable' => $isInsertable,
			'enforce' => $validatorConfig['enforce'] ?? false,
			'keymatch' => $validatorConfig['keymatch'] ?? false
		];
	}

	/**
	 * Returns the currently set validators for this group.
	 *
	 * @return MessageValidatorInterface[] List of validators
	 */
	public function getValidators(): array {
		return array_map( function ( $validator ) {
			return $validator['instance'];
		}, $this->validators );
	}

	/**
	 * Returns currently set validators that are insertable.
	 *
	 * @return MessageValidatorInterface[] List of insertable
	 * validators
	 */
	public function getInsertableValidators(): array {
		$insertableValidators = [];
		foreach ( $this->validators as $validator ) {
			if ( $validator['insertable'] === true ) {
				$insertableValidators[] = $validator['instance'];
			}
		}

		return $insertableValidators;
	}

	/**
	 * Validates one message, returns array of warnings / errors that can be
	 * passed to OutputPage::addWikiMsg or similar.
	 */
	public function validateMessage(
		TMessage $message, string $code, bool $ignoreWarnings = false
	): ValidationResult {
		$errors = new ValidationIssues();
		$warnings = new ValidationIssues();

		foreach ( $this->validators as $validator ) {
			$this->runValidation( $validator, $message, $code, $errors, $warnings, $ignoreWarnings );
		}

		$errors = $this->filterValidations( $errors, $code );
		$warnings = $this->filterValidations( $warnings, $code );

		return new ValidationResult( $errors, $warnings );
	}

	/** Validates a message, and returns as soon as any validation fails. */
	public function quickValidate(
		TMessage $message, string $code, bool $ignoreWarnings = false
	): ValidationResult {
		$errors = new ValidationIssues();
		$warnings = new ValidationIssues();

		foreach ( $this->validators as $validator ) {
			$this->runValidation( $validator, $message, $code, $errors, $warnings, $ignoreWarnings );

			$errors = $this->filterValidations( $errors, $code );
			$warnings = $this->filterValidations( $warnings, $code );

			if ( $warnings->hasIssues() || $errors->hasIssues() ) {
				break;
			}
		}

		return new ValidationResult( $errors, $warnings );
	}

	/** @internal Should only be used by tests and inside this class. */
	public static function reloadIgnorePatterns(): void {
		global $wgTranslateCheckBlacklist;

		if ( $wgTranslateCheckBlacklist === false ) {
			self::$ignorePatterns = [];
			return;
		}

		$list = PHPVariableLoader::loadVariableFromPHPFile(
			$wgTranslateCheckBlacklist, 'checkBlacklist'
		);
		$keys = [ 'group', 'check', 'subcheck', 'code', 'message' ];

		foreach ( $list as $key => $pattern ) {
			foreach ( $keys as $checkKey ) {
				if ( !isset( $pattern[$checkKey] ) ) {
					$list[$key][$checkKey] = '#';
				} elseif ( is_array( $pattern[$checkKey] ) ) {
					$list[$key][$checkKey] =
						array_map( 'MessageValidator::foldValue', $pattern[$checkKey] );
				} else {
					$list[$key][$checkKey] = self::foldValue( $pattern[$checkKey] );
				}
			}
		}

		self::$ignorePatterns = $list;
	}

	/** Filters validations based on a ignore list. */
	private function filterValidations(
		ValidationIssues $issues,
		string $targetLanguage
	): ValidationIssues {
		$filteredIssues = new ValidationIssues();

		foreach ( $issues as $issue ) {
			foreach ( self::$ignorePatterns as $pattern ) {
				if ( $this->shouldIgnore( $issue, $this->groupId, $targetLanguage, $pattern ) ) {
					continue 2;
				}
			}
			$filteredIssues->add( $issue );
		}

		return $filteredIssues;
	}

	private function shouldIgnore(
		ValidationIssue $issue,
		string $messageGroupId,
		string $targetLanguage,
		array $pattern
	): bool {
		return $this->match( $pattern['group'], $messageGroupId )
			&& $this->match( $pattern['check'], $issue->type() )
			&& $this->match( $pattern['subcheck'], $issue->subType() )
			&& $this->match( $pattern['message'], $issue->messageKey() )
			&& $this->match( $pattern['code'], $targetLanguage );
	}

	/**
	 * Matches validation information against a ignore pattern.
	 *
	 * @param string|array $pattern
	 * @param string $value The actual value in the validation produced by the validator
	 * @return bool True if the pattern matches the value.
	 */
	protected function match( $pattern, string $value ): bool {
		if ( $pattern === '#' ) {
			return true;
		} elseif ( is_array( $pattern ) ) {
			return in_array( strtolower( $value ), $pattern, true );
		} else {
			return strtolower( $value ) === $pattern;
		}
	}

	/**
	 * If the 'keymatch' option is specified in the validator, checks and ensures that the
	 * key matches.
	 *
	 * @param string $key
	 * @param string[] $keyMatches
	 * @return bool True if the key matches one of the matchers, false otherwise.
	 */
	protected function doesKeyMatch( string $key, array $keyMatches ): bool {
		$normalizedKey = lcfirst( $key );
		foreach ( $keyMatches as $match ) {
			if ( is_string( $match ) ) {
				if ( lcfirst( $match ) === $normalizedKey ) {
					return true;
				}
				continue;
			}

			// The value is neither a string nor an array, should never happen but still handle it.
			if ( !is_array( $match ) ) {
				throw new InvalidArgumentException(
					"Invalid key matcher configuration passed. Expected type: array or string. " .
					"Recieved: " . gettype( $match ) . ". match value: " . FormatJson::encode( $match )
				);
			}

			$matcherType = $match['type'];
			$pattern = $match['pattern'];

			// If regex matches, or wildcard matches return true, else continue processing.
			if (
				( $matcherType === 'regex' && preg_match( $pattern, $normalizedKey ) === 1 ) ||
				( $matcherType === 'wildcard' && fnmatch( $pattern, $normalizedKey ) )
			) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Run the validator to produce warnings and errors.
	 *
	 * May also skip validation depending on validator configuration and $ignoreWarnings.
	 */
	private function runValidation(
		array $validatorData,
		TMessage $message,
		string $targetLanguage,
		ValidationIssues $errors,
		ValidationIssues $warnings,
		bool $ignoreWarnings
	): void {
		// Check if key match has been specified, and then check if the key matches it.
		/** @var MessageValidatorInterface $validator */
		$validator = $validatorData['instance'];
		try {
			$keyMatches = $validatorData['keymatch'];
			if ( $keyMatches !== false && !$this->doesKeyMatch( $message->key(), $keyMatches ) ) {
				return;
			}

			if ( $validatorData['enforce'] === true ) {
				$errors->merge( $validator->getIssues( $message, $targetLanguage ) );
			} elseif ( !$ignoreWarnings ) {
				$warnings->merge( $validator->getIssues( $message, $targetLanguage ) );
			}
			// else: caller does not want warnings, skip running the validator
		} catch ( Exception $e ) {
			throw new \RuntimeException(
				'An error occurred while validating message: ' . $message->key() . '; group: ' .
				$this->groupId . "; validator: " . get_class( $validator ) . "\n. Exception: $e"
			);
		}
	}
}
