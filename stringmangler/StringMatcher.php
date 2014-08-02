<?php
/**
 * Default StringMangler implementation.
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0+
 */

/**
 * The versatile default implementation of StringMangler interface.
 * It supports exact matches and patterns with any-wildcard (*).
 * All matching strings are prefixed with the same prefix.
 */
class StringMatcher implements StringMangler {
	/**
	 * @var string Prefix for mangled message keys
	 */
	protected $sPrefix = '';

	/**
	 * @var string[] Exact message keys
	 */
	protected $aExact = array();

	/**
	 * @var string[] Patterns of type foo*
	 */
	protected $aPrefix = array();

	/**
	 * @var string[] Patterns that contain wildcard anywhere else than in the end.
	 */
	protected $aRegex = array();

	public function __construct( $prefix = '', $patterns = array() ) {
		$this->setConf( array(
			'prefix' => $prefix,
			'patterns' => $patterns,
		) );
	}

	protected static function getValidKeyChars() {
		static $valid = null;
		if ( $valid === null ) {
			global $wgLegalTitleChars;
			$valid = strtr( $wgLegalTitleChars, array(
				'=' => '', // equals sign, which is itself usef for escaping
				'&' => '', // ampersand, for entities
				'%' => '', // percent sign, which is used in URL encoding
			) );
		}

		return $valid;
	}

	public function setConf( $conf ) {
		$this->sPrefix = $conf['prefix'];
		$this->init( $conf['patterns'] );
	}

	/**
	 * Preprocesses the patterns.
	 *
	 * They are split into exact keys, prefix matches and pattern matches to
	 * speed up matching process.
	 *
	 * @param string[] $strings Key patterns.
	 */
	protected function init( array $strings ) {
		foreach ( $strings as $string ) {
			$pos = strpos( $string, '*' );
			if ( $pos === false ) {
				$this->aExact[] = $string;
			} elseif ( $pos + 1 === strlen( $string ) ) {
				$prefix = substr( $string, 0, -1 );
				$this->aPrefix[$prefix] = strlen( $prefix );
			} else {
				$string = str_replace( '\\*', '.+', preg_quote( $string ) );
				$this->aRegex[] = "/^$string$/";
			}
		}
	}

	public function match( $string ) {
		if ( in_array( $string, $this->aExact ) ) {
			return true;
		}

		foreach ( $this->aPrefix as $prefix => $len ) {
			if ( strncmp( $string, $prefix, $len ) === 0 ) {
				return true;
			}
		}

		foreach ( $this->aRegex as $regex ) {
			if ( preg_match( $regex, $string ) ) {
				return true;
			}
		}

		return false;
	}

	public function mangle( $string ) {
		if ( $this->match( $string ) ) {
			$string = $this->sPrefix . $string;
		}

		// Apply a "quoted-printable"-like escaping
		$valid = self::getValidKeyChars();
		$escapedString = preg_replace_callback( "/[^$valid]/",
			function ( $match ) {
				return '=' . sprintf( '%02X', ord( $match[0] ) );
			},
			$string
		);

		return $escapedString;
	}

	public function unmangle( $string ) {
		// Unescape the "quoted-printable"-like escaping,
		// which is applied in mangleString.
		$unescapedString = preg_replace_callback( "/=([A-F0-9]{2})/",
			function ( $match ) {
				return chr( hexdec( $match[0] ) );
			},
			$string
		);

		if ( strncmp( $unescapedString, $this->sPrefix, strlen( $this->sPrefix ) ) === 0 ) {
			return substr( $unescapedString, strlen( $this->sPrefix ) );
		} else {
			return $unescapedString;
		}
	}
}
