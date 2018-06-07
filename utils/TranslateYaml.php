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
 * and generate YAML files with syck or spyc backend.
 */
class TranslateYaml {
	/**
	 * @param string $text
	 * @return array
	 * @throws MWException
	 */
	public static function loadString( $text ) {
		global $wgTranslateYamlLibrary;

		switch ( $wgTranslateYamlLibrary ) {
			case 'phpyaml':
				// Harden: do not support unserializing objects.
				// Method 1: PHP ini setting (not supported by HHVM)
				// Method 2: Callback handler for !php/object
				$previousValue = ini_set( 'yaml.decode_php', false );
				$ignored = 0;
				$callback = function ( $value ) {
					return $value;
				};
				$ret = yaml_parse( $text, 0, $ignored, [ '!php/object' => $callback ] );
				ini_set( 'yaml.decode_php', $previousValue );
				if ( $ret === false ) {
					// Convert failures to exceptions
					throw new InvalidArgumentException( 'Invalid Yaml string' );
				}

				return $ret;
			case 'spyc':
				$yaml = spyc_load( $text );

				return self::fixSpycSpaces( $yaml );
			case 'syck':
				$yaml = self::syckLoad( $text );

				return self::fixSyckBooleans( $yaml );
			default:
				throw new MWException( 'Unknown Yaml library' );
		}
	}

	/**
	 * @param array &$yaml
	 * @return array
	 */
	public static function fixSyckBooleans( &$yaml ) {
		foreach ( $yaml as &$value ) {
			if ( is_array( $value ) ) {
				self::fixSyckBooleans( $value );
			} elseif ( $value === 'yes' ) {
				$value = true;
			}
		}

		return $yaml;
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
			case 'syck':
				return self::syckDump( $text );
			default:
				throw new MWException( 'Unknown Yaml library' );
		}
	}

	protected static function phpyamlDump( $data ) {
		if ( !is_array( $data ) ) {
			return yaml_emit( $data, YAML_UTF8_ENCODING );
		}

		// Fix decimal-less floats strings such as "2."
		// https://bugs.php.net/bug.php?id=76309
		$random = MWCryptRand::generateHex( 8 );
		// Ensure our random does not look like a number
		$random = "X$random";
		$mangler = function ( &$item ) use ( $random ) {
			if ( preg_match( '/^[0-9]+\.$/', $item ) ) {
				$item = "$random$item$random";
			}
		};

		array_walk_recursive( $data, $mangler );
		$yaml = yaml_emit( $data, YAML_UTF8_ENCODING );
		$yaml = str_replace( $random, '"', $yaml );
		return $yaml;
	}

	protected static function syckLoad( $data ) {
		# Make temporary file
		$td = wfTempDir();
		$tf = tempnam( $td, 'yaml-load-' );

		# Write to file
		file_put_contents( $tf, $data );

		$cmd = "perl -MYAML::Syck=LoadFile -MPHP::Serialization=serialize -wle '" .
			'my $tf = q[' . $tf . '];' .
			'my $yaml = LoadFile($tf);' .
			'open my $fh, ">", "$tf.serialized" or die qq[Can not open "$tf.serialized"];' .
			'print $fh serialize($yaml);' .
			'close($fh);' .
			"' 2>&1";

		$out = wfShellExec( $cmd, $ret );

		if ( (int)$ret !== 0 ) {
			throw new MWException( "The command '$cmd' died in execution with exit code '$ret': $out" );
		}

		$serialized = file_get_contents( "$tf.serialized" );
		$php_data = unserialize( $serialized );

		unlink( $tf );
		unlink( "$tf.serialized" );

		return $php_data;
	}

	protected static function syckDump( $data ) {
		# Make temporary file
		$td = wfTempDir();
		$tf = tempnam( $td, 'yaml-load-' );

		# Write to file
		$sdata = serialize( $data );
		file_put_contents( $tf, $sdata );

		$cmd = "perl -MYAML::Syck=DumpFile -MPHP::Serialization=unserialize -MFile::Slurp=slurp -we '" .
			'$YAML::Syck::Headless = 1;' .
			'$YAML::Syck::SortKeys = 1;' .
			'my $tf = q[' . $tf . '];' .
			'my $serialized = slurp($tf);' .
			'my $unserialized = unserialize($serialized);' .
			'my $unserialized_utf8 = deutf8($unserialized);' .
			'DumpFile(qq[$tf.yaml], $unserialized_utf8);' .
			'sub deutf8 {' .
				'if(ref($_[0]) eq "HASH") {' .
					'return { map { deutf8($_) } %{$_[0]} };' .
				'} elsif(ref($_[0]) eq "ARRAY") {' .
					'return [ map { deutf8($_) } @{$_[0]} ];' .
				'} else {' .
					'my $s = $_[0];' .
					'utf8::decode($s);' .
					'return $s;' .
				'}' .
			'}' .
			"' 2>&1";
		$out = wfShellExec( $cmd, $ret );
		if ( (int)$ret !== 0 ) {
			throw new MWException( "The command '$cmd' died in execution with exit code '$ret': $out" );
		}

		$yaml = file_get_contents( "$tf.yaml" );

		unlink( $tf );
		unlink( "$tf.yaml" );

		return $yaml;
	}
}
