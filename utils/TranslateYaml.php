<?php
/**
 * Contains wrapper class for interface to parse and generate YAML files.
 *
 * @file
 * @author Ævar Arnfjörð Bjarmason
 * @author Niklas Laxström
 * @copyright Copyright © 2009-2013, Niklas Laxström, Ævar Arnfjörð Bjarmason
 * @license GPL-2.0-or-later
 */

/**
 * This class is a wrapper class to provide interface to parse
 * and generate YAML files with phpyaml or spyc backend.
 */
class TranslateYaml {
	/**
	 * @param string $text
	 * @return array
	 */
	public static function loadString( $text ) {
		global $wgTranslateYamlLibrary;

		switch ( $wgTranslateYamlLibrary ) {
			case 'phpyaml':
				// Harden: do not support unserializing objects.
				// @phan-suppress-next-line PhanTypeMismatchArgumentInternal Scalar okay with php8.1
				$previousValue = ini_set( 'yaml.decode_php', false );
				$ret = yaml_parse( $text );
				ini_set( 'yaml.decode_php', $previousValue );
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

	/**
	 * @param array &$yaml
	 * @return array
	 */
	public static function fixSpycSpaces( &$yaml ) {
		foreach ( $yaml as $key => &$value ) {
			if ( is_array( $value ) ) {
				self::fixSpycSpaces( $value );
			} elseif ( is_string( $value ) && $key === 'header' ) {
				$value = preg_replace( '~^\*~m', ' *', $value ) . "\n";
			}
		}

		return $yaml;
	}

	public static function load( $file ) {
		$text = file_get_contents( $file );

		return self::loadString( $text );
	}

	public static function dump( $text ) {
		global $wgTranslateYamlLibrary;

		switch ( $wgTranslateYamlLibrary ) {
			case 'phpyaml':
				return self::phpyamlDump( $text );
			case 'spyc':
				return Spyc::YAMLDump( $text );
			default:
				throw new RuntimeException( 'Unknown Yaml library' );
		}
	}

	protected static function phpyamlDump( $data ) {
		return yaml_emit( $data, YAML_UTF8_ENCODING );
	}
}
