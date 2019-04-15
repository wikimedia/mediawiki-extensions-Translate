<?php
/**
 * Message validation framework.
 *
 * @file
 * @defgroup MessageValidator Message Validators
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 */

use MediaWiki\Extensions\Translate\MessageValidator\Validator;
use MediaWiki\Extensions\Translate\MessageValidator\ValidationResult;
use MediaWiki\Extensions\Translate\MessageValidator\ValidatorFactory;

/**
 * Message validator is used to run validators to find common mistakes so that
 * translators can fix them quickly. This is an improvement over the old Message
 * Checker framework because it allows maintainers to enforce a validation so
 * that translations that do not pass validation are not saved.
 *
 * To create your own validator, implement the following interface,
 * @see MediaWiki\Extensions\Translate\MessageValidator\Validator
 *
 * In addition you can use the following Trait to reuse some pre-existing methods,
 * @see MediaWiki\Extensions\Translate\MessageValidator\ValidatorHelper
 *
 * There are two types of notices - error and warning.
 *
 * The format for notices,
 * <pre>
 * $notices[$key][] = [
 *    # check idenfitication
 *    [ 'printf', $subcheck, $key, $code ],
 *    # check notice message
 *    'translate-checks-parameters-unknown',
 *    # optional special param list, formatted later with Language::commaList()
 *    [ 'PARAMS', $params ],
 *    # optional number of params, formatted later with Language::formatNum()
 *    [ 'COUNT', count( $params ) ] ],
 *    'Any other parameters to the message',
 * </pre>
 *
 * @link https://www.mediawiki.org/wiki/Help:Extension:Translate/Group_configuration#VALIDATORS
 * @link https://www.mediawiki.org/wiki/Help:Extension:Translate/Validators
 *
 * @ingroup MessageValidator
 * @since 2019.05
 */
class MessageValidator {

	/**
	 * Contains list of validators
	 *
	 * @var array
	 */
	protected $validators = [];

	protected $groupId;

	private static $globalBlacklist;

