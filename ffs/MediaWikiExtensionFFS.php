<?php
/**
 * Support for the ugly file format that is used by MediaWiki extensions.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2012-2013, Niklas Laxström
 * @license GPL-2.0+
 */

/**
 * Manipulates ExtensionName.i18n.php style files.
 *
 * @ingroup FFS
 * @since 2012-10-20
 */
class MediaWikiExtensionFFS extends SimpleFFS {
	public function supportsFuzzy() {
		return 'write';
	}

	public function getFileExtensions() {
		return array( '.i18n.php' );
	}

	/**
	 * To avoid parsing full files again and again when reading or exporting
	 * multiple languages, keep cache of the sections of the latest active file.
	 * @var array
	 */
	protected static $cache = array();

	/**
	 * @param string $data Full file contents
	 * @param string $filename Full path to file for debugging
	 * @return string[] Sections indexed by language code, or 0 for header section
	 * @throws MWException
	 */
	protected function splitSections( $data, $filename = 'unknown' ) {
		$data = SimpleFFS::fixNewLines( $data );

		$splitter = '$messages = array();';

		$pos = strpos( $data, $splitter );
		if ( $pos === false ) {
			throw new MWException( "MWEFFS1: File $filename: splitter not found" );
		}

		$offset = $pos + strlen( $splitter );
		$header = substr( $data, 0, $offset );

		$pattern = '(?: /\*\* .*? \*/ \n )? (?: \\$.*?  \n\);(?:\n\n|\s+\z) )';
		$regexp = "~$pattern~xsu";
		$matches = array();
		preg_match_all( $regexp, $data, $matches, PREG_SET_ORDER, $offset );

		$sections = array();
		$sections[] = $header;

		foreach ( $matches as $data ) {
			$pattern = "\\\$messages\['([a-z-]+)'\]";
			$regexp = "~$pattern~su";
			$matches = array();
			if ( !preg_match( $regexp, $data[0], $matches ) ) {
				throw new MWException( "MWEFFS2: File $filename: malformed section: {$data[0]}" );
			}
			$code = $matches[1];
			// Normalize number of newlines after each section
			$sections[$code] = rtrim( $data[0] );
		}

		return $sections;
	}

	/**
	 * @param string $code Language code.
	 * @return array|bool
	 */
	public function read( $code ) {
		$filename = $this->group->getSourceFilePath( $code );
		if ( !file_exists( $filename ) ) {
			return false;
		}

		if ( isset( self::$cache[$filename]['parsed'][$code] ) ) {
			return self::$cache[$filename]['parsed'][$code];
		}

		if ( !isset( self::$cache[$filename] ) ) {
			// Clear the cache if the filename changes to reduce memory use
			self::$cache = array();

			$contents = file_get_contents( $filename );
			self::$cache[$filename]['sections'] =
				$this->splitSections( $contents, $filename );
		}

		// Shorten
		$cache = &self::$cache[$filename];

		$value = false;
		if ( isset( $cache['sections'][$code] ) ) {
			$value = $this->readFromVariable( $cache['sections'][$code] );
		}

		$cache['parsed'][$code] = $value;

		return $value;
	}

	/**
	 * @param string $data
	 * @return array Parsed data.
	 * @throws MWException
	 */
	public function readFromVariable( $data ) {
		$messages = array();
		eval( $data );

		$c = count( $messages );
		if ( $c !== 1 ) {
			throw new MWException( "MWEFFS3: Expected 1, got $c: $data" );
		}

		$messages = array_shift( $messages );
		$mangler = $this->group->getMangler();
		$messages = $mangler->mangle( $messages );

		return array(
			'MESSAGES' => $messages,
		);
	}

	// Handled in writeReal
	protected function tryReadSource( $filename, MessageCollection $collection ) {
	}

