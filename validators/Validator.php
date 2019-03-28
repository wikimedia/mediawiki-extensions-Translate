<?php
/**
 * Abstract class for Validators.
 *
 * @file
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 */

/**
 * Abstract class built to be extended by validators
 * to perform validations on messages and provide feedback
 * to translators.
 * @since 2019.03
 */
abstract class Validator {

	/**
	 * Is the validator enforced?
	 * @var Boolean
	 */
	protected $enforced;

	/**
	 * Is the validator also acting as an insertable?
	 * @var Boolean
	 */
	protected $insertable;

	/**
	 * Custom parameters
	 * @var mixed
	 */
	protected $params;

	abstract public function validate( $messages, $code, array &$notices, array &$errors );

	public function __construct( array $config ) {
		$this->enforced = $config['enforce'] ?? false;
		$this->insertable = $config['insertable'] ?? false;
		$this->params = $config['params'] ?? null;
	}

	public function isEnforced() {
		return $this->enforced;
	}

	public function isInsertable() {
		return $this->insertable;
	}

	/**
	 * Checks for missing and unknown printf formatting characters in
	 * translations.
	 * @param TMessage[] $messages Iterable list of TMessage objects.
	 * @param string $code Language code
	 * @param array &$validationOutput Array where validation output are appended to.
	 * @param string $pattern Regular expression for matching variables.
	 */
	protected function parameterCheck( $messages, $code, &$validationOutput, $pattern ) {
		foreach ( $messages as $message ) {
			$key = $message->key();
			$definition = $message->definition();
			$translation = $message->translation();

			preg_match_all( $pattern, $definition, $defVars );
			preg_match_all( $pattern, $translation, $transVars );

			// Check for missing variables in the translation
			$subcheck = 'missing';
			$params = self::compareArrays( $defVars[0], $transVars[0] );

			if ( count( $params ) ) {
				$validationOutput[$key][] = [
					[ 'variable', $subcheck, $key, $code ],
					'translate-checks-parameters',
					[ 'PARAMS', $params ],
					[ 'COUNT', count( $params ) ],
				];
			}

			// Check for unknown variables in the translation
			$subcheck = 'unknown';
			$params = self::compareArrays( $transVars[0], $defVars[0] );

			if ( count( $params ) ) {
				$validationOutput[$key][] = [
					[ 'variable', $subcheck, $key, $code ],
					'translate-checks-parameters-unknown',
					[ 'PARAMS', $params ],
					[ 'COUNT', count( $params ) ],
				];
			}
		}
	}

	/**
	 * Compares two arrays and return items that don't exist in the latter.
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
