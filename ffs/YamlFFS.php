<?php

/**
 * Implements support for message storage in YAML format.
 *
 * This class adds new key into FILES section: \c codeAsRoot.
 * If it is set to true, all messages will under language code.
 * @ingroup FFS
 */
class YamlFFS extends SimpleFFS implements MetaYamlSchemaExtender {
	public function getFileExtensions() {
		return array( '.yaml', '.yml' );
	}

	/**
	 * @param $data
	 * @return array Parsed data.
	 */
	public function readFromVariable( $data ) {
		// Authors first.
		$matches = array();
		preg_match_all( '/^#\s*Author:\s*(.*)$/m', $data, $matches );
		$authors = $matches[1];

		// Then messages.
		$messages = TranslateYaml::loadString( $data );

		// Some groups have messages under language code
		if ( isset( $this->extra['codeAsRoot'] ) ) {
			$messages = array_shift( $messages );
		}

		$messages = $this->flatten( $messages );
		$messages = $this->group->getMangler()->mangle( $messages );
		foreach ( $messages as &$value ) {
			$value = rtrim( $value, "\n" );
		}

		return array(
			'AUTHORS' => $authors,
			'MESSAGES' => $messages,
		);
	}

	/**
	 * @param $collection MessageCollection
	 * @return string
	 */
	protected function writeReal( MessageCollection $collection ) {
		$output = $this->doHeader( $collection );
		$output .= $this->doAuthors( $collection );

		$mangler = $this->group->getMangler();

		$messages = array();
		/**
		 * @var $m TMessage
		 */
		foreach ( $collection as $key => $m ) {
			$key = $mangler->unmangle( $key );
			$value = $m->translation();
			$value = str_replace( TRANSLATE_FUZZY, '', $value );

			if ( $value === '' ) {
				continue;
			}

			$messages[$key] = $value;
		}

		if ( !count( $messages ) ) {
			return false;
		}

		$messages = $this->unflatten( $messages );

		// Some groups have messages under language code.
		if ( isset( $this->extra['codeAsRoot'] ) ) {
			$code = $this->group->mapCode( $collection->code );
			$messages = array( $code => $messages );
		}

		$output .= TranslateYaml::dump( $messages );

		return $output;
	}

	/**
	 * @param $collection MessageCollection
	 * @return string
	 */
	protected function doHeader( MessageCollection $collection ) {
		global $wgSitename;
		global $wgTranslateYamlLibrary;

		$code = $collection->code;
		$name = TranslateUtils::getLanguageName( $code );
		$native = TranslateUtils::getLanguageName( $code, $code );
		$output = "# Messages for $name ($native)\n";
		$output .= "# Exported from $wgSitename\n";

		if ( isset( $wgTranslateYamlLibrary ) ) {
			$output .= "# Export driver: $wgTranslateYamlLibrary\n";
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

	/**
	 * Flattens multidimensional array by using the path to the value as key
	 * with each individual key separated by a dot.
	 *
	 * @param $messages array
	 *
	 * @return array
	 */
	protected function flatten( $messages ) {
		$flat = true;

		foreach ( $messages as $v ) {
			if ( !is_array( $v ) ) {
				continue;
			}

			$flat = false;
			break;
		}

		if ( $flat ) {
			return $messages;
		}

		$array = array();
		foreach ( $messages as $key => $value ) {
			if ( !is_array( $value ) ) {
				$array[$key] = $value;
			} else {
				$plural = $this->flattenPlural( $value );
				if ( $plural ) {
					$array[$key] = $plural;
				} else {
					$newArray = array();
					foreach ( $value as $newKey => $newValue ) {
						$newArray["$key.$newKey"] = $newValue;
					}
					$array += $this->flatten( $newArray );
				}
			}

			/**
			 * Can as well keep only one copy around.
			 */
			unset( $messages[$key] );
		}

		return $array;
	}

	/**
	 * Performs the reverse operation of flatten. Each dot in the key starts a
	 * new subarray in the final array.
	 *
	 * @param $messages array
	 *
	 * @return array
	 */
	protected function unflatten( $messages ) {
		$array = array();
		foreach ( $messages as $key => $value ) {
			$plurals = $this->unflattenPlural( $key, $value );

			if ( $plurals === false ) {
				continue;
			}

			foreach ( $plurals as $keyPlural => $valuePlural ) {
				$path = explode( '.', $keyPlural );
				if ( count( $path ) === 1 ) {
					$array[$keyPlural] = $valuePlural;
					continue;
				}

				$pointer = &$array;
				do {
					/**
					 * Extract the level and make sure it exists.
					 */
					$level = array_shift( $path );
					if ( !isset( $pointer[$level] ) ) {
						$pointer[$level] = array();
					}

					/**
					 * Update the pointer to the new reference.
					 */
					$tmpPointer = &$pointer[$level];
					unset( $pointer );
					$pointer = &$tmpPointer;
					unset( $tmpPointer );

					/**
					 * If next level is the last, add it into the array.
					 */
					if ( count( $path ) === 1 ) {
						$lastKey = array_shift( $path );
						$pointer[$lastKey] = $valuePlural;
					}
				} while ( count( $path ) );
			}
		}

		return $array;
	}

	/**
	 * @param $value
	 * @return bool
	 */
	public function flattenPlural( $value ) {
		return false;
	}

	/**
	 * Override this. Return false to skip processing this value. Otherwise
	 *
	 * @param $key string
	 * @param $value string
	 *
	 * @return array with keys and values.
	 */
	public function unflattenPlural( $key, $value ) {
		return array( $key => $value );
	}

	public static function getExtraSchema() {
		$schema = array(
			'root' => array(
				'_type' => 'array',
				'_children' => array(
					'FILES' => array(
						'_type' => 'array',
						'_children' => array(
							'codeAsRoot' => array(
								'_type' => 'boolean',
							),
						)
					)
				)
			)
		);

		return $schema;
	}
}
