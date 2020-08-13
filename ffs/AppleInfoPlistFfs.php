<?php

/**
 * AppleInfoPlistFfs extends the AppleFFS class and implements support for
 * Apple InfoPlist .strings files.
 *
 * This class reads and writes only UTF-8 files.
 *
 * @ingroup FFS
 * @since 2020.03
 */
class AppleInfoPlistFfs extends AppleFFS {
	/**
	 * Parses non-empty strings file row to key and value.
	 * @param string $line
	 * @throws RuntimeException
	 * @return array array( string $key, string $val )
	 */
	public static function readRow( $line ) {
		$match = [];
		// InfoPList file does not use quoted keys, allows only basic characters without spaces
		// as keys.
		if ( preg_match( '/([A-Za-z ]*)\s*=\s*"((?:\\\"|[^"])*)"\s*;\s*$/', $line, $match ) ) {
			// trimming to allow beginning and ending spaces but these will be removed
			// during exports.
			$key = parent::unescapeString( trim( $match[1] ) );
			$value = parent::unescapeString( $match[2] );

			if ( $key === '' ) {
				throw new RuntimeException( "Empty or invalid key in line: $line" );
			}

			if ( strpos( $key, ' ' ) !== false ) {
				throw new RuntimeException( "Key with space found in line: $line" );
			}

			return [ $key, $value ];
		} else {
			throw new RuntimeException( "Unrecognized line format: $line." );
		}
	}

	/**
	 * Writes well-formed properties file row with key and value.
	 * @param string $key
	 * @param string $value
	 * @return string
	 */
	public static function writeRow( $key, $value ) {
		return $key . ' = ' . parent::quoteString( $value ) . ';' . "\n";
	}
}
