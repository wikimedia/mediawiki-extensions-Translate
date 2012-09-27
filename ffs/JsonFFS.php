<?php
/**
 * Support for JSON message file format.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2012, Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
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
		return is_array( FormatJSON::decode( $data, $asArray = true ) );
	}

	/**
	 * @param array $data
	 * @return array
	 */
	public function readFromVariable( $data ) {
		$messages = (array) FormatJSON::decode( $data, $asArray = true );
		$authors = array();
		if ( isset( $messages['@metadata']['authors'] ) ) {
			$authors = (array) $messages['@metadata']['authors'];
		}
		unset( $messages['@metadata'] );

		$messages = $this->group->getMangler()->mangle( $messages );

		return array(
			'MESSAGES' => $messages,
			'AUTHORS' => $authors,
		);
	}

	/**
	 * @param MessageCollection $collection
	 * @return string
	 */
	protected function writeReal( MessageCollection $collection ) {
		$messages = array();
		$mangler = $this->group->getMangler();

		/**
		 * @var $m ThinMessage
		 */
		foreach ( $collection as $key => $m ) {
			$value = $m->translation();
			if ( $m->hasTag( 'fuzzy' ) ) {
				$value = str_replace( TRANSLATE_FUZZY, '', $value );
			}

			$key = $mangler->unmangle( $key );
			$messages[$key] = $value;
		}

		$authors = $collection->getAuthors();
		$authors = $this->filterAuthors( $authors, $collection->code );

		if ( $authors !== array() ) {
			$messages['@metadata']['authors'] = $authors;
		}

		return FormatJSON::encode( $messages, true );
	}
}
