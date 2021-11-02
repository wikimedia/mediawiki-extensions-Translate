<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageProcessing;

use MWException;
use TranslateUtils;

/**
 * Flattens message arrays for further processing. Supports parsing CLDR
 * plural messages and converting them into MediaWiki's {{PLURAL}} syntax
 * in a single message.
 *
 * @author Niklas Laxström
 * @author Erik Moeller
 * @license GPL-2.0-or-later
 * @since 2016.01
 */
class ArrayFlattener {
	/** @var string */
	protected $sep;
	/** @var bool */
	protected $parseCLDRPlurals;
	// For CLDR pluralization rules
	protected static $pluralWords = [
		'zero' => 1,
		'one' => 1,
		'many' => 1,
		'few' => 1,
		'other' => 1,
		'two' => 1
	];

	public function __construct( string $sep = '.', bool $parseCLDRPlurals = false ) {
		$this->sep = $sep;
		$this->parseCLDRPlurals = $parseCLDRPlurals;
	}

	/**
	 * Flattens multidimensional array.
	 * @param array $unflat Array of messages
	 */
	public function flatten( array $unflat ): array {
		$flat = [];

		foreach ( $unflat as $key => $value ) {
			if ( !is_array( $value ) ) {
				$flat[$key] = $value;
				continue;
			}

			$plurals = false;
			if ( $this->parseCLDRPlurals ) {
				$plurals = $this->flattenCLDRPlurals( $value );
			}

			if ( $this->parseCLDRPlurals && $plurals ) {
				$flat[$key] = $plurals;
			} else {
				$temp = [];
				foreach ( $value as $subKey => $subValue ) {
					$newKey = "$key{$this->sep}$subKey";
					$temp[$newKey] = $subValue;
				}
				$flat += $this->flatten( $temp );
			}

			// Can as well keep only one copy around.
			unset( $unflat[$key] );
		}

		return $flat;
	}

	/**
	 * Flattens arrays that contain CLDR plural keywords into single values using
	 * MediaWiki's plural syntax.
	 * @throws MWException
	 * @return bool|string
	 */
	public function flattenCLDRPlurals( array $messages ) {
		$hasNonPluralKeys = false;
		$pluralKeys = [];
		foreach ( $messages as $key => $value ) {
			if ( is_array( $value ) ) {
				// Plurals can only happen in the lowest level of the structure
				return false;
			}

			// Check if we find any reserved plural keyword
			if ( isset( self::$pluralWords[$key] ) ) {
				$pluralKeys[] = $key;
			} else {
				$hasNonPluralKeys = true;
			}
		}

		// No plural keys at all, we can skip
		if ( !$pluralKeys ) {
			return false;
		}

		// Mixed plural keys with other keys, should not happen
		if ( $hasNonPluralKeys ) {
			 // Allow `other` with other keys, as long it is is only one of the reserved ones
			if ( $pluralKeys === [ 'other' ] ) {
				return false;
			}

			$keys = implode( ', ', array_keys( $messages ) );
			throw new MWException( "Reserved plural keywords mixed with other keys: $keys." );
		}

		$pls = '{{PLURAL';
		foreach ( $messages as $key => $value ) {
			if ( $key === 'other' ) {
				continue;
			}

			$pls .= "|$key=$value";
		}

		// Put the "other" alternative last, without other= prefix.
		$other = isset( $messages['other'] ) ? '|' . $messages['other'] : '';
		$pls .= "$other}}";

		return $pls;
	}