	/**
	 * Constructs a suitable validator for given message group.
	 * @param string $groupId
	 */
	public function __construct( $groupId ) {
		global $wgTranslateCheckBlacklist;

		if ( $wgTranslateCheckBlacklist === false ) {
			self::$globalBlacklist = [];
		} elseif ( self::$globalBlacklist === null ) {
			// TODO: Review if this logic belongs in this class.
			self::reloadCheckBlacklist();
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
		if ( $isInsertable && !$validator instanceof \InsertablesSuggester ) {
			throw new InvalidArgumentException(
				"Insertable validator does not implement InsertablesSuggester interface."
			);
		}

		$this->validators[] = [
			'instance' => $validator,
			'insertable' => $isInsertable,
			'enforce' => $validatorConfig['enforce'] ?? false
		];
	}

	/**
	 * Returns the currently set validators for this group.
	 * @return Validator[] List of currently set validators.
	 */
	public function getValidators() {
		return array_map( function ( $validator ) {
			return $validator['instance'];
		}, $this->validators );
	}

	/**
	 * Returns currently set validators that are insertable.
	 *
	 * @return Validator[] List of currently set insertable validators.
	 */
	public function getInsertableValidators() {
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
	 *
	 * @param TMessage $message
	 * @param string $code Language code
	 * @param bool $ignoreWarnings Ignore warnings, and only run enforced validators
	 * @return ValidationResult
	 */
	public function validateMessage( TMessage $message, $code, $ignoreWarnings = false ) {
		$warnings = [];
		$errors = [];
		$messages = [ $message ];

		foreach ( $this->validators as $validator ) {
			$this->runValidation( $validator, $messages, $code, $errors, $warnings, $ignoreWarnings );
		}

		$warnings = $this->normalizeNotices( $message->key(), $warnings );
		$errors = $this->normalizeNotices( $message->key(), $errors );

		$validationResult = new ValidationResult( $errors, $warnings );
		return $validationResult;
	}

	/**
	 * Validates a message, and returns as soon as any validation fails.
	 * @param TMessage $message
	 * @param string $code Language code
	 * @param bool $ignoreWarnings Should warnings be ignored?
	 * @return ValidationResult
	 */
	public function quickValidate( TMessage $message, $code, $ignoreWarnings = false ) {
		$warnings = [];
		$errors = [];
		$messages = [ $message ];

		foreach ( $this->validators as $validator ) {
			$this->runValidation( $validator, $messages, $code, $errors, $warnings, $ignoreWarnings );

			$warnings = $this->normalizeNotices( $message->key(), $warnings );
			$errors = $this->normalizeNotices( $message->key(), $errors );

			if ( $warnings !== [] || $errors !== [] ) {
				break;
			}
		}

		$validationResult = new ValidationResult( $errors, $warnings );
		return $validationResult;
	}

	/**
	 * Updates the blacklist
	 */
	public static function reloadCheckBlacklist() {
		global $wgTranslateCheckBlacklist;
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

		self::$globalBlacklist = $list;
	}

	/**
	 * Filters validations defined in check-blacklist.php.
	 * @param array $validationsArray List of validations failures produced by validateMessage()
	 * or quickValidate().
	 * @return array List of filtered validations.
	 */
	protected function filterValidations( array $validationsArray ) {
		// There is an array of messages...
		foreach ( $validationsArray as $mkey => $validations ) {
			// ... each which has an array of validations.
			foreach ( $validations as $vkey => $validation ) {
				$validator = array_shift( $validation );
				// Check if the key is blacklisted...
				foreach ( self::$globalBlacklist as $pattern ) {
					if ( !$this->match( $pattern['group'], $this->groupId ) ) {
						continue;
					}
					if ( !$this->match( $pattern['check'], $validator[0] ) ) {
						continue;
					}
					if ( !$this->match( $pattern['subcheck'], $validator[1] ) ) {
						continue;
					}
					if ( !$this->match( $pattern['message'], $validator[2] ) ) {
						continue;
					}
					if ( !$this->match( $pattern['code'], $validator[3] ) ) {
						continue;
					}

					// If all of the above match, filter the validator
					unset( $validationsArray[$mkey][$vkey] );
				}

				if ( $validationsArray[$mkey] === [] ) {
					unset( $validationsArray[$mkey] );
				}
			}
		}

		return $validationsArray;
	}

	/**
	 * Matches validation information against blacklist pattern.
	 * @param string|array $pattern
	 * @param string $value The actual value in the validation produced by the validator
	 * @return bool True if the pattern matches the value.
	 */
	protected function match( $pattern, $value ) {
		if ( $pattern === '#' ) {
			return true;
		} elseif ( is_array( $pattern ) ) {
			return in_array( strtolower( $value ), $pattern, true );
		} else {
			return strtolower( $value ) === $pattern;
		}
	}

	/**
	 * Converts the special params to something nice.
	 * @param array $notices List of warnings / errors
	 * @throws InvalidArgumentException
	 * @return array List of validation messages with parameters.
	 */
	protected function fixMessageParams( array $notices ) {
		$lang = RequestContext::getMain()->getLanguage();

		foreach ( $notices as $vkey => $validator ) {
			array_shift( $validator );
			$message = [ array_shift( $validator ) ];

			foreach ( $validator as $param ) {
				if ( !is_array( $param ) ) {
					$message[] = $param;
				} else {
					list( $type, $value ) = $param;
					if ( $type === 'COUNT' ) {
						$message[] = $lang->formatNum( $value );
					} elseif ( $type === 'PARAMS' ) {
						$message[] = $lang->commaList( $value );
					} else {
						throw new InvalidArgumentException( "Unknown type $type" );
					}
				}
			}
			$validators[$vkey] = $message;
		}

		return $validators;
	}

	/**
	 * Runs the actual validation. Reused by quickValidate() and validateMessage()
	 *
	 * @param array $validator
	 * @param TMessage[] $messages
	 * @param string $code
	 * @param array $errors
	 * @param array $warnings
	 * @param bool $ignoreWarnings
	 */
	private function runValidation( $validator, $messages, $code,
		&$errors, &$warnings, $ignoreWarnings ) {
		if ( $validator['enforce'] === true ) {
			$validator['instance']->validate( $messages, $code, $errors );
		} else {
			// let's not bother running "warning" validators if warnings are ignored.
			if ( $ignoreWarnings ) {
				return;
			}
			$validator['instance']->validate( $messages, $code, $warnings );
		}
	}

	/**
	 * Filters and then normalizes the notices array.
	 *
	 * @param string $messageKey
	 * @param array $notices
	 * @return array
	 */
	private function normalizeNotices( $messageKey, $notices ) {
		$notices = $this->filterValidations( $notices );
		if ( count( $notices ) > 0 ) {
			$notices = $notices[$messageKey];
			$notices = $this->fixMessageParams( $notices );
		}

		return $notices;
	}
}