	/**
	 * @param MessageCollection $collection
	 * @return string
	 */
	protected function writeReal( MessageCollection $collection ) {
		$mangler = $this->group->getMangler();
		$code = $collection->getLanguage();

		$block = $this->generateMessageBlock( $collection, $mangler );
		if ( $block === false ) {
			return '';
		}

		// Ugly code, relies on side effects
		// Avoid parsing stuff with fake language code
		// Premature optimization
		$this->read( 'mul' );
		$filename = $this->group->getSourceFilePath( $code );
		$cache = &self::$cache[$filename];

		// Generating authors
		if ( isset( $cache['sections'][$code] ) ) {
			// More premature optimization
			$fromFile = self::parseAuthorsFromString( $cache['sections'][$code] );
			$collection->addCollectionAuthors( $fromFile );
		}

		$authors = $collection->getAuthors();
		$authors = $this->filterAuthors( $authors, $code );

		$authorList = '';
		foreach ( $authors as $author ) {
			$authorList .= "\n * @author $author";
		}

		// And putting all together
		$name = TranslateUtils::getLanguageName( $code );
		$native = TranslateUtils::getLanguageName( $code, $code );

		$section = <<<PHP
/** $name ($native)$authorList
 */
\$messages['$code'] = array($block);
PHP;

		// Store the written part, so that when next language is called,
		// the new version will be used (instead of the old parsed version
		$cache['sections'][$code] = $section;

		// Make a copy we can alter
		$sections = $cache['sections'];
		$priority = array();

		global $wgTranslateDocumentationLanguageCode;
		$codes = array(
			0, // File header
			$this->group->getSourceLanguage(),
			$wgTranslateDocumentationLanguageCode,
		);
		foreach ( $codes as $pcode ) {
			if ( isset( $sections[$pcode] ) ) {
				$priority[] = $sections[$pcode];
				unset( $sections[$pcode] );
			}
		}

		ksort( $sections );

		return implode( "\n\n", $priority ) . "\n\n" . implode( "\n\n", $sections ) . "\n";
	}

	protected function generateMessageBlock( MessageCollection $collection, StringMatcher $mangler ) {
		$block = '';
		/**
		 * @var TMessage $m
		 */
		foreach ( $collection as $key => $m ) {
			$value = $m->translation();
			if ( $value === null ) {
				continue;
			}

			$key = $mangler->unmangle( $key );
			$value = str_replace( TRANSLATE_FUZZY, '', $value );
			$fuzzy = $m->hasTag( 'fuzzy' ) ? ' # Fuzzy' : '';

			$key = self::quote( $key );
			$value = self::quote( $value );
			$block .= "\t$key => $value,$fuzzy\n";
		}

		// Do not create empty sections
		if ( $block === '' ) {
			return false;
		}

		return "\n$block";
	}

	/**
	 * Scans for \@author tags in the string.
	 * @param string $string String containing the comments of a section
	 * @return string[] List of authors
	 */
	protected static function parseAuthorsFromString( $string ) {
		preg_match_all( '/@author (.*)/', $string, $m );

		return $m[1];
	}

	/**
	 * Tries to find optimal way to quote a string by choosing
	 * either double quotes or single quotes depending on how
	 * many escapes are needed.
	 * @param string $value The string to quote.
	 * @return string String suitable for inclusion in PHP code
	 */
	protected static function quote( $value ) {
		# Check for the appropriate apostrophe and add the value
		# Quote \ here, because it needs always escaping
		$value = addcslashes( $value, '\\' );

		# For readability
		$single = "'";
		$double = '"';
		$quote = $single; // Default

		# It is safe to use '-quoting, unless there is '-quote in the text
		if ( strpos( $value, $single ) !== false ) {
			# In case there are no variables that need to be escaped, just use "-quote
			if ( strpos( $value, $double ) === false && !preg_match( '/\$[^0-9]/', $value ) ) {
				$quote = $double;
			} else {
				# Something needs quoting, so pick the quote which causes less quoting
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

		return $quote . $value . $quote;
	}
}