	/**
	 * Performs the reverse operation of flatten.
	 * @param array $flat Array of messages
	 */
	public function unflatten( array $flat ): array {
		$unflat = [];

		if ( $this->parseCLDRPlurals ) {
			$unflattenedPlurals = [];
			foreach ( $flat as $key => $value ) {
				$plurals = false;
				if ( !is_array( $value ) ) {
					$plurals = $this->unflattenCLDRPlurals( $key, $value );
				}
				if ( is_array( $plurals ) ) {
					$unflattenedPlurals += $plurals;
				} else {
					$unflattenedPlurals[$key] = $value;
				}
			}
			$flat = $unflattenedPlurals;
		}

		foreach ( $flat as $key => $value ) {
			$path = explode( $this->sep, $key );
			if ( count( $path ) === 1 ) {
				$unflat[$key] = $value;
				continue;
			}

			$pointer = &$unflat;
			do {
				// Extract the level and make sure it exists.
				$level = array_shift( $path );
				if ( !isset( $pointer[$level] ) ) {
					$pointer[$level] = [];
				}

				// Update the pointer to the new reference.
				$tmpPointer = &$pointer[$level];
				unset( $pointer );
				$pointer = &$tmpPointer;
				unset( $tmpPointer );

				// If next level is the last, add it into the array.
				if ( count( $path ) === 1 ) {
					$lastKey = array_shift( $path );
					$pointer[$lastKey] = $value;
				}
			} while ( count( $path ) );
		}

		return $unflat;
	}

	/**
	 * Converts the plural syntax to array of CLDR style plurals
	 * @return bool|array
	 */
	public function unflattenCLDRPlurals( string $key, string $message ) {
		// Quick escape.
		if ( strpos( $message, '{{PLURAL' ) === false ) {
			return false;
		}

		/*
		 * Replace all variables with placeholders. Possible source of bugs
		 * if other characters that given below are used.
		 */
		$regex = '/\{[a-z_-]+}/i';
		$placeholders = [];
		$match = [];

		while ( preg_match( $regex, $message, $match ) ) {
			$uniqkey = TranslateUtils::getPlaceholder();
			$placeholders[$uniqkey] = $match[0];
			$search = preg_quote( $match[0], '~' );
			$message = preg_replace( "~$search~", $uniqkey, $message );
		}

		// Then replace (possible multiple) plural instances into placeholders.
		$regex = '~\{\{PLURAL\|(.*?)}}~s';
		$matches = [];
		$match = [];

		while ( preg_match( $regex, $message, $match ) ) {
			$uniqkey = TranslateUtils::getPlaceholder();
			$matches[$uniqkey] = $match;
			$message = preg_replace( $regex, $uniqkey, $message, 1 );
		}

		// No plurals, should not happen.
		if ( !count( $matches ) ) {
			return false;
		}

		// The final array of alternative plurals forms.
		$alts = [];

		/*
		 * Then loop trough each plural block and replacing the placeholders
		 * to construct the alternatives. Produces invalid output if there is
		 * multiple plural bocks which don't have the same set of keys.
		 */
		$pluralChoice = implode( '|', array_keys( self::$pluralWords ) );
		$regex = "~($pluralChoice)\s*=\s*(.*)~s";
		foreach ( $matches as $ph => $plu ) {
			$forms = explode( '|', $plu[1] );

			foreach ( $forms as $form ) {
				$match = [];
				if ( preg_match( $regex, $form, $match ) ) {
					$formWord = "$key{$this->sep}{$match[1]}";
					$value = $match[2];
				} else {
					$formWord = "$key{$this->sep}other";
					$value = $form;
				}

				if ( !isset( $alts[$formWord] ) ) {
					$alts[$formWord] = $message;
				}

				$string = $alts[$formWord];
				$alts[$formWord] = str_replace( $ph, $value, $string );
			}
		}

		// Replace other variables.
		foreach ( $alts as &$value ) {
			$value = str_replace( array_keys( $placeholders ), array_values( $placeholders ), $value );
		}

		if ( !isset( $alts["$key{$this->sep}other"] ) ) {
			// Ensure other form is always present, even if missing from the translation
			$alts["$key{$this->sep}other"] = end( $alts );
		}

		return $alts;
	}

	/** Compares two strings for equal content, taking PLURAL expansion into account. */
	public function compareContent( string $a, string $b ): bool {
		if ( !$this->parseCLDRPlurals ) {
			return $a === $b;
		}

		$a2 = $this->unflattenCLDRPlurals( 'prefix', $a );
		$b2 = $this->unflattenCLDRPlurals( 'prefix', $b );

		// Fall back to regular comparison if parsing fails.
		if ( $a2 === false || $b2 === false ) {
			return $a === $b;
		}

		// Require key-value pairs to match, but ignore order and types (all should be strings).
		return $a2 == $b2;
	}
}
