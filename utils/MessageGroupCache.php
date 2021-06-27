<?php
/**
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */

use Cdb\Reader;
use Cdb\Writer;

/**
 * Caches messages of file based message group source file. Can also track
 * that the cache is up to date. Parsing the source files can be slow, so
 * constructing CDB cache makes accessing that data constant speed regardless
 * of the actual format. This also avoid having to deal with potentially unsafe
 * external files during web requests.
 *
 * @ingroup MessageGroups
 */
class MessageGroupCache {
	public const NO_SOURCE = 1;
	public const NO_CACHE = 2;
	public const CHANGED = 3;
	private const VERSION = '4';
	/** @var FileBasedMessageGroup */
	protected $group;
	/** @var Reader */
	protected $cache;
	/** @var string */
	protected $code;
	/** @var string */
	private $cacheFilePath;

	/**
	 * Contructs a new cache object for given group and language code.
	 * @param FileBasedMessageGroup $group
	 * @param string $code Language code.
	 * @param string $cacheFilePath
	 */
	public function __construct(
		FileBasedMessageGroup $group,
		string $code,
		string $cacheFilePath
	) {
		$this->group = $group;
		$this->code = $code;
		$this->cacheFilePath = $cacheFilePath;
	}

	/**
	 * Returns whether cache exists for this language and group.
	 * @return bool
	 */
	public function exists() {
		return file_exists( $this->getCacheFilePath() );
	}

	/**
	 * Returns list of message keys that are stored.
	 * @return string[] Message keys that can be passed one-by-one to get() method.
	 */
	public function getKeys() {
		$reader = $this->open();
		$keys = [];

		$key = $reader->firstkey();
		while ( $key !== false ) {
			if ( ( $key[0] ?? '' ) !== '#' ) {
				$keys[] = $key;
			}

			$key = $reader->nextkey();
		}

		return $keys;
	}

	/**
	 * Returns timestamp in unix-format about when this cache was first created.
	 * @return string Unix timestamp.
	 */
	public function getTimestamp() {
		return $this->open()->get( '#created' );
	}

	/**
	 * ...
	 * @return string Unix timestamp.
	 */
	public function getUpdateTimestamp() {
		return $this->open()->get( '#updated' );
	}

	/**
	 * Get an item from the cache.
	 * @param string $key
	 * @return string
	 */
	public function get( $key ) {
		return $this->open()->get( $key );
	}

	/**
	 * Get a list of authors.
	 * @return string[]
	 * @since 2020.04
	 */
	public function getAuthors(): array {
		$cache = $this->open();
		return $cache->exists( '#authors' ) ?
			$this->unserialize( $cache->get( '#authors' ) ) : [];
	}

	/**
	 * Get other data cached from the FFS class.
	 * @return array
	 * @since 2020.04
	 */
	public function getExtra(): array {
		$cache = $this->open();
		return $cache->exists( '#extra' ) ? $this->unserialize( $cache->get( '#extra' ) ) : [];
	}

	/**
	 * Populates the cache from current state of the source file.
	 * @param bool|string $created Unix timestamp when the cache is created (for automatic updates).
	 */
	public function create( $created = false ) {
		$this->close(); // Close the reader instance just to be sure

		$parseOutput = $this->group->parseExternal( $this->code );
		$messages = $parseOutput['MESSAGES'];
		if ( $messages === [] ) {
			if ( $this->exists() ) {
				// Delete stale cache files
				unlink( $this->getCacheFilePath() );
			}

			return; // Don't create empty caches
		}
		$hash = md5( file_get_contents( $this->group->getSourceFilePath( $this->code ) ) );

		wfMkdirParents( dirname( $this->getCacheFilePath() ) );
		$cache = Writer::open( $this->getCacheFilePath() );

		foreach ( $messages as $key => $value ) {
			$cache->set( $key, $value );
		}
		$cache->set( '#authors', $this->serialize( $parseOutput['AUTHORS'] ) );
		$cache->set( '#extra', $this->serialize( $parseOutput['EXTRA'] ) );
		$cache->set( '#created', $created ?: wfTimestamp() );
		$cache->set( '#updated', wfTimestamp() );
		$cache->set( '#filehash', $hash );
		$cache->set( '#msghash', md5( serialize( $parseOutput ) ) );
		$cache->set( '#version', self::VERSION );
		$cache->close();
	}

