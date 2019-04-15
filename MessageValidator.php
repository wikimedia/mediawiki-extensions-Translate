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
 * There are two types of notices now - error and warning.
 *
 * The format for notices,
 * <pre>
 * $notices[$key][] = array(
 *    # check idenfitication
 *    array( 'printf', $subcheck, $key, $code ),
 *    # check notice message
 *    'translate-checks-parameters-unknown',
 *    # optional special param list, formatted later with Language::commaList()
 *    array( 'PARAMS', $params ),
 *    # optional number of params, formatted later with Language::formatNum()
 *    array( 'COUNT', count( $params ) ),
 *    'Any other parameters to the message',
 * </pre>
 *
 * TODO: MV - Update this later on.
 * @link https://www.mediawiki.org/wiki/Help:Extension:Translate/Group_configuration#CHECKER
 *
 * @ingroup MessageValidator
 * @since 2019.04
 */
class MessageValidator {

	/**
	 * Contains list of validators
	 *
	 * @var array
	 */
	protected $validators = [];

	protected $group;

	private static $globalBlacklist;

	/**
	 * Constructs a suitable validator for given message group.
	 * @param MessageGroup $group
	 */
	public function __construct( MessageGroup $group ) {
		global $wgTranslateCheckBlacklist;

		if ( $wgTranslateCheckBlacklist === false ) {
			self::$globalBlacklist = [];
		} elseif ( self::$globalBlacklist === null ) {
			$file = $wgTranslateCheckBlacklist;
			$list = PHPVariableLoader::loadVariableFromPHPFile( $file, 'checkBlacklist' );
			$keys = [ 'group', 'check', 'subcheck', 'code', 'message' ];

			foreach ( $list as $key => $pattern ) {
				foreach ( $keys as $checkKey ) {
					if ( !isset( $pattern[$checkKey] ) ) {
						$list[$key][$checkKey] = '#';
					} elseif ( is_array( $pattern[$checkKey] ) ) {
						$list[$key][$checkKey] =
							array_map( [ $this, 'foldValue' ], $pattern[$checkKey] );
					} else {
						$list[$key][$checkKey] = $this->foldValue( $pattern[$checkKey] );
					}
				}
			}

			self::$globalBlacklist = $list;
		}

		$this->group = $group;
	}

	/**
	 * Normalises validator keys.
	 * @param string $value validator key
	 * @return string Normalised validator key
	 */
	protected function foldValue( $value ) {
		return str_replace( ' ', '_', strtolower( $value ) );
	}

	/**
	 * Set the validators for this group.
	 * @see addValidators()
	 * @param array $validatorConfigs List of Validator configurations
	 */
	public function setValidators( array $validatorConfigs ) {
		foreach ( $validatorConfigs as $config ) {
			$this->addValidators( $config );
		}
	}

	/**
	 * Adds a validator for this group.
	 * @param array $validatorConfig
	 */
	public function addValidators( array $validatorConfig ) {
		$validatorId = $validatorConfig['id'] ?? null;
		$className = $validatorConfig['class'] ?? null;

		if ( !isset( $validatorId ) && !isset( $className ) ) {
			throw new \RuntimeException(
				'Validator configuration does not specify the \'class\' or \'id\'.'
			);
		}

		$validator = ValidatorFactory::get( $validatorId ?? $className,
			$validatorConfig['params'] ?? null );

		$isInsertable = $validatorConfig['insertable'] ?? false;
		if ( $isInsertable && !$validator instanceof \InsertablesSuggester ) {
			throw new \RuntimeException(
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
	 * Returns the currently set validatons for this group.
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
	 * @param bool $ignoreWarnings Ignore warnings, and only run enforced validations
	 * @return ValidationResult
	 */
	public function validateMessage( TMessage $message, $code, $ignoreWarnings = false ) {
		$warnings = [];
		$errors = [];
		$messages = [ $message ];

		foreach ( $this->validators as $validator ) {
			if ( $validator['enforce'] === true ) {
				$validator['instance']->validate( $messages, $code, $errors );
			} else {
				if ( $ignoreWarnings ) {
					continue;
				}
				$validator['instance']->validate( $messages, $code, $warnings );
			}
		}

		$warnings = $this->filterValidations( $warnings );
		$errors = $this->filterValidations( $errors );

		if ( count( $warnings ) > 0 ) {
			$warnings = $warnings[$message->key()];
			$warnings = $this->fixMessageParams( $warnings );
		}

		if ( count( $errors ) > 0 ) {
			$errors = $errors[$message->key()];
			$errors = $this->fixMessageParams( $errors );
		}

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
			if ( $validator['enforce'] === true ) {
				$validator['instance']->validate( $messages, $code, $errors );
			} else {
				// let's not bother running "warning" validators if warnings are ignored.
				if ( $ignoreWarnings ) {
					continue;
				}
				$validator['instance']->validate( $messages, $code, $warnings );
			}

			if ( count( $errors ) > 0 ) {
				$errors = $errors[$message->key()];
				$errors = $this->fixMessageParams( $errors );
				break;
			}

			if ( count( $warnings ) > 0 ) {
				$warnings = $warnings[$message->key()];
				$warnings = $this->fixMessageParams( $warnings );
				break;
			}
		}

		$validationResult = new ValidationResult( $errors, $warnings );
		return $validationResult;
	}

	/**
	 * Filters validations defined in check-blacklist.php.
	 * @param array $validationsArray List of validations produces by validateMessage().
	 * @return array List of filtered validations.
	 */
	protected function filterValidations( array $validationsArray ) {
		$groupId = $this->group->getId();

		// There is an array of messages...
		foreach ( $validationsArray as $mkey => $validations ) {
			// ... each which has an array of validations.
			foreach ( $validations as $vkey => $validation ) {
				$validator = array_shift( $validation );
				// Check if the key is blacklisted...
				foreach ( self::$globalBlacklist as $pattern ) {
					if ( !$this->match( $pattern['group'], $groupId ) ) {
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

					// If all of the aboce match, filter the validator
					unset( $validationsArray[$mkey][$vkey] );
				}
			}
		}

		return $validationsArray;
	}

	/**
	 * Matches validation information against blacklist pattern.
	 * @param string|array $pattern
	 * @param string $value The actual value in the validation produced by the validator
	 * @return bool True of the pattern matches the value.
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
	 * Converts the special params to something nice. Currently useless, but
	 * useful if in the future blacklist can work with parameter level too.
	 * @param array $notices List of warnings / errors
	 * @throws MWException
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
						throw new MWException( "Unknown type $type" );
					}
				}
			}
			$validators[$vkey] = $message;
		}

		return $validators;
	}
}
