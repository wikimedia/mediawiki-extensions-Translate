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

namespace MediaWiki\Extension\Translate\Validation;

use Exception;
use FormatJson;
use InvalidArgumentException;
use MediaWiki\Extension\Translate\Services;
use MediaWiki\Extension\Translate\TranslatorInterface\Insertable\InsertablesSuggester;
use PHPVariableLoader;
use RuntimeException;
use TMessage;

/**
 * Message validator is used to run validators to find common mistakes so that
 * translators can fix them quickly. This is an improvement over the old Message
 * Checker framework because it allows maintainers to enforce a validation so
 * that translations that do not pass validation are not saved.
 *
 * To create your own validator, implement the MessageValidator interface.
 *
 * There are two types of notices - error and warning.
 *
 * @link https://www.mediawiki.org/wiki/Help:Extension:Translate/Group_configuration#VALIDATORS
 * @link https://www.mediawiki.org/wiki/Help:Extension:Translate/Validators
 *
 * @ingroup MessageValidator
 * @since 2019.06
 */
class ValidationRunner {
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

	/** Normalise validator keys. */
	protected static function foldValue( string $value ): string {
		return str_replace( ' ', '_', strtolower( $value ) );
	}

	/**
	 * Set the validators for this group.
	 *
	 * Removes the existing validators.
	 *
	 * @param array $validatorConfigs List of Validator configurations
	 * @see addValidator()
	 */
	public function setValidators( array $validatorConfigs ): void {
		$this->validators = [];
		foreach ( $validatorConfigs as $config ) {
			$this->addValidator( $config );
		}
	}

	/** Add a validator for this group. */
	public function addValidator( array $validatorConfig ): void {
		$validatorId = $validatorConfig['id'] ?? null;
		$className = $validatorConfig['class'] ?? null;

		if ( $validatorId !== null ) {
			$validator = ValidatorFactory::get(
				$validatorId,
				$validatorConfig['params'] ?? null
			);
		} elseif ( $className !== null ) {
			$validator = ValidatorFactory::loadInstance(
				$className,
				$validatorConfig['params'] ?? null
			);
		} else {
			throw new InvalidArgumentException(
				'Validator configuration does not specify the \'class\' or \'id\'.'
			);
		}

		$isInsertable = $validatorConfig['insertable'] ?? false;
		if ( $isInsertable && !$validator instanceof InsertablesSuggester ) {
			$actualClassName = get_class( $validator );
			throw new InvalidArgumentException(
				"Insertable validator $actualClassName does not implement InsertablesSuggester interface."
			);
		}

		$this->validators[] = [
			'instance' => $validator,
			'insertable' => $isInsertable,
			'enforce' => $validatorConfig['enforce'] ?? false,
			'include' => $validatorConfig['keymatch'] ?? $validatorConfig['include'] ?? false,
			'exclude' => $validatorConfig['exclude'] ?? false
		];
	}

	/**
	 * Return the currently set validators for this group.
	 *
	 * @return MessageValidator[] List of validators
	 */
	public function getValidators(): array {
		return array_map(
			static function ( $validator ) {
				return $validator['instance'];
			},
			$this->validators
		);
	}

	/**
	 * Return currently set validators that are insertable.
	 *
	 * @return MessageValidator[] List of insertable
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
	 * Validate a translation of a message.
	 *
	 * Returns a ValidationResult that contains methods to print the issues.
	 */
	public function validateMessage(
		TMessage $message,
		string $code,
		bool $ignoreWarnings = false
	): ValidationResult {
		$errors = new ValidationIssues();
		$warnings = new ValidationIssues();

		foreach ( $this->validators as $validator ) {
			$this->runValidation(
				$validator,
				$message,
				$code,
				$errors,
				$warnings,
				$ignoreWarnings
			);
		}

		$errors = $this->filterValidations( $message->key(), $errors, $code );
		$warnings = $this->filterValidations( $message->key(), $warnings, $code );

		return new ValidationResult( $errors, $warnings );
	}

	/** Validate a message, and return as soon as any validation fails. */
	public function quickValidate(
		TMessage $message,
		string $code,
		bool $ignoreWarnings = false
	): ValidationResult {
		$errors = new ValidationIssues();
		$warnings = new ValidationIssues();

		foreach ( $this->validators as $validator ) {
			$this->runValidation(
				$validator,
				$message,
				$code,
				$errors,
				$warnings,
				$ignoreWarnings
			);

			$errors = $this->filterValidations( $message->key(), $errors, $code );
			$warnings = $this->filterValidations( $message->key(), $warnings, $code );

			if ( $warnings->hasIssues() || $errors->hasIssues() ) {
				break;
			}
		}

		return new ValidationResult( $errors, $warnings );
	}

