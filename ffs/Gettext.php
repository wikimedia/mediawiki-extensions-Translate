<?php
if (!defined('MEDIAWIKI')) die();
/**
 * Parses a po file that has been exported from Mediawiki. Other files are not
 * supported.
 */

class GettextFormatHandler {

	public function __construct( $file ) {
		$this->file = $file;
	}

	/**
	 * Loads translations for comparison.
	 *
	 * @param $id Id of MessageGroup.
	 * @param $code Language code.
	 * @return MessageCollection
	 */
	protected function initMessages( $id, $code ) {
		$messages = new MessageCollection( $code );
		$group = MessageGroups::getGroup( $id );

		$definitions = $group->getDefinitions();
		foreach ( $definitions as $key => $definition ) {
			$messages->add( new TMessage( $key, $definition ) );
		}

		$bools = $group->getBools();
		foreach ( $bools['optional'] as $key ) {
			if ( isset($messages[$key]) ) { $messages[$key]->optional = true; }
		}
		foreach ( $bools['ignored'] as $key ) {
			if ( isset($messages[$key]) ) { $messages[$key]->ignored = true; }
		}

		$messages->populatePageExistence();
		$messages->populateTranslationsFromDatabase();
		$group->fill( $messages );

		return $messages;
	}

	/**
	 * Parses relevant stuff from the po file.
	 */
	public function parse() {
		$data = file_get_contents( $this->file );
		$data = str_replace( "\r\n", "\n", $data );

		$matches = array();
		if ( preg_match( '/X-Language-Code:\s+([a-zA-Z-_]+)/', $data, $matches ) ) {
			$code = $matches[1];
			echo "Detected language as $code\n";
		} else {
			echo "Unable to determine language code\n";
			return false;
		}

		if ( preg_match( '/X-Message-Group:\s+([a-zA-Z0-9-_]+)/', $data, $matches ) ) {
			$groupId = $matches[1];
			echo "Detected message group as $groupId\n";
		} else {
			echo "Unable to determine message group\n";
			return false;
		}

		$contents = $this->initMessages( $groupId, $code );

		echo "----\n";

		$poformat = '".*"\n?(^".*"$\n?)*';
		$quotePattern = '/(^"|"$\n?)/m';

		$sections = preg_split( '/\n{2,}/', $data );
		$changes = array();
		foreach ( $sections as $section ) {
			$matches = array();
			if ( preg_match( "/^msgctxt\s($poformat)/mx", $section, $matches ) ) {
				// Remove quoting
				$key = preg_replace( $quotePattern, '', $matches[1] );
				// Ignore unknown keys
				if ( !isset($contents[$key]) ) continue;
			} else {
				continue;
			}
			$matches = array();
			if ( preg_match( "/^msgstr\s($poformat)/mx", $section, $matches ) ) {
				// Remove quoting
				$translation = preg_replace( $quotePattern, '', $matches[1] );
				// Restore new lines and remove quoting
				$translation = stripcslashes( $translation );
			} else {
				continue;
			}

			// Fuzzy messages
			if ( preg_match( '/^#, fuzzy$/m', $section ) ) {
				$translation = TRANSLATE_FUZZY . $translation;
			}

			if ( $translation !== (string) $contents[$key]->translation ) {
				echo "Translation of $key differs:\n$translation\n\n";
				$changes["$key/$code"] = $translation;
			}

		}

		return $changes;

	}

	// Inherited: Stored objects
	protected $info;

	public function makeHeaderTo( $handle ) {
		$name = $this->info->getOption( 'languagename' );
		$native = $this->info->getOption( 'nativename' );
		$authors = $this->_formatAuthors( $this->info->getOption( 'authors' ) );

		fwrite( $handle, <<<HEADER
# Messages for $name ($native)
# Exported from XYZ
$authors

HEADER
		);
	}

	protected function _formatAuthors( array $authors ) {
		$s = array();
		foreach ( $authors as $a ) {
			$s[] = "# Author: $a";
		}
		return "\n" . implode( "\n", $s ) . "\n";
	}

	protected function _exportItem( $handle, $key, $value ) {
		$prefix = $this->info->getOption( 'exportprefix' );
		if ( $prefix ) fwrite( $handle, $prefix );

		# Add the key name
		fwrite( $handle, "'$key'" );
		# Add the appropriate block whitespace
		fwrite( $handle, str_repeat( ' ', $this->padTo - strlen($key) ) );
		fwrite( $handle, ' => ' );

		# Check for the appropriate apostrophe and add the value
		# Quote \ here, because it needs always escaping
		$value = addcslashes( $value, '\\' );

		# For readability
		$single = "'";
		$double = '"';
		$quote = $single;

		# It is safe to use '-quoting, unless there is '-quote in the text
		if( strpos( $value, $single ) !== false ) {

			# In case there is no variables that need to be escaped, just use "-quote
			if( strpos( $value, $double ) === false && !preg_match('/\$[^0-9]/', $value) ) {
				$quote = $double;

			# Something needs quoting, pick the quote which causes less quoting
			} else {
				$doubleEsc = substr_count( $value, $double ) + substr_count( $value, '$' );
				$singleEsc = substr_count( $value, $single );

				if ( $doubleEsc < $singleEsc ) {
					$quote = $double;
					$extra = '$';
				} else {
					$extra = '';
				}

				$value = addcslashes( $value, $quote . $extra );
			}
		}

		fwrite( $handle, $quote . $value . $quote );
		fwrite( $handle, ",\n" );
	}

	public function parseMessages( $filename ) {
		$messages = array();
		require( $filename );
		return $messages;
	}
}
