<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Utilities;

use InvalidArgumentException;
use RuntimeException;
use Spyc;
use function spyc_load;
use function yaml_emit;
use function yaml_parse;

/**
 * A wrapper class to provide interface to parse
 * and generate YAML files with phpyaml or spyc backend.
 * @author Ævar Arnfjörð Bjarmason
 * @author Niklas Laxström
 * @copyright Copyright © 2009-2013, Niklas Laxström, Ævar Arnfjörð Bjarmason
 * @license GPL-2.0-or-later
 */
class Yaml {
	public static function loadString( string $text ): array {
		global $wgTranslateYamlLibrary;

		switch ( $wgTranslateYamlLibrary ) {
			case 'phpyaml':
				// Harden: do not support unserializing objects.
				$previousValue = ini_set( 'yaml.decode_php', '0' );
				$ret = yaml_parse( $text );
				if ( $previousValue !== false ) {
					ini_set( 'yaml.decode_php', $previousValue );
				}

				if ( $ret === false ) {
					// Convert failures to exceptions
					throw new InvalidArgumentException( 'Invalid Yaml string' );
				}

				return $ret;
			case 'spyc':
				$yaml = spyc_load( $text );

				return self::fixSpycSpaces( $yaml );
			default:
				throw new RuntimeException( 'Unknown Yaml library' );
		}
	}

	private static function fixSpycSpaces( array &$yaml ): array {
		foreach ( $yaml as $key => &$value ) {
			if ( is_array( $value ) ) {
				self::fixSpycSpaces( $value );
			} elseif ( is_string( $value ) && $key === 'header' ) {
				$value = preg_replace( '~^\*~m', ' *', $value ) . "\n";
			}
		}

		return $yaml;
	}

	public static function load( string $file ): array {
		$text = file_get_contents( $file );

		return self::loadString( $text );
	}

	public static function dump( array $text ): string {
		global $wgTranslateYamlLibrary;

		switch ( $wgTranslateYamlLibrary ) {
			case 'phpyaml':
				return yaml_emit( $text, YAML_UTF8_ENCODING );
			case 'spyc':
				return Spyc::YAMLDump( $text );
			default:
				throw new RuntimeException( 'Unknown Yaml library' );
		}
	}
}
