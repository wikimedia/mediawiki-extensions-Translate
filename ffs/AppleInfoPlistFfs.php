<?php

use MediaWiki\Extension\Translate\FileFormatSupport\AppleFormat;

/**
 * AppleInfoPlistFfs extends the AppleFFS class and implements support for
 * Apple InfoPlist .strings files.
 *
 * This class reads and writes only UTF-8 files.
 *
 * @ingroup FileFormatSupport
 * @since 2020.03
 */
class AppleInfoPlistFfs extends AppleFormat {
	/**
	 * @throws RuntimeException
	 * @inheritDoc
	 */
	public function readRow( string $line ): array {
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

			if ( str_contains( $key, ' ' ) ) {
				throw new RuntimeException( "Key with space found in line: $line" );
			}

			return [ $key, $value ];
		} else {
			throw new RuntimeException( "Unrecognized line format: $line." );
		}
	}

	/**
	 * Writes well-formed properties file row with key and value.
	 * @inheritDoc
	 */
	public function writeRow( string $key, string $value ): string {
		return $key . ' = ' . parent::quoteString( $value ) . ';' . "\n";
	}
}
