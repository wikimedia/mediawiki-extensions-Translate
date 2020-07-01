<?php
declare( strict_types = 1 );

namespace MediaWiki\Extensions\Translate\MessageValidator;

use InvalidArgumentException;
use MediaWiki\Extensions\Translate\MessageValidator\Validators\BraceBalanceValidator;
use MediaWiki\Extensions\Translate\MessageValidator\Validators\EscapeCharacterValidator;
use MediaWiki\Extensions\Translate\MessageValidator\Validators\GettextNewlineValidator;
use MediaWiki\Extensions\Translate\MessageValidator\Validators\GettextPluralValidator;
use MediaWiki\Extensions\Translate\MessageValidator\Validators\InsertableRegexValidator;
use MediaWiki\Extensions\Translate\MessageValidator\Validators\InsertableRubyVariableValidator;
use MediaWiki\Extensions\Translate\MessageValidator\Validators\IosVariableValidator;
use MediaWiki\Extensions\Translate\MessageValidator\Validators\MatchSetValidator;
use MediaWiki\Extensions\Translate\MessageValidator\Validators\MediaWikiPageNameValidator;
use MediaWiki\Extensions\Translate\MessageValidator\Validators\MediaWikiPluralValidator;
use MediaWiki\Extensions\Translate\MessageValidator\Validators\MediaWikiTimeListValidator;
use MediaWiki\Extensions\Translate\MessageValidator\Validators\NewlineValidator;
use MediaWiki\Extensions\Translate\MessageValidator\Validators\NumericalParameterValidator;
use MediaWiki\Extensions\Translate\MessageValidator\Validators\PrintfValidator;
use MediaWiki\Extensions\Translate\MessageValidator\Validators\PythonInterpolationValidator;
use MediaWiki\Extensions\Translate\MessageValidator\Validators\SmartFormatPluralValidator;
use MediaWiki\Extensions\Translate\MessageValidator\Validators\UnicodePluralValidator;
use MediaWiki\Extensions\Translate\MessageValidator\Validators\WikiLinkValidator;
use MediaWiki\Extensions\Translate\MessageValidator\Validators\WikiParameterValidator;
use MediaWiki\Extensions\Translate\Validation\LegacyValidatorAdapter;
use MediaWiki\Extensions\Translate\Validation\MessageValidator;
use RuntimeException;

/**
 * A factory class used to instantiate instances of pre-provided Validators
 *
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 * @since 2019.06
 */
class ValidatorFactory {
	/** @var string[] */
	protected static $validators = [
		'BraceBalance' => BraceBalanceValidator::class,
		'EscapeCharacter' => EscapeCharacterValidator::class,
		'GettextNewline' => GettextNewlineValidator::class,
		'GettextPlural' => GettextPluralValidator::class,
		'InsertableRegex' => InsertableRegexValidator::class,
		'InsertableRubyVariable' => InsertableRubyVariableValidator::class,
		'IosVariable' => IosVariableValidator::class,
		'MatchSet' => MatchSetValidator::class,
		// TODO: Remove this BC alias
		'MediaWikiMisc' => MediaWikiTimeListValidator::class,
		'MediaWikiPageName' => MediaWikiPageNameValidator::class,
		'MediaWikiPlural' => MediaWikiPluralValidator::class,
		'MediaWikiTimeList' => MediaWikiTimeListValidator::class,
		'Newline' => NewlineValidator::class,
		'NumericalParameter' => NumericalParameterValidator::class,
		'Printf' => PrintfValidator::class,
		'PythonInterpolation' => PythonInterpolationValidator::class,
		'SmartFormatPlural' => SmartFormatPluralValidator::class,
		'UnicodePlural' => UnicodePluralValidator::class,
		'WikiLink' => WikiLinkValidator::class,
		'WikiParameter' => WikiParameterValidator::class
	];

	/**
	 * Returns a validator instance based on the id specified
	 *
	 * @param string $id Id of the pre-defined validator class
	 * @param mixed|null $params
	 * @throws InvalidArgumentException
	 * @return MessageValidator
	 */
	public static function get( $id, $params = null ) {
		if ( !isset( self::$validators[ $id ] ) ) {
			throw new InvalidArgumentException( "Could not find validator with id - '$id'. " );
		}

		return self::loadInstance( self::$validators[ $id ], $params );
	}

	/**
	 * Takes a Validator class name, and returns an instance of that class.
	 *
	 * @param string $class Custom validator class name
	 * @param mixed|null $params
	 * @throws InvalidArgumentException
	 * @return MessageValidator
	 */
	public static function loadInstance( $class, $params = null ): MessageValidator {
		if ( !class_exists( $class ) ) {
			throw new InvalidArgumentException( "Could not find validator class - '$class'. " );
		}

		$validator = new $class( $params );

		if ( $validator instanceof Validator ) {
			return new LegacyValidatorAdapter( $validator );
		}

		return $validator;
	}

	/**
	 * Adds / Updates available list of validators
	 * @param string $id Id of the validator
	 * @param string $validator Validator class name
	 * @param string $ns
	 */
	public static function set( $id, $validator, $ns = '\\' ) {
		if ( !class_exists( $ns . $validator ) ) {
			throw new RuntimeException( 'Could not find validator class - ' . $ns . $validator );
		}

		self::$validators[ $id ] = $ns . $validator;
	}
}