	/** @internal Should only be used by tests and inside this class. */
	public static function reloadIgnorePatterns(): void {
		$validationExclusionFile = Services::getInstance()->getConfigHelper()->getValidationExclusionFile();

		if ( $validationExclusionFile === false ) {
			self::$ignorePatterns = [];
			return;
		}

		$list = PHPVariableLoader::loadVariableFromPHPFile(
			$validationExclusionFile,
			'validationExclusionList'
		);
		$keys = [ 'group', 'check', 'subcheck', 'code', 'message' ];

		if ( $list && !is_array( $list ) ) {
			throw new InvalidArgumentException(
				"validationExclusionList defined in $validationExclusionFile must be an array"
			);
		}

		foreach ( $list as $key => $pattern ) {
			foreach ( $keys as $checkKey ) {
				if ( !isset( $pattern[$checkKey] ) ) {
					$list[$key][$checkKey] = '#';
				} elseif ( is_array( $pattern[$checkKey] ) ) {
					$list[$key][$checkKey] =
						array_map(
							[ self::class, 'foldValue' ],
							$pattern[$checkKey]
						);
				} else {
					$list[$key][$checkKey] = self::foldValue( $pattern[$checkKey] );
				}
			}
		}

		self::$ignorePatterns = $list;
	}

	/** Filter validations based on a ignore list. */
	private function filterValidations(
		string $messageKey,
		ValidationIssues $issues,
		string $targetLanguage
	): ValidationIssues {
		$filteredIssues = new ValidationIssues();

		foreach ( $issues as $issue ) {
			foreach ( self::$ignorePatterns as $pattern ) {
				if ( $this->shouldIgnore( $messageKey, $issue, $this->groupId, $targetLanguage, $pattern ) ) {
					continue 2;
				}
			}
			$filteredIssues->add( $issue );
		}

		return $filteredIssues;
	}

	private function shouldIgnore(
		string $messageKey,
		ValidationIssue $issue,
		string $messageGroupId,
		string $targetLanguage,
		array $pattern
	): bool {
		return $this->matchesIgnorePattern( $pattern['group'], $messageGroupId )
			&& $this->matchesIgnorePattern( $pattern['check'], $issue->type() )
			&& $this->matchesIgnorePattern( $pattern['subcheck'], $issue->subType() )
			&& $this->matchesIgnorePattern( $pattern['message'], $messageKey )
			&& $this->matchesIgnorePattern( $pattern['code'], $targetLanguage );
	}

	/**
	 * Match validation information against a ignore pattern.
	 *
	 * @param string|string[] $pattern
	 * @param string $value The actual value in the validation produced by the validator
	 * @return bool True if the pattern matches the value.
	 */
	private function matchesIgnorePattern( $pattern, string $value ): bool {
		if ( $pattern === '#' ) {
			return true;
		} elseif ( is_array( $pattern ) ) {
			return in_array( strtolower( $value ), $pattern, true );
		} else {
			return strtolower( $value ) === $pattern;
		}
	}

	/**
	 * Check if key matches validator's key patterns.
	 * Only relevant if the 'include' or 'exclude' option is specified in the validator.
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
					"Received: " . gettype( $match ) . ". match value: " . FormatJson::encode( $match )
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
		/** @var MessageValidator $validator */
		$validator = $validatorData['instance'];

		$definition = $message->definition();
		if ( $definition === null ) {
			// This should NOT happen, but add a check since it seems to be happening
			// See: https://phabricator.wikimedia.org/T255669
			return;
		}

		try {
			$includedKeys = $validatorData['include'];
			if ( $includedKeys !== false && !$this->doesKeyMatch( $message->key(), $includedKeys ) ) {
				return;
			}

			$excludedKeys = $validatorData['exclude'];
			if ( $excludedKeys !== false && $this->doesKeyMatch( $message->key(), $excludedKeys ) ) {
				return;
			}

			if ( $validatorData['enforce'] === true ) {
				$errors->merge( $validator->getIssues( $message, $targetLanguage ) );
			} elseif ( !$ignoreWarnings ) {
				$warnings->merge( $validator->getIssues( $message, $targetLanguage ) );
			}
			// else: caller does not want warnings, skip running the validator
		} catch ( Exception $e ) {
			throw new RuntimeException(
				'An error occurred while validating message: ' . $message->key() . '; group: ' .
				$this->groupId . "; validator: " . get_class( $validator ) . "\n. Exception: $e"
			);
		}
	}
}
