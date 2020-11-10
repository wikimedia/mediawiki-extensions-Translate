<?php
/**
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
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
 * @since 2020.04 Interface was changed.
 */
interface StringMangler {
	/**
	 * General way to pass configuration to the mangler.
	 *
	 * @param array $configuration
	 */
	public function setConf( array $configuration ): void;

	/**
	 * Match strings against a pattern.
	 *
	 * If string matches, mangle() prefixes the key.
	 *
	 * @param string $key Message key
	 * @return bool
	 */
	public function matches( string $key ): bool;

	/**
	 * Mangle a string.
	 *
	 * @param string $key
	 * @return string Mangled message key
	 */
	public function mangle( string $key ): string;

	/**
	 * Mangle a list of strings.
	 *
	 * @param string[] $list
	 * @return string[]
	 */
	public function mangleList( array $list ): array;

	/**
	 * Mangle the keys of an array.
	 *
	 * @param array<string,mixed> $array
	 * @return array<string,mixed>
	 */
	public function mangleArray( array $array ): array;

	/**
	 * Reverse mangling of a string.
	 *
	 * @param string $key Mangled message key
	 * @return string $key Unmangled message key
	 */
	public function unmangle( string $key ): string;

	/**
	 * Reverse mangling a list of strings.
	 *
	 * @param string[] $list
	 * @return string[]
	 */
	public function unmangleList( array $list ): array;

	/**
	 * Reverse mangling of the keys of an array.
	 *
	 * @param array<string,mixed> $array
	 * @return array<string,mixed>
	 */
	public function unmangleArray( array $array ): array;
}
