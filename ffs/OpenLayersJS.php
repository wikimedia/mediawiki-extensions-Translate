<?php

/**
 * *INCOMPLETE* OpenLayers JavaScript language class file format handler.
 *
 * @author Robert Leverington
 * @copyright Copyright © 2009 Robert Leverington
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 * @file
 */

class JavaScriptFormatReader extends SimpleFormatReader {

	private function leftTrim( $string ) {
		$string = ltrim( $string );
		$string = ltrim( $string, '"' );
		return $string;
	}

	/**
	 * Parse OpenLayer JavaScript language class.
	 * Known issues:
	 *   - It is a requirement for key names to be enclosed in single
	 *     quotation marks, and for messages to be enclosed in double.
	 *   - The last key-value pair must have a comma at the end.
	 *   - Uses seperate $this->leftTrim() function, this is undersired.
	 */
	protected function parseMessages() {
		$this->filename = dirname( __FILE__ ) . '/en.js';
		$data = file_get_contents( $this->filename );

		// Just get relevant data.
		$dataStart = strpos( $data, '{' );
		$dataEnd   = strrpos( $data, '}' );
		$data = substr( $data, $dataStart + 1, $dataEnd - $dataStart - 1);
		// Strip comments.
		$data = preg_replace( '#^(\s*?)//(.*?)$#m', '', $data );
		// Break in to message segements for further parsing.
		$data = explode( '",', $data );

		$messages = array();
		// Process each segment.
		foreach( $data as $segment ) {
			// Remove excess quote mark at beginning.
			$segment = substr( $segment, 1 );
			// Add back trailing quote.
			$segment .= '"';
			// Concatenate seperate strings.
			$segment = explode( '" +', $segment );
			$segment = array_map( array( $this, 'leftTrim' ), $segment );
			$segment = implode( $segment );
			#$segment = preg_replace( '#\" \+(.*?)\"#m', '', $segment );
			// Break in to key and message.
			$segments = explode( '\':', $segment );
			$key = $segments[ 0 ];
			unset( $segments[ 0 ] );
			$value = implode( $segments );
			// Strip excess whitespace from both.
			$key = trim( $key );
			$value = trim( $value );
			// Remove quotation marks and syntax.
			$key = substr( $key, 1 );
			$value = substr( $value, 1, -1 );
			$messages[ $key ] = $value;
		}

		return $messages;
	}

}
