<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\FileFormatSupport;

use Exception;
use FileBasedMessageGroup;
use InvalidArgumentException;
use LogicException;
use MediaWiki\Extension\Translate\MessageLoading\Message;
use MediaWiki\Extension\Translate\MessageLoading\MessageCollection;
use MediaWiki\Extension\Translate\Services;
use RuntimeException;
use StringUtils;
use UtfNormal\Validator;

/**
 * A very basic FileFormatSupport module that implements some basic functionality and
 * a simple binary based file format. Other FFS classes can extend SimpleFormat and
 * override suitable methods.
 * @ingroup FileFormatSupport
 * @author Niklas Laxström
 */
class SimpleFormat implements FileFormatSupport {

	public function supportsFuzzy(): string {
		return 'no';
	}

	public function getFileExtensions(): array {
		return [];
	}

	protected FileBasedMessageGroup $group;
	protected ?string $writePath = null;
	/**
	 * Stores the FILES section of the YAML configuration,
	 * which can be accessed for extra FFS class specific options.
	 * @var mixed
	 */
	protected $extra;

	private const RECORD_SEPARATOR = "\0";
	private const PART_SEPARATOR = "\0\0\0\0";

	public function __construct( FileBasedMessageGroup $group ) {
		$this->setGroup( $group );
		$conf = $group->getConfiguration();
		$this->extra = $conf['FILES'];
	}

	public function setGroup( FileBasedMessageGroup $group ): void {
		$this->group = $group;
	}

	public function getGroup(): FileBasedMessageGroup {
		return $this->group;
	}

	public function setWritePath( string $target ): void {
		$this->writePath = $target;
	}

