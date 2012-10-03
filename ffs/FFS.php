<?php
/**
 * File format support classes.
 *
 * These classes handle parsing and generating various different
 * file formats where translation messages are stored.
 *
 * @file
 * @defgroup FFS File format support
 * @author Niklas Laxström
 * @copyright Copyright © 2008-2012, Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

/**
 * Interface for file system support classes.
 * @ingroup FFS
 */
interface FFS {
	public function __construct( FileBasedMessageGroup $group );

	/**
	 * Set the file's location in the system
     * @param $target \string Filesystem path for exported files.
	 */
	public function setWritePath( $target );

	/**
	 * Get the file's location in the system
	 * @return \string
	 */
	public function getWritePath();

	/**
	 * Will parse messages, authors, and any custom data from the file
	 * and return it in associative array with keys like \c AUTHORS and
	 * \c MESSAGES.
	 * @param $code \string Languge code.
	 * @return array of string|mixed Parsed data.
	 */
	public function read( $code );

	/**
	 * Same as read(), but takes the data as a parameter. The caller
	 * is supposed to know in what language the translations are.
	 * @param $data \string Formatted messages.
	 * @return array of string|mixed Parsed data.
	 */
	public function readFromVariable( $data );

	/**
	 * Writes to the location provided with setWritePath and group specific
	 * directory structure. Exports translations included in the given
	 * collection with any special handling needed.
	 * @param $collection MessageCollection
	 */
	public function write( MessageCollection $collection );

	/**
	 * Quick shortcut for getting the plain exported data.
	 * Same as write(), but returns the output instead of writing it into
	 * a file.
	 * @param $collection MessageCollection
	 * @return \string
	 */
	public function writeIntoVariable( MessageCollection $collection );
}

/**
 * A very basic FFS module that implements some basic functionality and
 * a simple binary based file format.
 * Other FFS classes can extend SimpleFFS and override suitable methods.
 * @ingroup FFS
 */
class SimpleFFS implements FFS {

	/**
	 * @var FileBasedMessageGroup
	 */
	protected $group;

	protected $writePath;

    /**
     * @todo doc!
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
	 * @param $group FileBasedMessageGroup
	 */
	public function setGroup( FileBasedMessageGroup $group ) { $this->group = $group; }

	/**
	 * @return FileBasedMessageGroup
	 */
	public function getGroup() { return $this->group; }

	/**
	 * @param $writePath string
	 */
	public function setWritePath( $writePath ) { $this->writePath = $writePath; }

	/**
	 * @return string
	 */
	public function getWritePath() { return $this->writePath; }

	/**
     * Returns true if the file for this message group in language $code
     * exists. If no $code is given, the groups source language is assumed.
     *
	 * @param $code string|bool
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

		if ( !file_exists( $filename ) ) {
			return false;
		}

		return true;
	}

	/**
     * Reads messages from the file in language $code and returns an array
     * of AUTHORS and MESSAGES.
     * Returns false if the file doesn't exist.
     * Throws an exception if the file appears to exist, but cannot be read.
     *
	 * @param $code string
	 * @return array|bool
	 * @throws MWException
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

		return $this->readFromVariable( $input );
	}

	/**
     * Parse the message data given as a string in the SimpleFFS format
     * and return it as an array of AUTHORS and MESSAGES.
     *
	 * @param $data string
	 * @return array
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
	 * @param $collection MessageCollection
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
     * Read a collection from a file, and return it as
	 * a SimpleFFS formatted string.
     *
	 * @param $collection MessageCollection
	 * @return string
	 */
	public function writeIntoVariable( MessageCollection $collection ) {
		$sourceFile = $this->group->getSourceFilePath( $collection->code );
		$this->tryReadSource( $sourceFile, $collection );

		return $this->writeReal( $collection );
	}

	/**
	 * @param $collection MessageCollection
	 * @return string
	 */
	protected function writeReal( MessageCollection $collection ) {
		$output = '';

		$authors = $collection->getAuthors();
		$authors = $this->filterAuthors( $authors, $collection->code );

		$output .= implode( self::RECORD_SEPARATOR, $authors );
		$output .= self::PART_SEPARATOR;

		$mangler = $this->group->getMangler();

		foreach ( $collection as $key => $m ) {
			$key = $mangler->unmangle( $key );
			$trans = $m->translation();
			$output .= "$key=$trans" . self::RECORD_SEPARATOR;
		}

		return $output;
	}

	/**
	 * @param $filename string
	 * @param $collection MessageCollection
	 */
	protected function tryReadSource( $filename, $collection ) {
		$sourceText = $this->tryReadFile( $filename );

        // XXX And if it is false?
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
	 * @param $filename string
	 * @return bool|string
	 * @throws MWException
	 */
	protected function tryReadFile( $filename ) { // XXX maybe $filename = false?
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
	 * @param $authors array
	 * @param $code string
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
	 * @param $data string
	 * @return string
	 */
	public static function fixNewLines( $data ) {
		$data = str_replace( "\r\n", "\n", $data );
		$data = str_replace( "\r", "\n", $data );

		return $data;
	}
}
