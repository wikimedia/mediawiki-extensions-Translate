<?php
/**
 * File format support classes.
 *
 * @file
 * @author Niklas Laxström
 */

/**
 * A very basic FFS module that implements some basic functionality and
 * a simple binary based file format.
 * Other FFS classes can extend SimpleFFS and override suitable methods.
 * @ingroup FFS
 */
class SimpleFFS implements FFS {
	public function supportsFuzzy() {
		return 'no';
	}

	public function getFileExtensions() {
		return array();
	}

	/**
	 * @var FileBasedMessageGroup
	 */
	protected $group;

	protected $writePath;

	/**
	 * Stores the FILES section of the YAML configuration,
	 * which can be accessed for extra FFS class specific options.
	 */
	protected $extra;

	const RECORD_SEPARATOR = "\0";
	const PART_SEPARATOR = "\0\0\0\0";

	public function __construct( FileBasedMessageGroup $group ) {
		$this->setGroup( $group );
		$conf = $group->getConfiguration();
		$this->extra = $conf['FILES'];
	}

	/**
	 * @param FileBasedMessageGroup $group
	 */
	public function setGroup( FileBasedMessageGroup $group ) {
		$this->group = $group;
	}

	/**
	 * @return FileBasedMessageGroup
	 */
	public function getGroup() {
		return $this->group;
	}

	/**
	 * @param string $writePath
	 */
	public function setWritePath( $writePath ) {
		$this->writePath = $writePath;
	}

	/**
	 * @return string
	 */
	public function getWritePath() {
		return $this->writePath;
	}

	/**
	 * Returns true if the file for this message group in a given language
	 * exists. If no $code is given, the groups source language is assumed.
	 * NB: Some formats store all languages in the same file, and then this
	 * function will return true even if there are no translations to that
	 * language.
	 *
	 * @param string|bool $code
	 * @return bool
	 */
	public function exists( $code = false ) {
		if ( $code === false ) {
			$code = $this->group->getSourceLanguage();
		}

		$filename = $this->group->getSourceFilePath( $code );
		if ( $filename === null ) {
			return false;
		}

		return file_exists( $filename );
	}

	/**
	 * Reads messages from the file in a given language and returns an array
	 * of AUTHORS, MESSAGES and possibly other properties.
	 *
	 * @param string $code Language code.
	 * @return array|bool False if the file does not exist
	 * @throws MWException if the file is not readable or has bad encoding
	 */
	public function read( $code ) {
		if ( !$this->exists( $code ) ) {
			return false;
		}

		$filename = $this->group->getSourceFilePath( $code );
		$input = file_get_contents( $filename );
		if ( $input === false ) {
			throw new MWException( "Unable to read file $filename." );
		}

		if ( !StringUtils::isUtf8( $input ) ) {
			throw new MWException( "Contents of $filename are not valid utf-8." );
		}

		$input = UtfNormal::cleanUp( $input );

		try {
			return $this->readFromVariable( $input );
		} catch ( Exception $e ) {
			throw new MWException( "Parsing $filename failed: " . $e->getMessage() );
		}
	}

	/**
	 * Parse the message data given as a string in the SimpleFFS format
	 * and return it as an array of AUTHORS and MESSAGES.
	 *
	 * @param string $data
	 * @return array Parsed data.
	 * @throws MWException
	 */
	public function readFromVariable( $data ) {
		$parts = explode( self::PART_SEPARATOR, $data );

		if ( count( $parts ) !== 2 ) {
			throw new MWException( 'Wrong number of parts.' );
		}

		list( $authorsPart, $messagesPart ) = $parts;
		$authors = explode( self::RECORD_SEPARATOR, $authorsPart );
		$messages = array();

		foreach ( explode( self::RECORD_SEPARATOR, $messagesPart ) as $line ) {
			if ( $line === '' ) {
				continue;
			}

			$lineParts = explode( '=', $line, 2 );

			if ( count( $lineParts ) !== 2 ) {
				throw new MWException( "Wrong number of parts in line $line." );
			}

			list( $key, $message ) = $lineParts;
			$key = trim( $key );
			$messages[$key] = $message;
		}

		$messages = $this->group->getMangler()->mangle( $messages );

		return array(
			'AUTHORS' => $authors,
			'MESSAGES' => $messages,
		);
	}

