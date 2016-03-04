<?php
/**
 * Support for JSON message file format.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0+
 * @since 2016.01
 */

class ArrayFlattener {
	protected $sep;

	public function __construct( $sep = '.' ) {
		$this->sep = $sep;
	}

	/**
	 * Flattens multidimensional array.
	 *
	 * @param array $unflat It's an array.
	 * @return array
	 */
	public function flatten( array $unflat ) {
		$flat = array();

		foreach ( $unflat as $key => $value ) {
			if ( !is_array( $value ) ) {
				$flat[$key] = $value;
				continue;
			}

			// Placeholder for special plural processing

			$temp = array();
			foreach ( $value as $subKey => $subValue ) {
				$newKey = "$key{$this->sep}$subKey";
				$temp[$newKey] = $subValue;
			}
			$flat += $this->flatten( $temp );

			// Can as well keep only one copy around.
			unset( $unflat[$key] );
		}

		return $flat;
	}

	/**
	 * Performs the reverse operation of flatten.
	 *
	 * @param array $flat It's an array
	 * @return array
	 */
	public function unflatten( $flat ) {
		$unflat = array();

		foreach ( $flat as $key => $value ) {
			$path = explode( $this->sep, $key );
			if ( count( $path ) === 1 ) {
				$unflat[$key] = $value;
				continue;
			}

			$pointer = &$unflat;
			do {
				/// Extract the level and make sure it exists.
				$level = array_shift( $path );
				if ( !isset( $pointer[$level] ) ) {
					$pointer[$level] = array();
				}

				/// Update the pointer to the new reference.
				$tmpPointer = &$pointer[$level];
				unset( $pointer );
				$pointer = &$tmpPointer;
				unset( $tmpPointer );

				/// If next level is the last, add it into the array.
				if ( count( $path ) === 1 ) {
					$lastKey = array_shift( $path );
					$pointer[$lastKey] = $value;
				}
			} while ( count( $path ) );
		}

		return $unflat;
	}
}
