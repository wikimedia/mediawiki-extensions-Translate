<?php
/**
 * Support for the AMD i18n message file format (used by require.js and Dojo). See:
 * http://requirejs.org/docs/api.html#i18n
 *
 * A limitation is that it only accepts json compatible structures inside the define
 * wrapper function. For example the following example is not ok since there are no
 * quotation marks around the keys:
 * define({
 *   key1: "somevalue",
 *   key2: "anothervalue"
 * });
 *
 * Instead it should look like:
 * define({
 *   "key1": "somevalue",
 *   "key2": "anothervalue"
 * });
 *
 * It also supports the the top-level bundle with a root construction and language indicators.
 * The following example will give the same messages as above:
 * define({
 *   "root": {
 *      "key1": "somevalue",
 *      "key2": "anothervalue"
 *   },
 *   "sv": true
 * });
 *
 * Note that it does not support exporting with the root construction, there is only support
 * for reading it. However, this is not a serious limitation as Translatewiki doesn't export
 * the base language.
 *
 * @file
 * @author Matthias Palmér
 * @copyright Copyright © 2011-2015, MetaSolutions AB
 * @license GPL-2.0+
 */

/**
 * AmdFFS implements a message format where messages are encoded
 * as key-value pairs in JSON objects wrapped in a define call.
 *
 * @ingroup FFS
 * @since 2015.02
 */
class AmdFFS extends SimpleFFS {

	/**
	 * @param string $data
	 * @return bool
	 */
	public static function isValid( $data ) {
		$data = AmdFFS::extractMessagePart( $data );
		return is_array( FormatJson::decode( $data, /*as array*/true ) );
	}

	public function getFileExtensions() {
		return array( '.js' );
	}

	/**
	 * @param array $data
	 * @return array Parsed data.
	 */
	public function readFromVariable( $data ) {
		$authors = AmdFFS::extractAuthors( $data );
		$data = AmdFFS::extractMessagePart( $data );
		$messages = (array) FormatJson::decode( $data, /*as array*/true );
		$metadata = array();

		// Take care of regular language bundles, as well as the root bundle.
		if ( isset( $messages['root'] ) ) {
			$messages = $this->group->getMangler()->mangle( $messages['root'] );
		} else {
			$messages = $this->group->getMangler()->mangle( $messages );
		}

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
		$mangler = $this->group->getMangler();

		/** @var ThinMessage $m */
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
		$header = $this->header( $collection->code, $collection->getAuthors() );
		return $header . FormatJson::encode( $messages, "\t", FormatJson::UTF8_OK ) . ");\n";
	}

	/**
	 * @param string $data
	 * @return string of JSON
	 */
	private static function extractMessagePart( $data ) {
		// Find the start and end of the data section (enclosed in the define function call).
		$dataStart = strpos( $data, 'define(' ) + 6;
		$dataEnd = strrpos( $data, ')' );

		// Strip everything outside of the data section.
		return substr( $data, $dataStart + 1, $dataEnd - $dataStart - 1 );
	}

	/**
	 * @param string $data
	 * @return array
	 */
	private static function extractAuthors( $data ) {
		preg_match_all( '~\n \*  - (.+)~', $data, $result );
		return $result[1];
	}

	/**
	 * @param string $code
	 * @param array $authors
	 * @return string
	 */
	private function header( $code, $authors ) {
		global $wgSitename;

		$name = TranslateUtils::getLanguageName( $code );
		$authorsList = $this->authorsList( $authors );

		return <<<EOT
/**
 * Messages for $name
 * Exported from $wgSitename
 *
{$authorsList}
 */
define(
EOT;
	}

	/**
	 * @param array $authors
	 * @return string
	 */
	private function authorsList( array $authors ) {
		if ( count( $authors ) === 0 ) {
			return '';
		}

		$prefix = ' *  - ';
		$authorList = implode( "\n$prefix", $authors );
		return " * Translators:\n$prefix$authorList";
	}
}
