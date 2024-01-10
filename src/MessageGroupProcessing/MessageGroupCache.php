<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use Cdb\Reader;
use Cdb\Writer;
use FileBasedMessageGroup;
use RuntimeException;

/**
 * Caches messages of file based message group source file. Can also track
 * that the cache is up to date. Parsing the source files can be slow, so
 * constructing CDB cache makes accessing that data constant speed regardless
 * of the actual format. This also avoid having to deal with potentially unsafe
 * external files during web requests.
 *
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 *
 * @ingroup MessageGroups
 */
class MessageGroupCache {
	public const NO_SOURCE = 1;
	public const NO_CACHE = 2;
	public const CHANGED = 3;
	private const VERSION = '4';
	private FileBasedMessageGroup $group;
	private ?Reader $cache = null;
	private string $languageCode;
	private string $cacheFilePath;

	/** Contructs a new cache object for given group and language code. */
	public function __construct(
		FileBasedMessageGroup $group,
		string $languageCode,
		string $cacheFilePath
	) {
		$this->group = $group;
		$this->languageCode = $languageCode;
		$this->cacheFilePath = $cacheFilePath;
	}

	/** Returns whether cache exists for this language and group. */
	public function exists(): bool {
		return file_exists( $this->getCacheFilePath() );
	}

	/**
	 * Returns list of message keys that are stored.
	 * @return string[] Message keys that can be passed one-by-one to get() method.
	 */
	public function getKeys(): array {
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
	 * @return string|false Unix timestamp.
	 */
	public function getTimestamp() {
		return $this->open()->get( '#created' );
	}

	/** @return string|false Unix timestamp. */
	public function getUpdateTimestamp() {
		return $this->open()->get( '#updated' );
	}

	/**
	 * Get an item from the cache.
	 * @return string|false
	 */
	public function get( string $key ) {
		return $this->open()->get( $key );
	}

	/**
	 * Get a list of authors.
	 * @return string[]
	 */
	public function getAuthors(): array {
		$cache = $this->open();
		return $cache->exists( '#authors' ) ?
			$this->unserialize( $cache->get( '#authors' ) ) : [];
	}

	/** Get other data cached from the file format class. */
	public function getExtra(): array {
		$cache = $this->open();
		return $cache->exists( '#extra' ) ? $this->unserialize( $cache->get( '#extra' ) ) : [];
	}

	/**
	 * Populates the cache from current state of the source file.
	 * @param string|false $created Unix timestamp when the cache is created (for automatic updates).
	 */
	public function create( $created = false ): void {
		$this->close(); // Close the reader instance just to be sure

		$parseOutput = $this->group->parseExternal( $this->languageCode );
		$messages = $parseOutput['MESSAGES'];
		if ( $messages === [] ) {
			if ( $this->exists() ) {
				// Delete stale cache files
				unlink( $this->getCacheFilePath() );
			}

			return; // Don't create empty caches
		}
		$hash = md5( file_get_contents( $this->group->getSourceFilePath( $this->languageCode ) ) );

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
	 *
	 * @param int &$reason (output) The reason for the cache being invalid.
	 * This parameter is an output-only parameter and doesn't need to be initialized
	 * by callers. It will be populated with the reason when the function returns.
	 * @return bool Whether the cache is up to date.
	 */
	public function isValid( &$reason ): bool {
		$group = $this->group;
		$pattern = $group->getSourceFilePath( '*' );
		$filename = $group->getSourceFilePath( $this->languageCode );

		$parseOutput = null;

		// If the file pattern is not dependent on the language, we will assume
		// that all translations are stored in one file. This means we need to
		// actually parse the file to know if a language is present.
		if ( !str_contains( $pattern, '*' ) ) {
			$parseOutput = $group->parseExternal( $this->languageCode );
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
		$parseOutput ??= $group->parseExternal( $this->languageCode );
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

	/** Open the cache for reading. */
	protected function open(): Reader {
		$this->cache ??= Reader::open( $this->getCacheFilePath() );

		return $this->cache;
	}

	/** Close the cache from reading. */
	protected function close(): void {
		if ( $this->cache !== null ) {
			$this->cache->close();
			$this->cache = null;
		}
	}

	/** Returns full path to the cache file. */
	protected function getCacheFilePath(): string {
		return $this->cacheFilePath;
	}
}
