<?php
/**
 * Support for JSON message file format.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2012-2013, Niklas Laxström
 * @license GPL-2.0+
 */

/**
 * JsonFFS implements a message format where messages are encoded
 * as key-value pairs in JSON objects. The format is extended to
 * support author information under the special @metadata key.
 *
 * @ingroup FFS
 * @since 2012-09-21
 */
class JsonFFS extends SimpleFFS {
	/**
	 * @param $data
	 * @return bool
	 */
	public static function isValid( $data ) {
		return is_array( FormatJSON::decode( $data, /*as array*/true ) );
	}

	public function getFileExtensions() {
		return array( '.json' );
	}

	/**
	 * @param array $data
	 * @return array
	 */
	public function readFromVariable( $data ) {
		$messages = (array) FormatJSON::decode( $data, /*as array*/true );
		$authors = array();
		$metadata = array();

		if ( isset( $messages['@metadata']['authors'] ) ) {
			$authors = (array)$messages['@metadata']['authors'];
			unset( $messages['@metadata']['authors'] );
		}

		if ( isset( $messages['@metadata'] ) ) {
			$metadata = $messages['@metadata'];
		}

		unset( $messages['@metadata'] );

		$messages = $this->flatten( $messages );
		$messages = $this->group->getMangler()->mangle( $messages );

		return array(
			'MESSAGES' => $messages,
			'AUTHORS' => $authors,
			'METADATA' => $metadata,
		);
	}

	/**
	 * @param MessageCollection $collection
	 * @return string
	 */
	protected function writeReal( MessageCollection $collection ) {
		$messages = array();
		$template = $this->read( $collection->getLanguage() );

		if ( isset( $template['METADATA'] ) ) {
			$messages['@metadata'] = $template['METADATA'];
		}

		$authors = $collection->getAuthors();
		$authors = $this->filterAuthors( $authors, $collection->code );

		if ( $authors !== array() ) {
			$messages['@metadata']['authors'] = $authors;
		}

		$mangler = $this->group->getMangler();

		/**
		 * @var $m ThinMessage
		 */
		foreach ( $collection as $key => $m ) {
			$value = $m->translation();
			if ( $value === null ) {
				continue;
			}

			if ( $m->hasTag( 'fuzzy' ) ) {
				$value = str_replace( TRANSLATE_FUZZY, '', $value );
			}

			$key = $mangler->unmangle( $key );
			$messages[$key] = $value;
		}

		// Do not create empty files
		if ( !count( $messages ) ) {
			return '';
		}

		$messages = $this->unflatten( $messages );

		return FormatJSON::encode( $messages, /*pretty*/true );
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
				if ( count( $path ) == 1 ) {
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
}
