<?php

/**
 * AppleFFS class implements support for Apple .strings files.
 * This class reads and writes only UTF-8 files.
 *
 * This class has not yet been battle-tested, so beware.
 *
 * @author Brion Vibber <bvibber@wikimedia.org>
 *
 * @ingroup FFS
 * @since 2014.02
 */
class AppleFFS extends SimpleFFS {
	public function supportsFuzzy() {
		return 'write';
	}

	public function getFileExtensions() {
		return array( '.strings' );
	}

	// READ

	/**
	 * @param array $data
	 * @return array Parsed data.
	 * @throws MWException
	 */
	public function readFromVariable( $data ) {
		$lines = explode( "\n", $data );
		$authors = $messages = array();
		$linecontinuation = false;

		$value = '';
		foreach ( $lines as $line ) {
			if ( $linecontinuation ) {
				$linecontinuation = false;
				$valuecont = $line;
				$value .= $valuecont;
			} else {
				if ( $line === '' ) {
					continue;
				}

				if ( substr( $line, 0, 2 ) === '//' ) {
					// Single-line comment
					$match = array();
					$ok = preg_match( '~//\s*Author:\s*(.*)~', $line, $match );
					if ( $ok ) {
						$authors[] = $match[1];
					}
					continue;
				}

				if ( substr( $line, 0, 2 ) === '/*' ) {
					if ( strpos( $line, '*/', 2 ) === false ) {
						$linecontinuation = true;
					}
					continue;
				}

				list( $key, $value ) = self::readRow( $line );
				$messages[$key] = $value;
			}
		}

		$messages = $this->group->getMangler()->mangle( $messages );

		return array(
			'AUTHORS' => $authors,
			'MESSAGES' => $messages,
		);
	}

	/**
	 * Parses non-empty strings file row to key and value.
	 * @param string $line
	 * @throws MWException
	 * @return array( string $key, string $val )
	 */
	public static function readRow( $line ) {
		$match = array();
		if ( preg_match( '/^"((?:\\\"|[^"])*)"\s*=\s*"((?:\\\"|[^"])*)"\s*;\s*$/', $line, $match ) ) {
			$key = self::unescapeString( $match[1] );
			$value = self::unescapeString( $match[2] );
			if ( $key === '' ) {
				throw new MWException( "Empty key in line $line" );
			}
			return array( $key, $value );
		} else {
			throw new MWException( "Unrecognized line format: $line" );
		}
	}

	// Write

	/**
	 * @param MessageCollection $collection
	 * @return string
	 */
	protected function writeReal( MessageCollection $collection ) {
		$header = $this->doHeader( $collection );
		$header .= $this->doAuthors( $collection );
		$header .= "\n";

		$output = '';
		$mangler = $this->group->getMangler();

		/**
		 * @var TMessage $m
		 */
		foreach ( $collection as $key => $m ) {
			$value = $m->translation();
			$value = str_replace( TRANSLATE_FUZZY, '', $value );

			if ( $value === '' ) {
				continue;
			}

			// Just to give an overview of translation quality.
			if ( $m->hasTag( 'fuzzy' ) ) {
				$output .= "// Fuzzy\n";
			}

			$key = $mangler->unmangle( $key );
			$output .= self::writeRow( $key, $value );
		}

		if ( $output ) {
			$data = $header . $output;
		} else {
			$data = $header;
		}

		return $data;
	}

	/**
	 * Writes well-formed properties file row with key and value.
	 * @param string $key
	 * @param string $value
	 * @return string
	 */
	public static function writeRow( $key, $value ) {
		return self::quoteString( $key ) . ' = ' . self::quoteString( $value ) . ';' . "\n";
	}

	/**
	 * Quote and escape Obj-C-style strings for .strings format.
	 *
	 * @param string $str
	 * @return string
	 */
	protected static function quoteString( $str ) {
		return '"' . self::escapeString( $str ) . '"';
	}

	/**
	 * Escape Obj-C-style strings; use backslash-escapes etc.
	 *
	 * @param string $str
	 * @return string
	 */
	protected static function escapeString( $str ) {
		$str = addcslashes( $str, '\\"' );
		$str = str_replace( "\n", '\\n', $str );
		return $str;
	}

	/**
	 * Unescape Obj-C-style strings; can include backslash-escapes
	 *
	 * @todo support \UXXXX
	 *
	 * @param string $str
	 * @return string
	 */
	protected static function unescapeString( $str ) {
		return stripcslashes( $str );
	}

	/**
	 * @param MessageCollection $collection
	 * @return string
	 */
	protected function doHeader( MessageCollection $collection ) {
		if ( isset( $this->extra['header'] ) ) {
			$output = $this->extra['header'];
		} else {
			global $wgSitename;

			$code = $collection->code;
			$name = TranslateUtils::getLanguageName( $code );
			$native = TranslateUtils::getLanguageName( $code, $code );
			$output = "// Messages for $name ($native)\n";
			$output .= "// Exported from $wgSitename\n";
		}

		return $output;
	}

	/**
	 * @param MessageCollection $collection
	 * @return string
	 */
	protected function doAuthors( MessageCollection $collection ) {
		$output = '';
		$authors = $collection->getAuthors();
		$authors = $this->filterAuthors( $authors, $collection->code );

		foreach ( $authors as $author ) {
			$output .= "// Author: $author\n";
		}

		return $output;
	}
}