	public function getWritePath(): string {
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
	 */
	public function exists( $code = false ): bool {
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
	 * @return array|bool False if the file does not exist
	 * @throws RuntimeException if the file is not readable or has bad encoding
	 */
	public function read( string $languageCode ) {
		if ( !$this->isGroupFfsReadable() ) {
			return [];
		}

		if ( !$this->exists( $languageCode ) ) {
			return false;
		}

		$filename = $this->group->getSourceFilePath( $languageCode );
		$input = file_get_contents( $filename );
		if ( $input === false ) {
			throw new RuntimeException( "Unable to read file $filename." );
		}

		if ( !StringUtils::isUtf8( $input ) ) {
			throw new RuntimeException( "Contents of $filename are not valid utf-8." );
		}

		$input = Validator::cleanUp( $input );

		// Strip BOM mark
		$input = ltrim( $input, "\u{FEFF}" );

		try {
			return $this->readFromVariable( $input );
		} catch ( Exception $e ) {
			throw new RuntimeException( "Parsing $filename failed: " . $e->getMessage() );
		}
	}

	/**
	 * Parse the message data given as a string in the SimpleFormat format
	 * and return it as an array of AUTHORS and MESSAGES.
	 *
	 * @throws InvalidArgumentException
	 */
	public function readFromVariable( string $data ): array {
		$parts = explode( self::PART_SEPARATOR, $data );

		if ( count( $parts ) !== 2 ) {
			throw new InvalidArgumentException( 'Wrong number of parts.' );
		}

		[ $authorsPart, $messagesPart ] = $parts;
		$authors = explode( self::RECORD_SEPARATOR, $authorsPart );
		$messages = [];

		foreach ( explode( self::RECORD_SEPARATOR, $messagesPart ) as $line ) {
			if ( $line === '' ) {
				continue;
			}

			$lineParts = explode( '=', $line, 2 );

			if ( count( $lineParts ) !== 2 ) {
				throw new InvalidArgumentException( "Wrong number of parts in line $line." );
			}

			[ $key, $message ] = $lineParts;
			$key = trim( $key );
			$messages[$key] = $message;
		}

		$messages = $this->group->getMangler()->mangleArray( $messages );

		return [
			'AUTHORS' => $authors,
			'MESSAGES' => $messages,
		];
	}

	/** Write the collection to file. */
	public function write( MessageCollection $collection ): void {
		$writePath = $this->writePath;

		if ( $writePath === null ) {
			throw new LogicException( 'Write path is not set. Set write path before calling write()' );
		}

		if ( !file_exists( $writePath ) ) {
			throw new InvalidArgumentException( "Write path '$writePath' does not exist." );
		}

		if ( !is_writable( $writePath ) ) {
			throw new InvalidArgumentException( "Write path '$writePath' is not writable." );
		}

		$targetFile = $writePath . '/' . $this->group->getTargetFilename( $collection->code );

		$targetFileExists = file_exists( $targetFile );

		if ( $targetFileExists ) {
			$this->tryReadSource( $targetFile, $collection );
		} else {
			$sourceFile = $this->group->getSourceFilePath( $collection->code );
			$this->tryReadSource( $sourceFile, $collection );
		}

		$output = $this->writeReal( $collection );
		if ( !$output ) {
			return;
		}

		// Some file formats might have changing parts, such as timestamp.
		// This allows the file handler to skip updating files, where only
		// the timestamp would change.
		if ( $targetFileExists ) {
			$oldContent = $this->tryReadFile( $targetFile );
			if ( $oldContent === null || !$this->shouldOverwrite( $oldContent, $output ) ) {
				return;
			}
		}

		wfMkdirParents( dirname( $targetFile ), null, __METHOD__ );
		file_put_contents( $targetFile, $output );
	}

	/** Read a collection and return it as a SimpleFormat formatted string. */
	public function writeIntoVariable( MessageCollection $collection ): string {
		$sourceFile = $this->group->getSourceFilePath( $collection->code );
		$this->tryReadSource( $sourceFile, $collection );

		return $this->writeReal( $collection );
	}

	protected function writeReal( MessageCollection $collection ): string {
		$output = '';

		$authors = $collection->getAuthors();
		$authors = $this->filterAuthors( $authors, $collection->code );

		$output .= implode( self::RECORD_SEPARATOR, $authors );
		$output .= self::PART_SEPARATOR;

		$mangler = $this->group->getMangler();

		/** @var Message $m */
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
	 */
	protected function tryReadSource( string $filename, MessageCollection $collection ): void {
		if ( !$this->isGroupFfsReadable() ) {
			return;
		}

		$sourceText = $this->tryReadFile( $filename );

		// No need to do anything in SimpleFormat if it's null,
		// it only reads author data from it.
		if ( $sourceText !== null ) {
			$sourceData = $this->readFromVariable( $sourceText );

			if ( isset( $sourceData['AUTHORS'] ) ) {
				$collection->addCollectionAuthors( $sourceData['AUTHORS'] );
			}
		}
	}

	/**
	 * Read the contents of $filename and return it as a string.
	 * Return null if the file doesn't exist.
	 * Throw an exception if the file isn't readable
	 * or if the reading fails strangely.
	 * @throws InvalidArgumentException
	 */
	protected function tryReadFile( string $filename ): ?string {
		if ( $filename === '' || !file_exists( $filename ) ) {
			return null;
		}

		if ( !is_readable( $filename ) ) {
			throw new InvalidArgumentException( "File $filename is not readable." );
		}

		$data = file_get_contents( $filename );
		if ( $data === false ) {
			throw new InvalidArgumentException( "Unable to read file $filename." );
		}

		return $data;
	}

	/** Remove excluded authors. */
	public function filterAuthors( array $authors, string $code ): array {
		$groupId = $this->group->getId();
		$configHelper = Services::getInstance()->getConfigHelper();
		foreach ( $authors as $i => $v ) {
			if ( $configHelper->isAuthorExcluded( $groupId, $code, (string)$v ) ) {
				unset( $authors[$i] );
			}
		}

		return array_values( $authors );
	}

	public function isContentEqual( ?string $a, ?string $b ): bool {
		return $a === $b;
	}

	public function shouldOverwrite( string $a, string $b ): bool {
		return true;
	}

	/**
	 * Check if the file format of the current group is readable by the file
	 * format system. This might happen if we are trying to export a JsonFormat
	 * or WikiPageMessage group to a GettextFormat.
	 */
	public function isGroupFfsReadable(): bool {
		try {
			$ffs = $this->group->getFFS();
		} catch ( RuntimeException $e ) {
			if ( $e->getCode() === FileBasedMessageGroup::NO_FILE_FORMAT ) {
				return false;
			}

			throw $e;
		}

		return get_class( $ffs ) === get_class( $this );
	}
}

class_alias( SimpleFormat::class, 'SimpleFFS' );
