<?php
/**
 * Code for caching the messages of file based message groups.
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2009-2013 Niklas Laxström
 * @license GPL-2.0+
 */

/**
 * Caches messages of file based message group source file. Can also track
 * that the cache is up to date. Parsing the source files can be slow, so
 * constructing CDB cache makes accessing that data constant speed regardless
 * of the actual format.
 *
 * @ingroup MessageGroups
 */
class MessageGroupCache {
	const NO_SOURCE = 1;
	const NO_CACHE = 2;
	const CHANGED = 3;

	/**
	 * @var MessageGroup
	 */
	protected $group;

	/**
	 * @var CdbReader
	 */
	protected $cache;

	/**
	 * @var string
	 */
	protected $code;

	/**
	 * Contructs a new cache object for given group and language code.
	 * @param string|FileBasedMessageGroup $group Group object or id.
	 * @param string $code Language code. Default value 'en'.
	 */
	public function __construct( $group, $code = 'en' ) {
		if ( is_object( $group ) ) {
			$this->group = $group;
		} else {
			$this->group = MessageGroups::getGroup( $group );
		}
		$this->code = $code;
	}

	/**
	 * Returns whether cache exists for this language and group.
	 * @return bool
	 */
	public function exists() {
		$old = $this->getOldCacheFileName();
		$new = $this->getCacheFileName();
		$exists = file_exists( $new );

		if ( $exists ) {
			return true;
		}

		// Perform migration if possible
		if ( file_exists( $old ) ) {
			wfMkdirParents( dirname( $new ) );
			rename( $old, $new );
			return true;
		}

		return false;
	}

	/**
	 * Returns list of message keys that are stored.
	 * @return string[] Message keys that can be passed one-by-one to get() method.
	 */
	public function getKeys() {
		$value = $this->open()->get( '#keys' );
		$array = unserialize( $value );

		return $array;
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
	 * Populates the cache from current state of the source file.
	 * @param bool|string $created Unix timestamp when the cache is created (for automatic updates).
	 */
	public function create( $created = false ) {
		$this->close(); // Close the reader instance just to be sure

		$messages = $this->group->load( $this->code );
		if ( $messages === array() ) {
			if ( $this->exists() ) {
				// Delete stale cache files
				unlink( $this->getCacheFileName() );
			}

			return; // Don't create empty caches
		}
		$hash = md5( file_get_contents( $this->group->getSourceFilePath( $this->code ) ) );

		wfMkdirParents( dirname( $this->getCacheFileName() ) );
		$cache = \Cdb\Writer::open( $this->getCacheFileName() );
		$keys = array_keys( $messages );
		$cache->set( '#keys', serialize( $keys ) );

		foreach ( $messages as $key => $value ) {
			$cache->set( $key, $value );
		}

		$cache->set( '#created', $created ? $created : wfTimestamp() );
		$cache->set( '#updated', wfTimestamp() );
		$cache->set( '#filehash', $hash );
		$cache->set( '#msgcount', count( $messages ) );
		ksort( $messages );
		$cache->set( '#msghash', md5( serialize( $messages ) ) );
		$cache->set( '#version', '3' );
		$cache->close();
	}

	/**
	 * Checks whether the cache still reflects the source file.
	 * It uses multiple conditions to speed up the checking from file
	 * modification timestamps to hashing.
	 * @param int $reason
	 * @return bool Whether the cache is up to date.
	 */
	public function isValid( &$reason ) {
		$group = $this->group;
		$groupId = $group->getId();

		$pattern = $group->getSourceFilePath( '*' );
		$filename = $group->getSourceFilePath( $this->code );

		// If the file pattern is not dependent on the language, we will assume
		// that all translations are stored in one file. This means we need to
		// actually parse the file to know if a language is present.
		if ( strpos( $pattern, '*' ) === false ) {
			$source = $group->getFFS()->read( $this->code ) !== false;
		} else {
			static $globCache = null;
			if ( !isset( $globCache[$groupId] ) ) {
				$globCache[$groupId] = array_flip( glob( $pattern, GLOB_NOESCAPE ) );
				// Definition file might not match the above pattern
				$globCache[$groupId][$group->getSourceFilePath( 'en' )] = true;
			}
			$source = isset( $globCache[$groupId][$filename] );
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
		} elseif ( filemtime( $filename ) <= $this->get( '#updated' ) ) {
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

		// Message count check
		$messages = $group->load( $this->code );
		// CDB converts numbers to strings
		$count = (int)( $this->get( '#msgcount' ) );
		if ( $count !== count( $messages ) ) {
			// Number of messsages has changed
			$reason = self::CHANGED;

			return false;
		}

		// Content hash check
		ksort( $messages );
		if ( $this->get( '#msghash' ) === md5( serialize( $messages ) ) ) {
			// Update cache so that we don't need to do slow checks next time
			$this->create( $created );

			return true;
		}

		$reason = self::CHANGED;

		return false;
	}

	/**
	 * Open the cache for reading.
	 * @return MessageGroupCache
	 */
	protected function open() {
		if ( $this->cache === null ) {
			$this->cache = \Cdb\Reader::open( $this->getCacheFileName() );
			if ( $this->cache->get( '#version' ) !== '3' ) {
				$this->close();
				unlink( $this->getCacheFileName() );
			}
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
	protected function getCacheFileName() {
		$cacheFileName = "translate_groupcache-{$this->group->getId()}/{$this->code}.cdb";

		return TranslateUtils::cacheFile( $cacheFileName );
	}

	/**
	 * Returns full path to the old cache file location.
	 * @return string
	 */
	protected function getOldCacheFileName() {
		$cacheFileName = "translate_groupcache-{$this->group->getId()}-{$this->code}.cdb";

		return TranslateUtils::cacheFile( $cacheFileName );
	}
}
