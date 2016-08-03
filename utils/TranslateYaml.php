<?php
/**
 * Contains wrapper class for interface to parse and generate YAML files.
 *
 * @file
 * @author Ævar Arnfjörð Bjarmason
 * @author Niklas Laxström
 * @copyright Copyright © 2009-2013, Niklas Laxström, Ævar Arnfjörð Bjarmason
 * @license GPL-2.0+
 */

/**
 * This class is a wrapper class to provide interface to parse
 * and generate YAML files with syck or spyc backend.
 */
class TranslateYaml {
	/**
	 * @param $text string
	 * @return array
	 * @throws MWException
	 */
	public static function loadString( $text ) {
		global $wgTranslateYamlLibrary;

		switch ( $wgTranslateYamlLibrary ) {
			case 'phpyaml':
				$ret = yaml_parse( $text );
				if ( $ret === false ) {
					// Convert failures to exceptions
					throw new InvalidArgumentException( 'Invalid Yaml string' );
				}

				return $ret;

			case 'spyc':
				// Load the bundled version if not otherwise available
				if ( !class_exists( 'Spyc' ) ) {
					require_once __DIR__ . '/../libs/spyc/spyc.php';
				}
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
	 * @param $yaml array
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
	 * @param $yaml array
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
				return yaml_emit( $text, YAML_UTF8_ENCODING );

			case 'spyc':
				require_once __DIR__ . '/../libs/spyc/spyc.php';

				return Spyc::YAMLDump( $text );
			case 'syck':
				return self::syckDump( $text );
			default:
				throw new MWException( 'Unknown Yaml library' );
		}
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
