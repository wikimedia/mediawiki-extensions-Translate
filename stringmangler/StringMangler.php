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
 * x equals unmangle( mangle( x ) ).
 *
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
	public function unmangle( $data );
}
