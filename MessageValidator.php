<?php
/**
 * Message validation framework.
 *
 * @file
 * @defgroup MessageValidator Message Validators
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 */

/**
 * Message validator is used to run validations to find common mistakes so that
 * translators can fix them quickly.
 *
 * To implement your own validator,
 *
 * TODO: MV
 * @ingroup MessageValidator
 * @since 2019.03
 */
class MessageValidator {

	/**
	 * Contains list of validations
	 *
	 * @var array
	 */
	protected $validations = [];

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
	 * Set the validations for this group. 
	 * @see addValidators()
	 * @param array $validatorConfigs List of Validator configurations
	 */
	public function setValidators( array $validatorConfigs ) {
		foreach ( $validatorConfigs as $config ) {
			$this->addValidators( $config );
		}
	}

	/**
	 * Adds a validation for this group.
	 * @param array $validatorConfig
	 */
	public function addValidators( array $validatorConfig ) {
		$class = $validatorConfig[ 'class' ];
		if ( empty( $class ) ) {
			throw new \RuntimeException( "The class property is mandatory for validators." );
		}
		$validator = new $class( $validatorConfig );
		if ( !$validator instanceof Validator ) {
			throw new \RuntimeException( "Expect validator $class to implement Validator interface." );
		}
		$this->validations[] = $validator;
	}

	/**
	 * Returns the currently set validatons for this group.
	 * @return Validator[] List of currently set validators.
	 */
	public function getValidations() {
		return $this->validations;
	}

	/**
	 * Validates one message, returns array of warnings / errors that can be
	 * passed to OutputPage::addWikiMsg or similar.
	 *
	 * @param TMessage $message
	 * @param string $code Language code
	 * @return array
	 */
	public function validateMessage( TMessage $message, $code ) {
		$warnings = [];
		$errors = [];
		$messages = [ $message ];

		foreach ( $this->validations as $validator ) {
			$validator->validate( $messages, $code, $warnings, $errors );
		}

		$warnings = $this->filterValidations( $warnings );
		$errors = $this->filterValidations( $errors );

		if ( count( $warnings ) > 0 ) {
			$warnings = $warnings[$message->key()];
			$warnings = $this->fixMessageParams( $warnings );
		} else {
			$warnings = [];
		}

		if ( count( $errors ) > 0 ) {
			$errors = $errors[$message->key()];
			$errors = $this->fixMessageParams( $errors );
		} else {
			$errors = [];
		}

		return [
			'errors' => $errors,
			'warnings' => $warnings
		];
	}

	/**
	 * Validates one message, returns false if any validation fails, else true.
	 * @param TMessage $message
	 * @param string $code Language code
	 * @return bool True if there is no problem, false otherwise.
	 */
	public function isMessageValid( TMessage $message, $code ) {
		$warningsArray = [];
		$messages = [ $message ];

		foreach ( $this->validations as $validator ) {
			call_user_func_array( $validator, [ $messages, $code, &$warningsArray ] );
			if ( count( $warningsArray ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Filters validations defined in check-blacklist.php.
	 * @param array $validationsArray List of warnings produces by validateMessage().
	 * @return array List of filtered warnings.
	 */
	protected function filterValidations( array $validationsArray ) {
		$groupId = $this->group->getId();

		// There is an array of messages...
		foreach ( $validationsArray as $mkey => $validations ) {
			// ... each which has an array of warnings.
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
	 * @return array List of warning messages with parameters.
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

	/**
	 * Compares two arrays return items that don't exist in the latter.
	 * @param array $defs
	 * @param array $trans
	 * @return array Items of $defs that are not in $trans.
	 */
	protected static function compareArrays( array $defs, array $trans ) {
		$missing = [];

		foreach ( $defs as $defVar ) {
			if ( !in_array( $defVar, $trans ) ) {
				$missing[] = $defVar;
			}
		}

		return $missing;
	}

}
