<?php

/**
 * This class is a wrapper class to provide interface to parse
 * and generate YAML files with syck or spyc backend.
 */
class TranslateYaml {
	public static function parseGroupFile( $filename ) {
		$data = file_get_contents( $filename );
		$documents = preg_split( "/^---$/m", $data, -1, PREG_SPLIT_NO_EMPTY );
		$groups = array();
		$template = false;
		foreach ( $documents as $document ) {
			$document = self::loadString( $document );
			if ( isset( $document['TEMPLATE'] ) ) {
				$template = $document['TEMPLATE'];
			} else {
				if ( !isset($document['BASIC']['id']) ) {
					trigger_error( "No path ./BASIC/id (group id not defined) in yaml document located in $filename" );
					continue;
				}
				$groups[$document['BASIC']['id']] = $document;
			}
		}

		foreach ( $groups as $i => $group ) {
			$groups[$i] = self::mergeTemplate( $template, $group );
		}

		return $groups;
	}

	/**
	 * Merges a document template (base) to actual definition (specific)
	 */
	public static function mergeTemplate( $base, $specific ) {
		foreach ( $specific as $key => $value ) {
			if ( is_array( $value ) && isset( $base[$key] ) && is_array( $base[$key] ) ) {
				$base[$key] = self::mergeTemplate( $base[$key], $value );
			} else {
				$base[$key] = $value;
			}
		}
		return $base;
	}


	public static function loadString( $text ) {
		global $wgTranslateYamlLibrary;

		switch ( $wgTranslateYamlLibrary ) {
			case 'spyc':
				require_once( dirname( __FILE__ ) . '/../spyc/spyc.php' );
				return spyc_load( $text );
			case 'syck':
				return self::syckLoad( $text );
			default:
				throw new MWException( "Unknown Yaml library" );
		}
	}

	public static function load( $file ) {
		$text = file_get_contents( $file );

		return self::loadString( $text );
	}

	public static function dump( $text ) {
		global $wgTranslateYamlLibrary;

		switch ( $wgTranslateYamlLibrary ) {
			case 'spyc':
				require_once( dirname( __FILE__ ) . '/../spyc/spyc.php' );
				return Spyc::YAMLDump( $text );
			case 'syck':
				return self::syckDump( $text );
			default:
				throw new MWException( "Unknown Yaml library" );
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

		if ( $ret != 0 ) {
			wfDebugDieBacktrace( "The command '$cmd' died in execution with exit code '$ret': $out" );
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
		if ( $ret != 0 ) {
			wfDebugDieBacktrace( "The command '$cmd' died in execution with exit code '$ret': $out" );
		}

		$yaml = file_get_contents( "$tf.yaml" );

		unlink( $tf );
		unlink( "$tf.yaml" );

		return $yaml;
	}
}

