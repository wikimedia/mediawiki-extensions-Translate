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
	 * @return array Parsed data.
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

		if ( isset( $template['AUTHORS'] ) ) {
			$authors = array_unique( array_merge( $template['AUTHORS'], $authors ) );
		}

		if ( $authors !== array() ) {
			$messages['@metadata']['authors'] = array_values( $authors );
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

		// BC for MediaWiki <= 1.21
		if ( defined( 'FormatJson::ALL_OK' ) ) {
			return FormatJSON::encode( $messages, /*pretty*/true, FormatJson::ALL_OK ) . "\n";
		} else {
			return FormatJSON::encode( $messages, /*pretty*/true ) . "\n";
		}
	}
}
