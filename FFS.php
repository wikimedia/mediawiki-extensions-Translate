<?php

interface FFS {
	public function __construct( FileBasedMessageGroup $group );

	// The file system location
	public function setWritePath( $target );
	public function getWritePath();

	// Will parse messages, authors, and any custom data from file specified in
	// $group and return it in associative array with keys like AUTHORS and
	// MESSAGES.
	public function read( $code );

	public function readFromVariable( $data );

	// Writes to the location provided in $group, exporting translations included
	// in collection with any special handling needed.
	public function write( MessageCollection $collection );

	// Quick shortcut for getting the plain exported data
	public function writeIntoVariable( MessageCollection $collection );

}

class SimpleFFS implements FFS {
	protected $group;
	protected $writePath;
	protected $extra;

	public function __construct( FileBasedMessageGroup $group ) {
		$this->setGroup( $group );
		$conf = $group->getConfiguration();
		$this->extra = $conf['FILES'];
	}

	public function setGroup( FileBasedMessageGroup $group ) { $this->group = $group; }
	public function getGroup() { return $this->group; }

	public function setWritePath( $writePath ) { $this->writePath = $writePath; }
	public function getWritePath() { return $this->writePath; }

	public function read( $code ) {
		$filename = $this->group->getSourceFilePath( $code );
		if ( $filename === null ) return array();

		$input = file_get_contents( $filename );
		if ( $input === false ) throw new MWException( "Unable to read file $filename" );

		return $this->readFromVariable( $input );
	}

	public function readFromVariable( $data ) {
		$parts = explode( "\0\0\0\0", $data );

		if ( count($parts) !== 2 ) throw new MWException( 'Wrong number of parts' );

		list( $authorsPart, $messagesPart ) = $parts;
		$authors = explode( "\0", $authorsPart );
		$messages = array();
		foreach ( explode( "\0", $messagesPart ) as $line ) {
			if ( $line === '' ) continue;
			$lineParts = explode( '=', $line, 2 );

			if ( count($lineParts) !== 2 ) throw new MWException( "Wrong number of parts in line $line" );

			list( $key, $message ) = $lineParts;
			$messages[$key] = $message;
		}

		$messages = $this->group->getMangler()->mangle( $messages );

		return array(
			'AUTHORS' => $authors,
			'MESSAGES' => $messages,
		);
	}

	public function write( MessageCollection $collection ) {
		$writePath = $this->writePath;

		if ( $writePath === null ) throw new MWException( "Write path is not set" );
		if ( !file_exists($writePath) ) throw new MWException( "Write path '$writePath' does not exists" );
		if ( !is_writable($writePath) ) throw new MWException( "Write path '$writePath' is not writable" );

		$targetFile = $writePath . '/' . $this->group->getTargetFilename( $code );

		if ( !file_exists($targetFile) ) {
			// May be null
			$sourceFile = $this->group->geSourceFilePath( $code );
		} else {
			$sourceFile = $targetFile;
		}

		$sourceText = $this->tryReadFile( $sourceFile );
		if ( $sourceText !== false ) {
			$sourceData = $this->readFromVariable( $sourceText );
			if ( isset( $sourceData['AUTHORS'] ) ) {
				$collection->addCollectionAuthors( $sourceData['AUTHORS'] );
			}
		}

		$output = $this->writeIntoVariable( $collection );
		file_put_contents( $filename, $output );
	}

	public function writeIntoVariable( MessageCollection $collection ) {
		$output = '';

		$authors = $collection->getAuthors();
		$authors = $this->filterAuthors( $authors, $collection->code );
		$output .= implode( "\0", $authors );
		$output .= "\0\0\0\0";

		$mangler = $this->group->getMangler();
		foreach ( $collection as $key => $m ) {
			$key = $mangler->unmangle( $key );
			$trans = $m->translation();
			$output .= "$key=$trans\0";
		}

		return $output;
	}

	protected function tryReadFile( $filename ) {
		if ( !$filename ) return false;
		if ( !file_exists($filename) ) return false;
		if ( !is_readable($filename) ) throw new MWException( "File $filename is not readable" );
		$data = file_get_contents($filename);
		if ( $data == false ) throw new MWException( "Unable to read file $filename" );
		return $data;
	}

	protected function filterAuthors( array $authors, $code ) {
		global $wgTranslateAuthorBlacklist;
		$groupId = $this->group->getId();
		foreach ( $authors as $i => $v ) {
			$hash = "$groupId;$code;$v";

			$blacklisted = false;
			foreach ( $wgTranslateAuthorBlacklist as $rule ) {
				list( $type, $regex ) = $rule;
				if ( preg_match( $regex, $hash ) ) {
					if ( $type === 'white' ) {
						$blacklisted = false;
						break;
					} else {
						$blacklisted = true;
					}
				}
			}
			if ( $blacklisted ) unset( $authors[$i] );
		}

		return $authors;
	}

	public static function fixNewLines( $data ) {
		$data = str_replace( "\r\n", "\n", $data );
		$data = str_replace( "\r", "\n", $data );
		return $data;
	}
}

class JavaFFS extends SimpleFFS {

	//
	// READ
	//

	public function readFromVariable( $data ) {
		$data = self::fixNewLines( $data );
		$lines = array_map( 'ltrim', explode( "\n", $data ) );
		$authors = $messages = array();
		
		foreach ( $lines as $line ) {
			if ( $line === '' ) continue;
			if ( $line[0] === '#' ) {
				$match = array();
				$ok = preg_match( '/#\s*Author:\s*(.*)/', $line, $match );
				if ( $ok ) $authors[] = $match[1];
				continue;
			}

			if ( strpos( $line, '=' ) === false ) {
				throw new MWException( "Line without '=': $line" );
			}

			list( $key, $value ) = explode( '=', $line, 2 );
			if ( $key === '' ) throw new MWException( "Empty key in line $line" );

			$value = str_replace( '\n', "\n", $value );

			$messages[$key] = $value;
		}

		$messages = $this->group->getMangler()->mangle( $messages );

		return array(
			'AUTHORS' => $authors,
			'MESSAGES' => $messages,
		);
	}

	//
	// WRITE
	//

	public function writeIntoVariable( MessageCollection $collection ) {
		$output  = $this->doHeader( $collection );
		$output .= $this->doAuthors( $collection );

		$mangler = $this->group->getMangler();
		foreach ( $collection as $key => $m ) {
			$key = $mangler->unmangle( $key );
			$value = $m->translation();
			$value = str_replace( TRANSLATE_FUZZY, '', $value );

			if ( $value === '' ) continue;

			# Make sure we don't slip newlines trough... it would be fatal
			$value = str_replace( "\n", '\\n', $value );
			# Just to give an overview of translation quality
			if ( $m->hasTag( 'fuzzy' ) ) $output .= "# Fuzzy\n";
			$output .= "$key=$value\n";
		}
		return $output;
	}

	protected function doHeader( MessageCollection $collection ) {
		$code = $collection->code;
		$name = TranslateUtils::getLanguageName( $code );
		$native = TranslateUtils::getLanguageName( $code, true );
		$output  = "# Messages for $name ($native)\n";
		if ( isset($this->extra['header']) ) {
			$output .= $this->extra['header'];
		}
		return $output;
	}

	protected function doAuthors( MessageCollection $collection ) {
		$output = '';
		$authors = $collection->getAuthors();
		$authors = $this->filterAuthors( $authors, $collection->code );
		foreach ( $authors as $author ) {
			$output .= "# $author\n";
		}
		return $output;
	}

}