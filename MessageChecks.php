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
	/**
	 * Message prefix.
	 */
	const MSG = 'translate-checks-';

	/**
	 * Entry point which runs all checks.
	 *
	 * @param $message Instance of TMessage.
	 * @return Array of warning messages, html-format.
	 */
	public static function doChecks( TMessage $message ) {
		$warnings = array();
		if ( count($values = self::checkParameters( $message )) ) {
			$warnings[] = wfMsgExt( self::MSG . 'parameters', 'parse', implode( ', ', $values ) );
		}

		if ( count($values = self::checkBalance( $message )) ) {
			$warnings[] = wfMsgExt( self::MSG . 'balance', 'parse', implode( ', ', $values ) );
		}

		if ( count($values = self::checkLinks( $message )) ) {
			$warnings[] = wfMsgExt( self::MSG . 'links', 'parse', implode( ', ', $values ) );
		}

		if ( count($values = self::checkXHTML( $message )) ) {
			$warnings[] = wfMsgExt( self::MSG . 'xhtml', 'parse', implode( ', ', $values ) );
		}

		if ( self::checkPlural( $message ) ) {
			$warnings[] = wfMsgExt( self::MSG . 'plural', 'parse' );
		}

		return $warnings;
	}

	/**
	 * Checks if the translation uses all variables $[1-9] that the definition
	 * uses.
	 *
	 * @param $message Instance of TMessage.
	 * @return Array of unused parameters.
	 */
	protected static function checkParameters( TMessage $message ) {
		$variables = array( '\$1', '\$2', '\$3', '\$4', '\$5', '\$6', '\$7', '\$8', '\$9' );

		$missing = array();
		$definition = $message->definition;
		$translation= $message->translation;
		for ( $i = 1; $i < 10; $i++ ) {
			$pattern = '/\$' . $i . '/s';
			if ( preg_match( $pattern, $definition ) && !preg_match( $pattern, $translation ) ) {
				$missing[] = '$' . $i;
			}
		}
		return $missing;
	}

	/**
	 * Checks if the translation has even number of opening and closing
	 * parentheses. {, [ and ( are checked.
	 *
	 * @param $message Instance of TMessage.
	 * @return Array of unbalanced paranthesis pairs with difference of opening
	 * and closing count as value.
	 */
	protected static function checkBalance( TMessage $message ) {
		$translation = preg_replace( '/[^{}[\]()]/u', '', $message->translation );
		$counts = array( '{' => 0, '}' => 0, '[' => 0, ']' => 0, '(' => 0, ')' => 0 );
		foreach ( preg_split('//u', $translation) as $char ) {
			if ( !$char ) continue;
			isset($counts[$char]) ? $counts[$char]++ : var_dump( $char );
		}

		$balance = array();
		if ( $counts['['] !== $counts[']'] ) $balance[] = '[]: ' . ($counts['['] - $counts[']']);
		if ( $counts['{'] !== $counts['}'] ) $balance[] = '{}: ' . ($counts['{'] - $counts['}']);
		if ( $counts['('] !== $counts[')'] ) $balance[] = '(): ' . ($counts['('] - $counts[')']);
		return $balance;
	}

	/**
	 * Checks if the translation uses links that are discouraged. Valid links are
	 * those that link to Special: or {{ns:special}}: or project pages trough
	 * MediaWiki messages like {{MediaWiki:helppage-url}}:.
	 *
	 * @param $message Instance of TMessage.
	 * @return Array of problematic links.
	 */
	protected static function checkLinks( TMessage $message ) {
		$translation = $message->translation;
		$matches = array();
		$links = array();
		$tc = Title::legalChars() . '#%{}';
		preg_match_all( "/\[\[([{$tc}]+)(?:\\|(.+?))?]]/sDu", $translation, $matches);
		for ($i = 0; $i < count($matches[0]); $i++ ) {
			if ( preg_match( '/({{ns:)?special(}})?:.*/sDui', $matches[1][$i] ) ) continue;
			if ( preg_match( '/{{mediawiki:.*}}/sDui', $matches[1][$i] ) ) continue;
			
			$links[] = "[[{$matches[1][$i]}|{$matches[2][$i]}]]";
		}
		return $links;
	}

	/**
	 * Checks if the <br /> and <hr /> tags are using the correct syntax.
	 *
	 * @param $message Instance of TMessage.
	 * @return Array of tags in invalid syntax with correction suggestions as
	 * value.
	 */
	protected static function checkXHTML( TMessage $message ) {
		$translation = $message->translation;
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
				$wrongTags[$wrongMatch] = htmlspecialchars( "$wrongMatch → $correct" );
			}
		}
		return $wrongTags;
	}

	/**
	 * Checks if the translation doesn't use plural while the definition has one.
	 *
	 * @param $message Instance of TMessage.
	 * @return True if plural magic word is missing.
	 */
	protected static function checkPlural( TMessage $message ) {
		$definition = $message->definition;
		$translation = $message->translation;
		if ( stripos( $definition, '{{plural:' ) !== false &&
			stripos( $translation, '{{plural:' ) === false ) {
			return true;
    } else {
			return false;
		}
	}

}