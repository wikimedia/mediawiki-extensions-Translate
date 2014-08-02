<?php
/**
 * StringMangler interface.
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0+
 */

/**
 * Interface that key-mangling classes must implement. Mangling is done to:
 * - converting characters which would be invalid in titles to something valid
 * - prefixing a set of messages to avoid conflicts when sharing a namespace
 *   with multiple message groups.
 *
 * The operations have to be reversible so that
 * x equals unMangle( mangle( x ) ).
 *
 */
interface StringMangler {
	/**
	 * General way to pass configuration to the mangler.
	 * @param array $configuration
	 */
	public function setConf( $configuration );

	/**
	 * Match strings against a pattern.
	 * If string matches, mangle() should mangle the key.
	 * @param string $string Message key.
	 * @return bool
	 */
	public function match( $string );

	/**
	 * Mangle a single key.
	 * @param string $key
	 * @return string
	 */
	public function mangle( $key );

	/**
	 * Reverse of mangle.
	 * @param string $key
	 * @return string
	 */
	public function unmangle( $key );
}
