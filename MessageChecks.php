<?php
if (!defined('MEDIAWIKI')) die();

/**
 * Some checks for common mistakes in translations.
 *
 * @author Niklas Laxström
 * @copyright Copyright © 2008 Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */
class MessageChecks {

	// Fastest first
	static $checksForType = array(
		'mediawiki' => array(
			array( __CLASS__, 'checkPlural' ),
			array( __CLASS__, 'checkParameters' ),
			array( __CLASS__, 'checkBalance' ),
			array( __CLASS__, 'checkLinks' ),
			array( __CLASS__, 'checkXHTML' ),
		),
		'freecol' => array(
			array( __CLASS__, 'checkFreeColMissingVars' ),
			array( __CLASS__, 'checkFreeColExtraVars' ),
		),
	);

	/**
	 * Entry point which runs all checks.
	 *
	 * @param $message Instance of TMessage.
	 * @return Array of warning messages, html-format.
	 */
	public static function doChecks( TMessage $message, $type ) {
		if ( $message->translation === null) return false;
		if ( !isset(self::$checksForType[$type])) return false;
		$warnings = array();

		foreach ( self::$checksForType[$type] as $check ) {
			$warning = '';
			if ( call_user_func( $check, $message, &$warning ) ) {
				$warnings[] = $warning;
			}
		}

		return $warnings;
	}

	public static function doFastChecks( TMessage $message, $type ) {
		if ( $message->translation === null) return false;
		if ( !isset(self::$checksForType[$type])) return false;

		foreach ( self::$checksForType[$type] as $check ) {
			if ( call_user_func( $check, $message ) ) return true;
		}

		return false;
	}

	/**
	 * Checks if the translation uses all variables $[1-9] that the definition
	 * uses.
	 *
	 * @param $message Instance of TMessage.
	 * @return Array of unused parameters.
	 */
	protected static function checkParameters( TMessage $message, &$desc = null ) {
		$variables = array( '\$1', '\$2', '\$3', '\$4', '\$5', '\$6', '\$7', '\$8', '\$9' );

		$missing = array();
		$definition = $message->definition;
		$translation= $message->translation;
		if ( strpos( $definition, '$' ) === false ) return false;

		for ( $i = 1; $i < 10; $i++ ) {
			$pattern = '/\$' . $i . '/s';
			if ( preg_match( $pattern, $definition ) && !preg_match( $pattern, $translation ) ) {
				$missing[] = '$' . $i;
			}
		}

		if ( $count = count($missing) ) {
			global $wgLang;
			$desc = array( 'translate-checks-parameters',
				implode( ', ', $missing ),
				$wgLang->formatNum($count) );
			return true;
		}

		return false;
	}

	/**
	 * Checks if the translation has even number of opening and closing
	 * parentheses. {, [ and ( are checked.
	 *
	 * @param $message Instance of TMessage.
	 * @return Array of unbalanced paranthesis pairs with difference of opening
	 * and closing count as value.
	 */
	protected static function checkBalance( TMessage $message, &$desc = null ) {
		$translation = preg_replace( '/[^{}[\]()]/u', '', $message->translation );
		$counts = array( '{' => 0, '}' => 0, '[' => 0, ']' => 0, '(' => 0, ')' => 0 );

		$i = 0;
		$len = strlen($translation);
		while ( $i < $len ) {
			$char = $translation[$i];
			isset($counts[$char]) ? $counts[$char]++ : var_dump( $char );
			$i++;
		}

		$balance = array();
		if ( $counts['['] !== $counts[']'] ) $balance[] = '[]: ' . ($counts['['] - $counts[']']);
		if ( $counts['{'] !== $counts['}'] ) $balance[] = '{}: ' . ($counts['{'] - $counts['}']);
		if ( $counts['('] !== $counts[')'] ) $balance[] = '(): ' . ($counts['('] - $counts[')']);

		if ( $count = count($balance) ) {
			global $wgLang;
			$desc = array( 'translate-checks-balance',
				implode( ', ', $balance ),
				$wgLang->formatNum($count) );
			return true;
		}

		return false;
	}

