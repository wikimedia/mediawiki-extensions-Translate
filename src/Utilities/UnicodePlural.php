<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Utilities;

use RuntimeException;

/**
 * @license GPL-2.0-or-later
 * @since 2019.09
 */
class UnicodePlural {
	/** @var string[] List of supported Unicode CLDR plural keywords */
	public const KEYWORDS = [ 'zero', 'one', 'two', 'few', 'many', 'other' ];
	private const PRE = '{{PLURAL|';
	private const POST = '}}';

	/**
	 * Returns CLDR plural rule for given language.
	 *
	 * @param string $code Language tag in MediaWiki internal format.
	 * @return ?string[] Null, if no plural rule found
	 */
	public static function getPluralKeywords( string $code ): ?array {
		$filePath = __DIR__ . '/../../data/plural-cldr.json';
		$ruleData = json_decode( file_get_contents( $filePath ), true );

		$ruleSet = $ruleData[ 'supplemental' ][ 'plurals-type-cardinal' ][ $code ] ?? null;
		if ( $ruleSet === null ) {
			return null;
		}

		$keywords = [];
		foreach ( array_keys( $ruleSet ) as $name ) {
			$keywords[] = str_replace( 'pluralRule-count-', '', $name );
		}

		return $keywords;
	}

	/** Quick way to check if the text contains plural syntax. */
	public static function hasPlural( string $text ): bool {
		return str_contains( $text, self::PRE );
	}

	/**
	 * Format plural forms map as single string suitable for translation.
	 *
	 * This does not check validity of forms. Use ::convertFormListToFormMap for that.
	 * @param string[] $forms
	 */
	public static function flattenMap( array $forms ): string {
		$list = [];
		foreach ( $forms as $keyword => $value ) {
			$list[] = [ $keyword, $value ];
		}

		return self::flattenList( $list );
	}

	/**
	 * Format plural forms list as single string.
	 *
	 * This does not check validity of forms.
	 * @param array[] $formList [ keyword, form ] pairs.
	 */
	public static function flattenList( array $formList ): string {
		$formatted = [];
		foreach ( $formList as [ $keyword, $value ] ) {
			$formatted[] = self::formatForm( $keyword, $value );
		}

		return self::PRE . implode( '|', $formatted ) . self::POST;
	}

	private static function formatForm( string $keyword, string $value ): string {
		$prefix = $keyword === 'other' ? '' : "$keyword=";
		return $prefix . $value;
	}

	/**
	 * Format translation with plural forms as array of forms.
	 *
	 * Reverse of flatten. Do note that A may be != flatten( unflatten( A ) ) because
	 * translators can place part of the text outside the plural markup or use multiple
	 * instances of the markup.
	 *
	 * @param string $text
	 * @param string[] $expectedKeywords
	 * @return string[]
	 */
	public static function unflatten( string $text, array $expectedKeywords ): array {
		[ $template, $instanceMap ] = self::parsePluralForms( $text );
		return self::expandTemplate( $template, $instanceMap, $expectedKeywords );
	}

	/**
	 * Parses plural markup into a structure form.
	 *
	 * @return array [ string $template, array $instanceMap ]
	 */
	public static function parsePluralForms( string $text ): array {
		$m = [];
		$pre = preg_quote( self::PRE, '/' );
		$post = preg_quote( self::POST, '/' );

		$ok = preg_match_all( "/$pre(.*)$post/Us", $text, $m );
		if ( $ok === false ) {
			throw new RuntimeException( "Plural regular expression failed for text: $text" );
		}

		$template = $text;
		$instanceMap = [];

		foreach ( $m[0] as $instanceIndex => $instanceText ) {
			$ph = Utilities::getPlaceholder();

			// Using preg_replace instead of str_replace because of the limit parameter
			$pattern = '/' . preg_quote( $instanceText, '/' ) . '/';
			$template = preg_replace( $pattern, $ph, $template, 1 );

			$instanceForms = [];
			foreach ( explode( '|', $m[ 1 ][ $instanceIndex ] ) as $form ) {
				$m2 = [];
				$ok = preg_match( '~\s*([a-z]+)\s*=(.+)~s', $form, $m2 );
				$keyword = $ok ? $m2[ 1 ] : 'other';
				$value = $ok ? trim( $m2[ 2 ] ) : $form;
				$instanceForms[] = [ $keyword, $value ];
			}

			$instanceMap[$ph] = $instanceForms;
		}

		return [ $template, $instanceMap ];
	}

	/**
	 * Gives fully expanded forms given a template and parsed plural markup instances.
	 *
	 * @param string $template
	 * @param array $instanceMap
	 * @param string[] $expectedKeywords
	 * @return string[]
	 */
	public static function expandTemplate( string $template, array $instanceMap, array $expectedKeywords ): array {
		$formArray = [];

		// Convert from list of forms to map of forms for easier processing
		foreach ( $instanceMap as $ph => $list ) {
			$instanceMap[ $ph ] = self::convertFormListToFormMap( $list, $expectedKeywords );
		}

		foreach ( $expectedKeywords as $keyword ) {
			// Start with the whole string
			$form = $template;

			// Loop over each plural markup instance and replace it with the plural form belonging
			// to the current index
			foreach ( $instanceMap as $ph => $instanceFormMap ) {
				// For missing forms, fall back to empty text.
				$replacement = $instanceFormMap[ $keyword ] ?? '';
				$form = str_replace( $ph, $replacement, $form );
			}

			$formArray[ $keyword ] = $form;
		}

		return $formArray;
	}

	public static function convertFormListToFormMap( array $formList, array $expectedKeywords ): array {
		$formMap = [];
		foreach ( $formList as [ $keyword, $value ] ) {
			$formMap[ $keyword ] = $value;
		}

		$sortedFormMap = [];
		foreach ( $expectedKeywords as $keyword ) {
			$sortedFormMap[ $keyword ] = $formMap[ $keyword ] ?? null;
		}

		return $sortedFormMap;
	}
}
