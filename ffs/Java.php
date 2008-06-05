<?php
if (!defined('MEDIAWIKI')) die();
/**
 * Java properties file format handler.
 *
 * @author Niklas Laxström
 * @copyright Copyright © 2008, Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 * @file
 */

class JavaFormatReader extends SimpleFormatReader {
	protected function parseHeader() {
		if ( $this->filename === false ) {
			return;
		}
		$authors = array();
		$staticHeader = '';

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

		$this->authors = $authors;
		$this->staticHeader = $staticHeader;
	}

	public function parseMessages( StringMangler $mangler ) {
		if ( !file_exists( $this->filename ) ) {
			return null;
		}

		$lines = file( $this->filename );
		if ( !$lines ) { return null; }

		$messages = array();

		foreach ( $lines as $line ) {
			if ( !strpos( $line, '=' ) ) { continue; }
			list( $key, $value ) = explode( '=', $line, 2 );
			$messages[$mangler->mangle($key)] = trim($value);
		}
		return $messages;
	}
}


class JavaFormatWriter extends SimpleFormatWriter {

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

	protected function exportMessages( $handle, array $messages ) {
		foreach ( $messages as $key => $value ) {
			$value = str_replace( "\n", '\\n', $value );
			fwrite( $handle, "$key=$value\n" );
		}
	}

}