<?php
/**
 * @file
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 */

namespace MediaWiki\Extensions\Translate\MessageValidator;

/**
 * A factory class used to instantiate instances of pre-provided Validators
 * @since 2019.04
 */
class ValidatorFactory {

	const VALIDATOR_NS = 'MediaWiki\\Extensions\\Translate\\MessageValidator\\Validators\\';

	/**
	 * @param array $validators
	 */
	protected static $validators = [
		'BraceBalance' => self::VALIDATOR_NS . 'BraceBalanceValidator',
		'InsertableRegex' => self::VALIDATOR_NS . 'InsertableRegexValidator',
		'InsertableRubyVariable' => self::VALIDATOR_NS . 'InsertableRubyVariableValidator',
		'MediaWikiMisc' => self::VALIDATOR_NS . 'MediaWikiMiscValidator',
		'MediaWikiPlural' => self::VALIDATOR_NS . 'MediaWikiPluralValidator'
	];

	/**
	 * Returns a validator instance based on the Id specified
	 * @param string $idOrClass Id or name of the validator class
	 * @param mixed|null $params
	 * @return Validator
	 */
	public static function get( $idOrClass, $params = null ) {
		if ( isset( self::$validators[ $idOrClass ] ) ) {
			return new self::$validators[ $idOrClass ]( $params );
		}

		if ( class_exists( $idOrClass ) ) {
			// Note that this checks for the class in the global namespace and not
			// the current namespace.
			$validator = new $idOrClass( $params );

			if ( !( $validator instanceof Validator ) ) {
				throw new \RuntimeException(
					"Validator '$idOrClass' does not implement the Validator interface."
				);
			}

			return $validator;
		}

		throw new \RuntimeException( "Could not find validator with id / class - '$idOrClass'. " );
	}

	/**
	 * Adds / Updates available list of validators
	 * @param string $id Id of the validator
	 * @param string $validator Validator class name
	 * @param string $ns
	 * @return void
	 */
	public static function set( $id, $validator, $ns = '\\' ) {
		if ( !class_exists( $ns . $validator ) ) {
			throw new RuntimeException( 'Could not find validator class - ' . $ns . $validator );
		}

		self::$validators[ $id ] = $ns . $validator;
	}
}