	/**
	 * Write the collection to file.
	 *
	 * @param MessageCollection $collection
	 * @throws MWException
	 */
	public function write( MessageCollection $collection ) {
		$writePath = $this->writePath;

		if ( $writePath === null ) {
			throw new MWException( 'Write path is not set.' );
		}

		if ( !file_exists( $writePath ) ) {
			throw new MWException( "Write path '$writePath' does not exist." );
		}

		if ( !is_writable( $writePath ) ) {
			throw new MWException( "Write path '$writePath' is not writable." );
		}

		$targetFile = $writePath . '/' . $this->group->getTargetFilename( $collection->code );

		if ( file_exists( $targetFile ) ) {
			$this->tryReadSource( $targetFile, $collection );
		} else {
			$sourceFile = $this->group->getSourceFilePath( $collection->code );
			$this->tryReadSource( $sourceFile, $collection );
		}

		$output = $this->writeReal( $collection );
		if ( $output ) {
			wfMkdirParents( dirname( $targetFile ), null, __METHOD__ );
			file_put_contents( $targetFile, $output );
		}
	}

	/**
	 * Read a collection and return it as a SimpleFFS formatted string.
	 *
	 * @param MessageCollection $collection
	 * @return string
	 */
	public function writeIntoVariable( MessageCollection $collection ) {
		$sourceFile = $this->group->getSourceFilePath( $collection->code );
		$this->tryReadSource( $sourceFile, $collection );

		return $this->writeReal( $collection );
	}

	/**
	 * @param MessageCollection $collection
	 * @return string
	 */
	protected function writeReal( MessageCollection $collection ) {
		$output = '';

		$authors = $collection->getAuthors();
		$authors = $this->filterAuthors( $authors, $collection->code );

		$output .= implode( self::RECORD_SEPARATOR, $authors );
		$output .= self::PART_SEPARATOR;

		$mangler = $this->group->getMangler();

		/** @var TMessage $m */
		foreach ( $collection as $key => $m ) {
			$key = $mangler->unmangle( $key );
			$trans = $m->translation();
			$output .= "$key=$trans" . self::RECORD_SEPARATOR;
		}

		return $output;
	}

	/**
	 * This tries to pick up external authors in the source files so that they
	 * are not lost if those authors are not among those who have translated in
	 * the wiki.
	 *
	 * @todo Get rid of this
	 * @param string $filename
	 * @param MessageCollection $collection
	 */
	protected function tryReadSource( $filename, MessageCollection $collection ) {
		if ( get_class( $this->group->getFFS() ) !== get_class( $this ) ) {
			return;
		}

		$sourceText = $this->tryReadFile( $filename );

		// No need to do anything in SimpleFFS if it's false,
		// it only reads author data from it.
		if ( $sourceText !== false ) {
			$sourceData = $this->readFromVariable( $sourceText );

			if ( isset( $sourceData['AUTHORS'] ) ) {
				$collection->addCollectionAuthors( $sourceData['AUTHORS'] );
			}
		}
	}

	/**
	 * Read the contents of $filename and return it as a string.
	 * Return false if the file doesn't exist.
	 * Throw an exception if the file isn't readable
	 * or if the reading fails strangely.
	 *
	 * @param string $filename
	 * @return bool|string
	 * @throws MWException
	 */
	protected function tryReadFile( $filename ) {
		if ( !$filename ) {
			return false;
		}

		if ( !file_exists( $filename ) ) {
			return false;
		}

		if ( !is_readable( $filename ) ) {
			throw new MWException( "File $filename is not readable." );
		}

		$data = file_get_contents( $filename );
		if ( $data === false ) {
			throw new MWException( "Unable to read file $filename." );
		}

		return $data;
	}

	/**
	 * Remove blacklisted authors.
	 *
	 * @param array $authors
	 * @param string $code
	 * @return array
	 */
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

			if ( $blacklisted ) {
				unset( $authors[$i] );
			}
		}

		return $authors;
	}

	/**
	 * Replaces all Windows and Mac line endings with Unix line endings.
	 * This is needed in some file types.
	 *
	 * @param string $data
	 * @return string
	 */
	public static function fixNewLines( $data ) {
		$data = str_replace( "\r\n", "\n", $data );
		$data = str_replace( "\r", "\n", $data );

		return $data;
	}
}
