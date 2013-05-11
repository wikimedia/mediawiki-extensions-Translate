<?php

/**
 * Generic file format support for Python single dictionary formatted files.
 * @ingroup FFS
 */
class PythonSingleFFS extends SimpleFFS {
	public function getFileExtensions() {
		return array( '.py' );
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

		$splitter = 'msg = {';

		$pos = strpos( $data, $splitter );
		if ( $pos === false ) {
			throw new MWException( "MWEFFS1: File $filename: splitter not found" );
		}

		$offset = $pos + strlen( $splitter );
		$header = substr( $data, 0, $offset );
		// Avoid buildup of whitespace
		$header = trim( $header );

		$pattern = '.*?},\s';
		$regexp = "~$pattern~xsu";
		$matches = array();
		preg_match_all( $regexp, $data, $matches, PREG_SET_ORDER, $offset );

		$sections = array();
		$sections[] = $header;

		foreach ( $matches as $data ) {
			$pattern = "'([a-z-]+)'\s*:\s*{";
			$regexp = "~$pattern~su";
			$matches = array();
			if ( !preg_match( $regexp, $data[0], $matches ) ) {
				throw new MWException( "MWEFFS2: File $filename: malformed section: {$data[0]}" );
			}
			$code = $matches[1];
			// Normalize number of newlines
			$sections[$code] = trim( $data[0], "\n" );
		}

		return $sections;
	}

	public function read( $code ) {
		$code = $this->group->mapCode( $code );
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

			self::$cache[$filename]['parsed'] = $this->parseFile();
		}

		if ( !isset( self::$cache[$filename]['parsed'][$code] ) ) {
			return null;
		}

		return self::$cache[$filename]['parsed'][$code];
	}

	protected function parseFile() {
		/* N levels of escaping
		 * - for PHP string
		 * - for Python string
		 * - for shell command
		 * - and wfShellExec will wrap the whole command once more
		 */
		$filename = $this->group->getSourceFilePath( 'mul' );
		$filename = addcslashes( $filename, '\\"' );
		$command = wfEscapeShellArg( "import simplejson as json; execfile(\"$filename\"); print json.dumps(msg)" );
		$json = wfShellExec( "python -c $command" );

		$parsed = FormatJson::decode( $json, true );
		if ( !is_array( $parsed ) ) {
			throw new MWException( "Failed to decode python file $filename" );
		}
		$sections = array();
		foreach ( $parsed as $code => $messages ) {
			$sections[$code] = array( 'MESSAGES' => $messages );
		}

		return $sections;
	}

	public function readFromVariable( $data ) {
		throw new MWException( 'Not yet supported' );
	}

	/**
	 * @param MessageCollection $collection
	 * @return string
	 */
	protected function writeReal( MessageCollection $collection ) {
		$mangler = $this->group->getMangler();
		$code = $collection->getLanguage();
		$code = $this->group->mapCode( $code );

		$block = $this->generateMessageBlock( $collection, $mangler );
		if ( $block === '' ) {
			return '';
		}

		// Ugly code, relies on side effects
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
			$authorList .= "\t# Author: $author\n";
		}

		$section = "$authorList\t'$code': {\n$block\t},";

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

		return implode( "\n", $priority ) . "\n" . implode( "\n", $sections ) . "\n};\n";
	}

	protected function generateMessageBlock( MessageCollection $collection, StringMatcher $mangler ) {
		$block = '';

		/**
		 * @var TMessage $message
		 */
		foreach ( $collection as $message ) {
			$translation = $message->translation();
			if ( $translation === null ) {
				continue;
			}

			$key = addcslashes( $message->key(), "\n'\\" );
			$translation = addcslashes( $translation, "\n'\\" );
			$translation = str_replace( TRANSLATE_FUZZY, '', $translation );

			$block .= "\t\t'{$key}': u'{$translation}',\n";
		}

		return $block;
	}

	/**
	 * Scans for author comments in the string.
	 * @param string $string String containing the comments of a section
	 * @return string[] List of authors
	 */
	protected static function parseAuthorsFromString( $string ) {
		preg_match_all( '/# Author: (.*)/', $string, $m );

		return $m[1];
	}
}