	/**
	 * Checks if the translation uses links that are discouraged. Valid links are
	 * those that link to Special: or {{ns:special}}: or project pages trough
	 * MediaWiki messages like {{MediaWiki:helppage-url}}:. Also links in the
	 * definition are allowed.
	 *
	 * @param $message Instance of TMessage.
	 * @return Array of problematic links.
	 */
	protected static function checkLinks( TMessage $message, &$desc = null ) {
		$translation = $message->translation;
		if ( strpos( $translation, '[[' ) === false ) return false;

		$matches = array();
		$links = array();
		$tc = Title::legalChars() . '#%{}';
		preg_match_all( "/\[\[([{$tc}]+)(?:\\|(.+?))?]]/sDu", $translation, $matches);
		for ($i = 0; $i < count($matches[0]); $i++ ) {
			if ( preg_match( '/({{ns:)?special(}})?:.*/sDui', $matches[1][$i] ) ) continue;
			if ( preg_match( '/{{mediawiki:.*}}/sDui', $matches[1][$i] ) ) continue;
			if ( preg_match( '/user([ _]talk)?:.*/sDui', $matches[1][$i] ) ) continue;
			if ( preg_match( '/:?\$[1-9]/sDu', $matches[1][$i] ) ) continue;

			$links[] = "[[{$matches[1][$i]}|{$matches[2][$i]}]]";
		}

		if ( $count = count($links) ) {
			global $wgLang;
			$desc = array( 'translate-checks-links',
				implode( ', ', $links ),
				$wgLang->formatNum($count) );
			return true;
		}

		return false;
	}

	/**
	 * Checks if the <br /> and <hr /> tags are using the correct syntax.
	 *
	 * @param $message Instance of TMessage.
	 * @return Array of tags in invalid syntax with correction suggestions as
	 * value.
	 */
	protected static function checkXHTML( TMessage $message, &$desc = null ) {
		$translation = $message->translation;
		if ( strpos( $translation, '<' ) === false ) return false;

		$tags = array(
			'~<hr *(\\\\)?>~suDi' => '<hr />', // Wrong syntax
			'~<br *(\\\\)?>~suDi' => '<br />',
			'~<hr/>~suDi' => '<hr />', // Wrong syntax
			'~<br/>~suDi' => '<br />',
			'~<(HR|Hr|hR) />~su' => '<hr />', // Case
			'~<(BR|Br|bR) />~su' => '<br />',
		);

		$wrongTags = array();
		foreach ( $tags as $wrong => $correct ) {
			$matches = array();
			preg_match_all( $wrong, $translation, $matches, PREG_PATTERN_ORDER);
			foreach ( $matches[0] as $wrongMatch ) {
				$wrongTags[$wrongMatch] = "$wrongMatch → $correct";
			}
		}

		if ( $count = count($wrongTags) ) {
			global $wgLang;
			$desc = array( 'translate-checks-xhtml',
				implode( ', ', $wrongTags ),
				$wgLang->formatNum($count) );
			return true;
		}

		return false;
	}

	/**
	 * Checks if the translation doesn't use plural while the definition has one.
	 *
	 * @param $message Instance of TMessage.
	 * @return True if plural magic word is missing.
	 */
	protected static function checkPlural( TMessage $message, &$desc = null ) {
		$definition = $message->definition;
		$translation = $message->translation;
		if ( stripos( $definition, '{{plural:' ) !== false &&
			stripos( $translation, '{{plural:' ) === false ) {
			$desc = array( 'translate-checks-plural' );
			return true;
		} else {
			return false;
		}
	}


	protected static function checkFreeColMissingVars( TMessage $message, &$desc = null ) {
		if ( !preg_match_all( '/%[^%]%/U', $message->definition, $defVars ) ) {
			return false;
		}
		preg_match_all( '/%[^%]%/U', $message->translation, $transVars );

		$missing = self::compareArrays( $defVars[0], $transVars[0] );

		if ( $count = count($missing) ) {
			global $wgLang;
			$desc = array( 'translate-checks-parameters',
				implode( ', ', $missing ),
				$wgLang->formatNum($count) );
			var_dump( $desc );
			return true;
		} else {
			return false;
		}
	}


	protected static function checkFreeColExtraVars( TMessage $message, &$desc = null ) {
		if ( !preg_match_all( '/%[^%]%/U', $message->definition, $defVars ) ) {
			return false;
		}
		preg_match_all( '/%[^%]%/U', $message->translation, $transVars );

		$missing = self::compareArrays( $transVars[0], $defVars[0] );

		if ( $count = count($missing) ) {
			global $wgLang;
			$desc = array( 'translate-checks-parameters-unknown',
				implode( ', ', $missing ),
				$wgLang->formatNum($count) );
			var_dump( $desc );
			return true;
		} else {
			return false;
		}
	}

	protected static function compareArrays( $defs, $trans ) {
		$missing = array();
		foreach ( $defs as $defVar ) {
			if ( !in_array($defVar, $trans) ) {
				$missing[] = $defVar;
			}
		}
		return $missing;
	}

}