	/**
	 * Checks whether the cache still reflects the source file.
	 * It uses multiple conditions to speed up the checking from file
	 * modification timestamps to hashing.
	 * @param int &$reason
	 * @return bool Whether the cache is up to date.
	 */
	public function isValid( &$reason ) {
		$group = $this->group;
		$pattern = $group->getSourceFilePath( '*' );
		$filename = $group->getSourceFilePath( $this->code );

		$parseOutput = null;

		// If the file pattern is not dependent on the language, we will assume
		// that all translations are stored in one file. This means we need to
		// actually parse the file to know if a language is present.
		if ( strpos( $pattern, '*' ) === false ) {
			$parseOutput = $group->parseExternal( $this->code );
			$source = $parseOutput['MESSAGES'] !== [];
		} else {
			static $globCache = [];
			if ( !isset( $globCache[$pattern] ) ) {
				$globCache[$pattern] = array_flip( glob( $pattern, GLOB_NOESCAPE ) );
				// Definition file might not match the above pattern
				$globCache[$pattern][$group->getSourceFilePath( 'en' )] = true;
			}
			$source = isset( $globCache[$pattern][$filename] );
		}

		$cache = $this->exists();

		// Timestamp and existence checks
		if ( !$cache && !$source ) {
			return true;
		} elseif ( !$cache && $source ) {
			$reason = self::NO_CACHE;

			return false;
		} elseif ( $cache && !$source ) {
			$reason = self::NO_SOURCE;

			return false;
		}

		if ( $this->get( '#version' ) !== self::VERSION ) {
			$reason = self::CHANGED;
			return false;
		}

		if ( filemtime( $filename ) <= $this->get( '#updated' ) ) {
			return true;
		}

		// From now on cache and source file exists, but source file mtime is newer
		$created = $this->get( '#created' );

		// File hash check
		$newhash = md5( file_get_contents( $filename ) );
		if ( $this->get( '#filehash' ) === $newhash ) {
			// Update cache so that we don't need to compare hashes next time
			$this->create( $created );

			return true;
		}

		// Parse output hash check
		$parseOutput = $parseOutput ?? $group->parseExternal( $this->code );
		if ( $this->get( '#msghash' ) === md5( serialize( $parseOutput ) ) ) {
			// Update cache so that we don't need to do slow checks next time
			$this->create( $created );

			return true;
		}

		$reason = self::CHANGED;

		return false;
	}

	public function invalidate(): void {
		$this->close();
		unlink( $this->getCacheFilePath() );
	}

	private function serialize( array $data ): string {
		// Using simple prefix for easy future extension
		return 'J' . json_encode( $data );
	}

	private function unserialize( string $serialized ): array {
		$type = $serialized[0];

		if ( $type !== 'J' ) {
			throw new RuntimeException( 'Unknown serialization format' );
		}

		return json_decode( substr( $serialized, 1 ), true );
	}

	/**
	 * Open the cache for reading.
	 * @return Reader
	 */
	protected function open() {
		if ( $this->cache === null ) {
			$this->cache = Reader::open( $this->getCacheFilePath() );
		}

		return $this->cache;
	}

	/**
	 * Close the cache from reading.
	 */
	protected function close() {
		if ( $this->cache !== null ) {
			$this->cache->close();
			$this->cache = null;
		}
	}

	/**
	 * Returns full path to the cache file.
	 * @return string
	 */
	protected function getCacheFilePath(): string {
		return $this->cacheFilePath;
	}
}
