<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Utilities;

use InvalidArgumentException;
use MediaWiki\Extension\Translate\FileFormatSupport\GettextPluralException;

/**
 * @license GPL-2.0-or-later
 * @since 2019.09
 */
class GettextPlural {
	private const PRE = '{{PLURAL:GETTEXT|';
	private const POST = '}}';

	/**
	 * Returns Gettext plural rule for given language.
	 *
	 * @param string $code Language tag in MediaWiki internal format.
	 * @return string Empty string if no plural rule found
	 */
	public static function getPluralRule( string $code ): string {
		global $wgTranslateDocumentationLanguageCode;

		if ( $code === $wgTranslateDocumentationLanguageCode ) {
			return 'nplurals=1; plural=0;';
		}

		$rulefile = __DIR__ . '/../../data/plural-gettext.txt';
		$rules = file_get_contents( $rulefile );
		foreach ( explode( "\n", $rules ) as $line ) {
			if ( trim( $line ) === '' ) {
				continue;
			}
			[ $rulecode, $rule ] = explode( "\t", $line );
			if ( $rulecode === $code ) {
				return $rule;
			}
		}

		return '';
	}

	/**
	 * Returns how many plural forms are expected by a given plural rule.
	 *
	 * @param string $rule Gettext style plural rule.
	 * @throws InvalidArgumentException
	 */
	public static function getPluralCount( string $rule ): int {
		$m = [];
		$ok = preg_match( '/nplurals=([0-9]+).*;/', $rule, $m );
		if ( !$ok ) {
			throw new InvalidArgumentException( "Rule $rule is malformed" );
		}
		return (int)$m[ 1 ];
	}

	/**
	 * Quick way to check if the text contains plural syntax.
	 */
	public static function hasPlural( string $text ): bool {
		return str_contains( $text, self::PRE );
	}

	/**
	 * Format plural forms as single string suitable for translation.
	 *
	 * @param string[] $forms
	 */
	public static function flatten( array $forms ): string {
		return self::PRE . implode( '|', $forms ) . self::POST;
	}

	/**
	 * Format translation with plural forms as array of forms.
	 *
	 * Reverse of flatten. Do note that A may be != flatten( unflatten( A ) ) because
	 * translators can place part of the text outside the plural markup or use multiple
	 * instances of the markup.
	 *
	 * @return string[]
	 */
	public static function unflatten( string $text, int $expectedPluralCount ): array {
		[ $template, $instanceMap ] = self::parsePluralForms( $text );
		return self::expandTemplate( $template, $instanceMap, $expectedPluralCount );
	}

	/**
	 * Replaces problematic markup which can confuse our plural syntax markup with placeholders
	 *
	 * @return array{0:string,1:array} [ string $text, array $map ]
	 */
	private static function armour( string $text ): array {
		// |/| is commonly used in KDE to support inflections. It needs to be escaped
		// to avoid it messing up the plural markup.
		$replacements = [
			'|/|' => Utilities::getPlaceholder(),
		];
		// {0} is a common variable format
		preg_match_all( '/\{\d+\}/', $text, $matches );
		foreach ( $matches[0] as $m ) {
			$replacements[$m] = Utilities::getPlaceholder();
		}

		$text = strtr( $text, $replacements );
		$map = array_flip( $replacements );

		return [ $text, $map ];
	}

	/**
	 * Reverse of armour.
	 *
	 * @param string $text
	 * @param array $map Map returned by armour.
	 */
	private static function unarmour( string $text, array $map ): string {
		return strtr( $text, $map );
	}

	/**
	 * Parses plural markup into a structure form.
	 *
	 * @return array{0:string,1:array} [ string $template, array $instanceMap ]
	 * @throws GettextPluralException
	 */
	public static function parsePluralForms( string $text ): array {
		$m = [];
		$pre = preg_quote( self::PRE, '/' );
		$post = preg_quote( self::POST, '/' );

		[ $armouredText, $armourMap ] = self::armour( $text );

		$ok = preg_match_all( "/$pre(.*)$post/Us", $armouredText, $m );
		if ( $ok === false ) {
			throw new GettextPluralException( "Plural regular expression failed for text: $text" );
		}

		$template = $armouredText;
		$instanceMap = [];

		foreach ( $m[0] as $instanceIndex => $instanceText ) {
			$ph = Utilities::getPlaceholder();

			// Using preg_replace instead of str_replace because of the limit parameter
			$pattern = '/' . preg_quote( $instanceText, '/' ) . '/';
			$template = preg_replace( $pattern, $ph, $template, 1 );

			$instanceForms = explode( '|', $m[ 1 ][ $instanceIndex ] );
			foreach ( $instanceForms as $i => $v ) {
				$instanceForms[ $i ] = self::unarmour( $v, $armourMap );
			}

			$instanceMap[$ph] = $instanceForms;
		}

		$template = self::unarmour( $template, $armourMap );
		return [ $template, $instanceMap ];
	}

	/**
	 * Gives fully expanded forms given a template and parsed plural markup instances.
	 *
	 * @return string[]
	 */
	public static function expandTemplate( string $template, array $instanceMap, int $expectedPluralCount ): array {
		$formArray = [];
		for ( $formIndex = 0; $formIndex < $expectedPluralCount; $formIndex++ ) {
			// Start with the whole string
			$form = $template;

			// Loop over each plural markup instance and replace it with the plural form belonging
			// to the current index
			foreach ( $instanceMap as $ph => $instanceForms ) {
				// For missing forms, fall back to empty text.
				// Extra forms are excluded because $formIndex < $expectedPluralCount
				$replacement = $instanceForms[ $formIndex ] ?? '';
				$form = str_replace( $ph, $replacement, $form );
			}

			$formArray[ $formIndex ] = $form;
		}

		return $formArray;
	}
}
