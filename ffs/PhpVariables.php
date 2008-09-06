<?php
/**
 * PHP variables file format handler.
 *
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @copyright Copyright © 2008, Niklas Laxström, Siebrand Mazeland
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 * @file
 */

/**
 * Reader for PHP variables files. Not completely general, as it excepts two
 * comment sections at the top, separated by a blank line.
 *
 * Authors in the first section are detected, if prefixed with '# Author: '.
 * Second section (if any) is returned verbatim.
 */
class PhpVariablesFormatReader extends SimpleFormatReader {

	/**
 	 * Inherited from SimpleFormatReader, which parses whole header in one pass.
	 * Basically the same, with different author prefix and separator between
	 * headers and messages.
	 *
	 * FIXME: possible to refactor to reduce duplication?
	 */
	protected function parseHeader() {
		$authors = array();
		$staticHeader = '';

		if ( $this->filename !== false ) {
			$handle = fopen( $this->filename, "rt" );
			$state = 0;

			while ( !feof($handle) ) {
				$line = fgets($handle);

				if ( $state === 0 ) {
					if ( $line === "\n" ) {
						$state = 1;
						continue;
					}

					$formatPrefix = '# Author: ';

					$prefixLength = strlen($formatPrefix);
					$prefix = substr( $line, 0, $prefixLength );
					if ( strcasecmp( $prefix, $formatPrefix ) === 0 ) {
						// fgets includes the trailing newline, trim to get rid of it
						$authors[] = trim(substr( $line, $prefixLength ));
					}
				} elseif ( $state === 1 ) {
					if ( $line === "\n" || $line[0] !== '#' ) {
						break; // End of static header, if any
					}
					$staticHeader .= $line;
				}
			}

			fclose( $handle );
		}

		$this->authors = $authors;
		$this->staticHeader = $staticHeader;
	}

	/**
	 * Parses messages from lines key=value. Whitespace is trimmer around key and
	 * values. New lines inside values have to be escaped as '\n'. Lines which do
	 * not have = are ignored. Comments are designated by # at the start of the
	 * line only. Values can have = characters, only the first one is considered
	 * separator.
	 */
	public function parseMessages( StringMangler $mangler ) {
		if ( !file_exists( $this->filename ) ) {
			return null;
		}

		$data = file_get_contents( $this->filename );

		$matches = array();
		$regex = '/^\$(.*?)\s*=\s*[\'"](.*?)[\'"];(\s*#.*?)?$/mus';
		preg_match_all( $regex, $data, $matches, PREG_SET_ORDER );
		$messages = array();
		foreach ( $matches as $_ ) {
			$legal = Title::legalChars();
			$key = preg_replace( "/([^$legal]|\\\\)/ue", '\'\x\'.' . "dechex(ord('\\0'))", $_[1] );
			$value = str_replace( array( "\'", "\\\\" ), array( "'", "\\" ), $_[2] );
			$messages[$key] = $value;
		}
		return $messages;
	}
}

/**
 * Very simple writer for exporting messages to PhpVariables property files from wiki.
 */
class PhpVariablesFormatWriter extends SimpleFormatWriter {

	/**
	 * Inherited. Very simplistic header with timestamp.
	 */
	public function makeHeader( $handle, $code ) {
		global $wgSitename;
		list( $name, $native ) = $this->getLanguageNames($code);
		$authors = $this->formatAuthors( '# Author: ', $code );
		$when = wfTimestamp(TS_ISO_8601);

		fwrite( $handle, <<<HEADER
# Messages for $name ($native)
# Exported from $wgSitename at $when
$authors

HEADER
		);
	}

	/**
	 * Inherited. Exports messages as lines of format $key = 'value'.
	 */
	protected function exportMessages( $handle, MessageCollection $collection ) {
		$mangler = $this->group->getMangler();
		foreach ( $collection->keys() as $item ) {
			$value = $collection[$item]->translation;
			if ( $value === null ) continue;

			$key = $mangler->unmangle($item);
			$key = stripcslashes( $key );

			$value = str_replace( TRANSLATE_FUZZY, '', $value );
			$value = addcslashes( $value, "'" );

			# No pretty alignment here, sorry
			fwrite( $handle, "$$key = '$value';\n" );
		}
	}
}
