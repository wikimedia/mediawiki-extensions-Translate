<?php

/**
 * JavaFFS class implements support for Java properties files.
 * This class reads and writes only utf-8 files. Java projects
 * need to run native2ascii on them before using them.
 *
 * This class adds a new item into FILES section of group configuration:
 * \c keySeparator which defaults to '='.
 * @ingroup FFS
 */
class JavaFFS extends SimpleFFS implements MetaYamlSchemaExtender {
	public function supportsFuzzy() {
		return 'write';
	}

	public function getFileExtensions() {
		return array( '.properties' );
	}

	protected $keySeparator = '=';

	/**
	 * @param $group FileBasedMessageGroup
	 */
	public function __construct( FileBasedMessageGroup $group ) {
		parent::__construct( $group );

		if ( isset( $this->extra['keySeparator'] ) ) {
			$this->keySeparator = $this->extra['keySeparator'];
		}
	}

	// READ

	/**
	 * @param $data array
	 * @return array Parsed data.
	 * @throws MWException
	 */
	public function readFromVariable( $data ) {
		$data = self::fixNewLines( $data );
		$lines = array_map( 'ltrim', explode( "\n", $data ) );
		$authors = $messages = array();
		$linecontinuation = false;

		$key = '';
		$value = '';
		foreach ( $lines as $line ) {
			if ( $linecontinuation ) {
				$linecontinuation = false;
				$valuecont = $line;
				$valuecont = str_replace( '\n', "\n", $valuecont );
				$value .= $valuecont;
			} else {
				if ( $line === '' ) {
					continue;
				}

				if ( $line[0] === '#' || $line[0] === '!' ) {
					$match = array();
					$ok = preg_match( '/#\s*Author:\s*(.*)/', $line, $match );

					if ( $ok ) {
						$authors[] = $match[1];
					}

					continue;
				}

				if ( strpos( $line, $this->keySeparator ) === false ) {
					throw new MWException( "Line without separator '{$this->keySeparator}': $line." );
				}

				list( $key, $value ) = self::readRow( $line, $this->keySeparator );
				if ( $key === '' ) {
					throw new MWException( "Empty key in line $line." );
				}
			}

			// @todo This doesn't handle the pathological case of even number of trailing \
			if ( strlen( $value ) && $value[strlen( $value ) - 1] === "\\" ) {
				$value = substr( $value, 0, strlen( $value ) - 1 );
				$linecontinuation = true;
			} else {
				$messages[$key] = ltrim( $value );
			}
		}

		$messages = $this->group->getMangler()->mangle( $messages );

		return array(
			'AUTHORS' => $authors,
			'MESSAGES' => $messages,
		);
	}

	// Write

	/**
	 * @param $collection MessageCollection
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
				$output .= "# Fuzzy\n";
			}

			$key = $mangler->unmangle( $key );
			$output .= self::writeRow( $key, $this->keySeparator, $value );
		}

		if ( $output ) {
			return $header . $output;
		}

		return '';
	}

	/**
	 * Writes well-formed properties file row with key and value.
	 * @param string $key
	 * @param string $sep
	 * @param string $value
	 * @return string
	 * @since 2012-03-28
	 */
	public static function writeRow( $key, $sep, $value ) {
		/* Keys containing the separator need escaping. Also escape comment
		 * characters, though strictly they would only need escaping when
		 * they are the first character. Plus the escape character itself. */
		$key = addcslashes( $key, "#!$sep\\" );
		// Make sure we do not slip newlines trough... it would be fatal.
		$value = str_replace( "\n", '\\n', $value );

		return "$key$sep$value\n";
	}

	/**
	 * Parses non-empty properties file row to key and value.
	 * @param string $line
	 * @param string $sep
	 * @return string
	 * @since 2012-03-28
	 */
	public static function readRow( $line, $sep ) {
		if ( strpos( $line, '\\' ) === false ) {
			/* Nothing appears to be escaped in this line.
			 * Just read the key and the value. */
			list( $key, $value ) = explode( $sep, $line, 2 );
		} else {
			/* There might be escaped separators in the key.
			 * Using slower method to find the separator. */

			/* Make the key default to empty instead of value, because
			 * empty key causes error on callers, while empty value
			 * wouldn't. */
			$key = '';
			$value = $line;

			/* Find the first unescaped separator. Example:
			 * First line is the string being read, second line is the
			 * value of $escaped after having read the above character.
			 *
			 * ki\ts\\s\=a = koira
			 * 0010010010000
			 *          ^ Not separator because $escaped was true
			 *             ^ Split the string into key and value here
			 */

			$len = strlen( $line );
			$escaped = false;
			for ( $i = 0; $i < $len; $i++ ) {
				$char = $line[$i];
				if ( $char === '\\' ) {
					$escaped = !$escaped;
				} elseif ( $escaped ) {
					$escaped = false;
				} elseif ( $char === $sep ) {
					$key = substr( $line, 0, $i );
					// Excluding the separator character from the value
					$value = substr( $line, $i + 1 );
					break;
				}
			}
		}

		/* We usually don't want to expand things like \t in values since
		 * translators cannot easily input those. But in keys we do.
		 * \n is exception we do handle in values. */
		$key = trim( $key );
		$key = stripcslashes( $key );
		$value = ltrim( $value );
		$value = str_replace( '\n', "\n", $value );

		return array( $key, $value );
	}

	/**
	 * @param $collection MessageCollection
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
			$output = "# Messages for $name ($native)\n";
			$output .= "# Exported from $wgSitename\n";
		}

		return $output;
	}

	/**
	 * @param $collection MessageCollection
	 * @return string
	 */
	protected function doAuthors( MessageCollection $collection ) {
		$output = '';
		$authors = $collection->getAuthors();
		$authors = $this->filterAuthors( $authors, $collection->code );

		foreach ( $authors as $author ) {
			$output .= "# Author: $author\n";
		}

		return $output;
	}

	public static function getExtraSchema() {
		$schema = array(
			'root' => array(
				'_type' => 'array',
				'_children' => array(
					'FILES' => array(
						'_type' => 'array',
						'_children' => array(
							'header' => array(
								'_type' => 'text',
							),
							'keySeparator' => array(
								'_type' => 'text',
							),
						)
					)
				)
			)
		);

		return $schema;
	}
}
