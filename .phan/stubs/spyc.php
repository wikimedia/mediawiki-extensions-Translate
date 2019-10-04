<?php

/**
 * https://www.php.net/manual/en/yaml.constants.php
 */
define( 'YAML_UTF8_ENCODING', 1 );

/**
 * stub for suggested mustangostang/spyc
 * @phpcs:disable MediaWiki.Files,MediaWiki.NamingConventions
 */

class Spyc {
	/**
	 * @param array|\stdClass $array
	 * @param int $indent
	 * @param int $wordwrap
	 * @param bool $no_opening_dashes
	 * @return string
	 */
	public static function YAMLDump(
		$array, $indent = false, $wordwrap = false, $no_opening_dashes = false
	) {
	}
}

/**
 * @param string $string
 * @return array
 */
function spyc_load( $string ) {
}
