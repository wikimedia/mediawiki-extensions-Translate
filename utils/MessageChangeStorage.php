<?php
/**
 * Handles storage of message change files.
 *
 * @author Niklas Laxström
 * @license GPL-2.0+
 * @since 2016.02
 * @file
 */

class MessageChangeStorage {
	const DEFAULT_NAME = 'default';

	/**
	 * Writes change array as a serialized file.
	 *
	 * @param array $array Array of changes as returned by processGroup
	 * indexed by message group id.
	 * @param string $file Which file to use.
	 */
	public static function writeChanges( $array, $file ) {
		$cache = \Cdb\Writer::open( $file );
		$keys = array_keys( $array );
		$cache->set( '#keys', serialize( $keys ) );

		foreach ( $array as $key => $value ) {
			$value = serialize( $value );
			$cache->set( $key, $value );
		}
		$cache->close();
	}

	/**
	 * Validate a name.
	 *
	 * @param string $name Which file to use.
	 * @return bool
	 */
	public static function isValidCdbName( $name ) {
		return preg_match( '/^[a-zA-Z_-]{1,100}$/', $name );
	}

	/**
	 * Get a full path to file in a known location.
	 *
	 * @param string $name Which file to use.
	 * @return string
	 */
	public static function getCdbPath( $name ) {
		return TranslateUtils::cacheFile( "messagechanges.$name.cdb" );
	}
}
