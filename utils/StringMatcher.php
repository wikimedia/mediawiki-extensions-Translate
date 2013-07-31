<?php
/**
 * Code for mangling message keys to avoid conflicting keys.
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2008-2013, Niklas Laxström
 * @license GPL-2.0+
 */

/**
 * Interface that key-mangling classes must implement.
 *
 * The operations have to be reversible so that
 * x equals unMangle( mangle( x ) ).
 */
interface StringMangler {
	/// @todo Does this really need to be in the interface???
	public static function EmptyMatcher();

	/**
	 * General way to pass configuration to the mangler.
	 * @param array $configuration
	 */
	public function setConf( $configuration );

	/**
	 * Match strings against a pattern.
	 * If string matches, mangle() should mangle the key.
	 * @param string $string Message key.
	 * @return \bool
	 */
	public function match( $string );

	/**
	 * Mangles a list of message keys.
	 * @param string|string[] $data Unmangled message keys.
	 * @return string|string[] Mangled message keys.
	 */
	public function mangle( $data );

	/**
	 * Reverses the operation mangle() did.
	 * @param string|string[] $data Mangled message keys.
	 * @return string|string[] Umangled message keys.
	 */
	public function unMangle( $data );
}

/**
 * The versatile default implementation of StringMangler interface.
 * It supports exact matches and patterns with any-wildcard (*).
 * All matching strings are prefixed with the same prefix.
 */
class StringMatcher implements StringMangler {
	/// Prefix for mangled message keys
	protected $sPrefix = '';
	/// Exact message keys
	protected $aExact = array();
	/// Patterns of type foo*
	protected $aPrefix = array();
	/// Patterns that contain wildcard anywhere else than in the end
	protected $aRegex = array();

	/**
	 * Alias for making NO-OP string mangler.
	 * @return StringMatcher
	 */
	public static function EmptyMatcher() {
		return new StringMatcher;
	}

	/**
	 * Constructor, see EmptyMatcher();
	 */
	public function __construct( $prefix = '', $patterns = array() ) {
		$this->sPrefix = $prefix;
		$this->init( $patterns );
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
	 * They are split into exact keys, prefix matches and pattern matches to
	 * speed up matching process.
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

	/**
	 * @param string $string
	 * @return bool
	 */
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

	/**
	 * @param string $data
	 * @return string|string[]
	 * @throws MWException
	 */
	public function mangle( $data ) {
		if ( is_array( $data ) ) {
			return $this->mangleArray( $data );
		} elseif ( is_string( $data ) ) {
			return $this->mangleString( $data );
		} elseif ( $data === null ) {
			return $data;
		} else {
			throw new MWException( __METHOD__ . ": Unsupported datatype" );
		}
	}

	public function unMangle( $data ) {
		if ( is_array( $data ) ) {
			return $this->mangleArray( $data, true );
		} elseif ( is_string( $data ) ) {
			return $this->mangleString( $data, true );
		} elseif ( $data === null ) {
			return $data;
		} else {
			throw new MWException( __METHOD__ . ": Unsupported datatype" );
		}
	}

	/**
	 * Mangles or unmangles single string.
	 * @param string $string Message key.
	 * @param bool $reverse Direction of mangling or unmangling.
	 * @return string
	 */
	protected function mangleString( $string, $reverse = false ) {
		if ( $reverse ) {
			return $this->unMangleString( $string );
		}

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

	/**
	 * Unmangles the message key by removing the prefix it it exists.
	 * @param string $string Message key.
	 * @return string Unmangled message key.
	 */
	protected function unMangleString( $string ) {
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

	/**
	 * Mangles or unmangles list of message keys.
	 * @param string[] $array Message keys.
	 * @param bool $reverse Direction of mangling or unmangling.
	 * @return string[] (Un)mangled message keys.
	 */
	protected function mangleArray( array $array, $reverse = false ) {
		$temp = array();

		if ( isset( $array[0] ) ) {
			foreach ( $array as $key => &$value ) {
				$value = $this->mangleString( $value, $reverse );
				$temp[$key] = $value; // Assign a reference
			}
		} else {
			foreach ( $array as $key => &$value ) {
				$key = $this->mangleString( $key, $reverse );
				$temp[$key] = $value; // Assign a reference
			}
		}

		return $temp;
	}
}
