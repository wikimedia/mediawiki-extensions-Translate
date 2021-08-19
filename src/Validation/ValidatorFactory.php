<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Validation;

use InvalidArgumentException;
use MediaWiki\Extension\Translate\Validation\Validators\BraceBalanceValidator;
use MediaWiki\Extension\Translate\Validation\Validators\EscapeCharacterValidator;
use MediaWiki\Extension\Translate\Validation\Validators\GettextNewlineValidator;
use MediaWiki\Extension\Translate\Validation\Validators\GettextPluralValidator;
use MediaWiki\Extension\Translate\Validation\Validators\InsertableRegexValidator;
use MediaWiki\Extension\Translate\Validation\Validators\InsertableRubyVariableValidator;
use MediaWiki\Extension\Translate\Validation\Validators\IosVariableValidator;
use MediaWiki\Extension\Translate\Validation\Validators\MatchSetValidator;
use MediaWiki\Extension\Translate\Validation\Validators\MediaWikiLinkValidator;
use MediaWiki\Extension\Translate\Validation\Validators\MediaWikiPageNameValidator;
use MediaWiki\Extension\Translate\Validation\Validators\MediaWikiParameterValidator;
use MediaWiki\Extension\Translate\Validation\Validators\MediaWikiPluralValidator;
use MediaWiki\Extension\Translate\Validation\Validators\MediaWikiTimeListValidator;
use MediaWiki\Extension\Translate\Validation\Validators\NewlineValidator;
use MediaWiki\Extension\Translate\Validation\Validators\NotEmptyValidator;
use MediaWiki\Extension\Translate\Validation\Validators\NumericalParameterValidator;
use MediaWiki\Extension\Translate\Validation\Validators\PrintfValidator;
use MediaWiki\Extension\Translate\Validation\Validators\PythonInterpolationValidator;
use MediaWiki\Extension\Translate\Validation\Validators\ReplacementValidator;
use MediaWiki\Extension\Translate\Validation\Validators\SmartFormatPluralValidator;
use MediaWiki\Extension\Translate\Validation\Validators\UnicodePluralValidator;
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
		'MediaWikiLink' => MediaWikiLinkValidator::class,
		'MediaWikiPageName' => MediaWikiPageNameValidator::class,
		'MediaWikiParameter' => MediaWikiParameterValidator::class,
		'MediaWikiPlural' => MediaWikiPluralValidator::class,
		'MediaWikiTimeList' => MediaWikiTimeListValidator::class,
		'Newline' => NewlineValidator::class,
		'NotEmpty' => NotEmptyValidator::class,
		'NumericalParameter' => NumericalParameterValidator::class,
		'Printf' => PrintfValidator::class,
		'PythonInterpolation' => PythonInterpolationValidator::class,
		'Replacement' => ReplacementValidator::class,
		'SmartFormatPlural' => SmartFormatPluralValidator::class,
		'UnicodePlural' => UnicodePluralValidator::class,
		// BC: remove when unused
		'WikiLink' => MediaWikiLinkValidator::class,
		// BC: remove when unused
		'WikiParameter' => MediaWikiParameterValidator::class,
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

		return new $class( $params );
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
