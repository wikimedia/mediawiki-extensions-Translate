<?php
/**
 * Contains classes for handling the message index.
 *
 * @file
 * @author Niklas Laxstrom
 * @copyright Copyright © 2008-2013, Niklas Laxström
 * @license GPL-2.0-or-later
 */

use Cdb\Reader;
use Cdb\Writer;
use MediaWiki\Extension\Translate\Services;
use MediaWiki\Logger\LoggerFactory;
use MediaWiki\MediaWikiServices;

/**
 * Creates a database of keys in all groups, so that namespace and key can be
 * used to get the groups they belong to. This is used as a fallback when
 * loadgroup parameter is not provided in the request, which happens if someone
 * reaches a messages from somewhere else than Special:Translate. Also used
 * by Special:TranslationStats and alike which need to map lots of titles
 * to message groups.
 */
abstract class MessageIndex {
	private const CACHEKEY = 'Translate-MessageIndex-interim';

	/** @var self */
	protected static $instance;
	/** @var MapCacheLRU|null */
	private static $keysCache;
	/** @var BagOStuff */
	protected $interimCache;
	/** @var WANObjectCache */
	private $statusCache;

	public function __construct() {
		// TODO: Use dependency injection
		$this->statusCache = MediaWikiServices::getInstance()->getMainWANObjectCache();
	}

	/**
	 * @deprecated Since 2020.10 Use Services::getMessageIndex()
	 * @return self
	 */
	public static function singleton(): self {
		if ( self::$instance === null ) {
			self::$instance = Services::getInstance()->getMessageIndex();
		}

		return self::$instance;
	}

	/**
	 * Override the global instance, for testing.
	 *
	 * @since 2015.04
	 * @param MessageIndex $instance
	 */
	public static function setInstance( self $instance ) {
		self::$instance = $instance;
	}

	/**
	 * Retrieves a list of groups given MessageHandle belongs to.
	 * @since 2012-01-04
	 * @param MessageHandle $handle
	 * @return string[]
	 */
	public static function getGroupIds( MessageHandle $handle ): array {
		global $wgTranslateMessageNamespaces;

		$title = $handle->getTitle();

		if ( !$title->inNamespaces( $wgTranslateMessageNamespaces ) ) {
			return [];
		}

		$namespace = $title->getNamespace();
		$key = $handle->getKey();
		$normkey = TranslateUtils::normaliseKey( $namespace, $key );

		$cache = self::getCache();
		$value = $cache->get( $normkey );
		if ( $value === null ) {
			$value = (array)self::singleton()->getWithCache( $normkey );
			$cache->set( $normkey, $value );
		}

		return $value;
	}

	/** @return MapCacheLRU */
	private static function getCache() {
		if ( self::$keysCache === null ) {
			self::$keysCache = new MapCacheLRU( 30 );
		}
		return self::$keysCache;
	}

	/**
	 * @since 2012-01-04
	 * @param MessageHandle $handle
	 * @return ?string
	 */
	public static function getPrimaryGroupId( MessageHandle $handle ): ?string {
		$groups = self::getGroupIds( $handle );

		return count( $groups ) ? array_shift( $groups ) : null;
	}

	private function getWithCache( $key ) {
		$interimCacheValue = $this->getInterimCache()->get( self::CACHEKEY );
		if ( $interimCacheValue && isset( $interimCacheValue['newKeys'][$key] ) ) {
			return $interimCacheValue['newKeys'][$key];
		}

		return $this->get( $key );
	}

	/**
	 * Looks up the stored value for single key. Only for testing.
	 * @since 2012-04-10
	 * @param string $key
	 * @return string|array|null
	 */
	protected function get( $key ) {
		// Default implementation
		$mi = $this->retrieve();
		return $mi[$key] ?? null;
	}

	/**
	 * @param bool $forRebuild
	 * @return array
	 */
	abstract public function retrieve( $forRebuild = false );

	/**
	 * @since 2018.01
	 * @return string[]
	 */
	public function getKeys() {
		return array_keys( $this->retrieve() );
	}

	abstract protected function store( array $array, array $diff );

	protected function lock() {
		return true;
	}

	protected function unlock() {
		return true;
	}

	/**
	 * Creates the index from scratch.
	 *
	 * @param float|null $timestamp Purge interim caches older than this timestamp.
	 * @return array
	 * @throws Exception
	 */
	public function rebuild( float $timestamp = null ): array {
		$logger = LoggerFactory::getInstance( 'Translate' );

		static $recursion = 0;

		if ( $recursion > 0 ) {
			$msg = __METHOD__ . ': trying to recurse - building the index first time?';
			wfWarn( $msg );

			$recursion--;
			return [];
		}
		$recursion++;

		$logger->info(
			'[MessageIndex] Started rebuild. Initiated by {callers}',
			[ 'callers' => wfGetAllCallers( 20 ) ]
		);

		$groups = MessageGroups::singleton()->getGroups();

		$tsStart = microtime( true );
		if ( !$this->lock() ) {
			throw new MessageIndexException( __CLASS__ . ': unable to acquire lock' );
		}

		$lockWaitDuration = microtime( true ) - $tsStart;
		$logger->info(
			'[MessageIndex] Got lock in {duration}',
			[ 'duration' => $lockWaitDuration ]
		);

		self::getCache()->clear();

		$new = [];
		$old = $this->retrieve( 'rebuild' );
		$postponed = [];

		/** @var MessageGroup $g */
		foreach ( $groups as $g ) {
			if ( !$g->exists() ) {
				$id = $g->getId();
				wfWarn( __METHOD__ . ": group '$id' is registered but does not exist" );
				continue;
			}

			# Skip meta thingies
			if ( $g->isMeta() ) {
				$postponed[] = $g;
				continue;
			}

			$this->checkAndAdd( $new, $g );
		}

		foreach ( $postponed as $g ) {
			$this->checkAndAdd( $new, $g, true );
		}

		$diff = self::getArrayDiff( $old, $new );
		$this->store( $new, $diff['keys'] );
		$this->unlock();

		$criticalSectionDuration = microtime( true ) - $tsStart - $lockWaitDuration;
		$logger->info(
			'[MessageIndex] Finished critical section in {duration}',
			[ 'duration' => $criticalSectionDuration ]
		);

		$cache = $this->getInterimCache();
		$interimCacheValue = $cache->get( self::CACHEKEY );
		$timestamp = $timestamp ?? microtime( true );
		if ( $interimCacheValue ) {
			if ( $interimCacheValue['timestamp'] <= $timestamp ) {
				$cache->delete( self::CACHEKEY );
			} else {
				// Cache has a later timestamp. This may be caused due to
				// job deduplication. Just in case, spin off a new job to clean up the cache.
				$job = MessageIndexRebuildJob::newJob();
				TranslateUtils::getJobQueueGroup()->push( $job );
			}
		}

		// Other caches can check this key to know when they need to refresh
		$this->statusCache->touchCheckKey( $this->getStatusCacheKey() );

		$this->clearMessageGroupStats( $diff );

		$recursion--;

		return $new;
	}

	/**
	 * @since 2021.10
	 * @return string
	 */
	public function getStatusCacheKey(): string {
		return $this->statusCache->makeKey( 'Translate', 'MessageIndex', 'status' );
	}

	private function getInterimCache(): BagOStuff {
		return ObjectCache::getInstance( CACHE_ANYTHING );
	}

	public function storeInterim( MessageGroup $group, array $newKeys ): void {
		$namespace = $group->getNamespace();
		$id = $group->getId();

		$normalizedNewKeys = [];
		foreach ( $newKeys as $key ) {
			$normalizedNewKeys[TranslateUtils::normaliseKey( $namespace, $key )] = $id;
		}

		$cache = $this->getInterimCache();
		// Merge existing with existing keys
		$interimCacheValue = $cache->get( self::CACHEKEY, $cache::READ_LATEST );
		if ( $interimCacheValue ) {
			$normalizedNewKeys = array_merge( $interimCacheValue['newKeys'], $normalizedNewKeys );
		}

		$value = [
			'timestamp' => microtime( true ),
			'newKeys' => $normalizedNewKeys,
		];

		$cache->set( self::CACHEKEY, $value, $cache::TTL_DAY );
	}

	/**
	 * Compares two associative arrays.
	 *
	 * Values must be a string or list of strings. Returns an array of added,
	 * deleted and modified keys as well as value changes (you can think values
	 * as categories and keys as pages). Each of the keys ('add', 'del', 'mod'
	 * respectively) maps to an array whose keys are the changed keys of the
	 * original arrays and values are lists where first element contains the
	 * old value and the second element the new value.
	 *
	 * @code
	 * $a = [ 'a' => '1', 'b' => '2', 'c' => '3' ];
	 * $b = [ 'b' => '2', 'c' => [ '3', '2' ], 'd' => '4' ];
	 *
	 * self::getArrayDiff( $a, $b ) === [
	 *   'keys' => [
	 *     'add' => [ 'd' => [ [], [ '4' ] ] ],
	 *     'del' => [ 'a' => [ [ '1' ], [] ] ],
	 *     'mod' => [ 'c' => [ [ '3' ], [ '3', '2' ] ] ],
	 *   ],
	 *   'values' => [ 2, 4, 1 ]
	 * ];
	 * @endcode
	 *
	 * @param array $old
	 * @param array $new
	 * @return array
	 */
	public static function getArrayDiff( array $old, array $new ) {
		$values = [];
		$record = static function ( $groups ) use ( &$values ) {
			foreach ( $groups as $group ) {
				$values[$group] = true;
			}
		};

		$keys = [
			'add' => [],
			'del' => [],
			'mod' => [],
		];

		foreach ( $new as $key => $groups ) {
			if ( !isset( $old[$key] ) ) {
				$keys['add'][$key] = [ [], (array)$groups ];
				$record( (array)$groups );
			// Using != here on purpose to ignore the order of items
			} elseif ( $groups != $old[$key] ) {
				$keys['mod'][$key] = [ (array)$old[$key], (array)$groups ];
				$record( array_diff( (array)$old[$key], (array)$groups ) );
				$record( array_diff( (array)$groups, (array)$old[$key] ) );
			}
		}

		foreach ( $old as $key => $groups ) {
			if ( !isset( $new[$key] ) ) {
				$keys['del'][$key] = [ (array)$groups, [] ];
				$record( (array)$groups );
			}
			// We already checked for diffs above
		}

		return [
			'keys' => $keys,
			'values' => array_keys( $values ),
		];
	}

	/**
	 * Purge stuff when set of keys have changed.
	 *
	 * @param array $diff
	 */
	protected function clearMessageGroupStats( array $diff ) {
		$job = MessageGroupStatsRebuildJob::newRefreshGroupsJob( $diff['values'] );
		TranslateUtils::getJobQueueGroup()->push( $job );

		foreach ( $diff['keys'] as $keys ) {
			foreach ( $keys as $key => $data ) {
				[ $ns, $pagename ] = explode( ':', $key, 2 );
				$title = Title::makeTitle( $ns, $pagename );
				$handle = new MessageHandle( $title );
				[ $oldGroups, $newGroups ] = $data;
				Hooks::run( 'TranslateEventMessageMembershipChange',
					[ $handle, $oldGroups, $newGroups ] );
			}
		}
	}

	/**
	 * @param array &$hugearray
	 * @param MessageGroup $g
	 * @param bool $ignore
	 */
	protected function checkAndAdd( &$hugearray, MessageGroup $g, $ignore = false ) {
		$keys = $g->getKeys();
		$id = $g->getId();
		$namespace = $g->getNamespace();

		foreach ( $keys as $key ) {
			# Force all keys to lower case, because the case doesn't matter and it is
			# easier to do comparing when the case of first letter is unknown, because
			# mediawiki forces it to upper case
			$key = TranslateUtils::normaliseKey( $namespace, $key );
			if ( isset( $hugearray[$key] ) ) {
				if ( !$ignore ) {
					$to = implode( ', ', (array)$hugearray[$key] );
					wfWarn( "Key $key already belongs to $to, conflict with $id" );
				}

				if ( is_array( $hugearray[$key] ) ) {
					// Hard work is already done, just add a new reference
					$hugearray[$key][] = & $id;
				} else {
					// Store the actual reference, then remove it from array, to not
					// replace the references value, but to store an array of new
					// references instead. References are hard!
					$value = & $hugearray[$key];
					unset( $hugearray[$key] );
					$hugearray[$key] = [ &$value, &$id ];
				}
			} else {
				$hugearray[$key] = & $id;
			}
		}
		unset( $id ); // Disconnect the previous references to this $id
	}

	/**
	 * These are probably slower than serialize and unserialize,
	 * but they are more space efficient because we only need
	 * strings and arrays.
	 * @param mixed $data
	 * @return mixed
	 */
	protected function serialize( $data ) {
		if ( is_array( $data ) ) {
			return implode( '|', $data );
		} else {
			return $data;
		}
	}

	protected function unserialize( $data ) {
		if ( strpos( $data, '|' ) !== false ) {
			return explode( '|', $data );
		}

		return $data;
	}
}

/**
 * Storage on serialized file.
 *
 * This serializes the whole array. Because this format can preserve
 * the values which are stored as references inside the array, this is
 * the most space efficient storage method and fastest when you want
 * the full index.
 *
 * Unfortunately when the size of index grows to about 50000 items, even
 * though it is only 3,5M on disk, it takes 35M when loaded into memory
 * and the loading can take more than 0,5 seconds. Because usually we
 * need to look up only few keys, it is better to use another backend
 * which provides random access - this backend doesn't support that.
 */
class SerializedMessageIndex extends MessageIndex {
	/** @var array|null */
	protected $index;
	protected $filename = 'translate_messageindex.ser';

	/**
	 * @param bool $forRebuild
	 * @return array
	 */
	public function retrieve( $forRebuild = false ) {
		if ( $this->index !== null ) {
			return $this->index;
		}

		$file = TranslateUtils::cacheFile( $this->filename );
		if ( file_exists( $file ) ) {
			$this->index = unserialize( file_get_contents( $file ) );
		} else {
			$this->index = $this->rebuild();
		}

		return $this->index;
	}

	protected function store( array $array, array $diff ) {
		$file = TranslateUtils::cacheFile( $this->filename );
		file_put_contents( $file, serialize( $array ) );
		$this->index = $array;
	}
}

/**
 * Storage on the database itself.
 *
 * This is likely to be the slowest backend. However, it scales okay
 * and provides random access. It also doesn't need any special setup,
 * the database table is added with update.php together with other tables,
 * which is the reason this is the default backend. It also works well
 * on multi-server setup without needing for shared file storage.
 *
 * @since 2012-04-12
 */
class DatabaseMessageIndex extends MessageIndex {
	/** @var array|null */
	protected $index;

	protected function lock() {
		$dbw = wfGetDB( DB_PRIMARY );

		// Any transaction should be flushed after getting the lock to avoid
		// stale pre-lock REPEATABLE-READ snapshot data.
		$ok = $dbw->lock( 'translate-messageindex', __METHOD__, 30 );
		if ( $ok ) {
			$dbw->commit( __METHOD__, 'flush' );
		}

		return $ok;
	}

	protected function unlock() {
		$fname = __METHOD__;
		$dbw = wfGetDB( DB_PRIMARY );
		// Unlock once the rows are actually unlocked to avoid deadlocks
		if ( !$dbw->trxLevel() ) {
			$dbw->unlock( 'translate-messageindex', $fname );
		} elseif ( is_callable( [ $dbw, 'onTransactionResolution' ] ) ) { // 1.28
			$dbw->onTransactionResolution( static function () use ( $dbw, $fname ) {
				$dbw->unlock( 'translate-messageindex', $fname );
			}, $fname );
		} else {
			$dbw->onTransactionCommitOrIdle( static function () use ( $dbw, $fname ) {
				$dbw->unlock( 'translate-messageindex', $fname );
			}, $fname );
		}

		return true;
	}

	/**
	 * @param bool $forRebuild
	 * @return array
	 */
	public function retrieve( $forRebuild = false ) {
		if ( $this->index !== null && !$forRebuild ) {
			return $this->index;
		}

		$dbr = wfGetDB( $forRebuild ? DB_PRIMARY : DB_REPLICA );
		$res = $dbr->select( 'translate_messageindex', '*', [], __METHOD__ );
		$this->index = [];
		foreach ( $res as $row ) {
			$this->index[$row->tmi_key] = $this->unserialize( $row->tmi_value );
		}

		return $this->index;
	}

	protected function get( $key ) {
		$dbr = wfGetDB( DB_REPLICA );
		$value = $dbr->selectField(
			'translate_messageindex',
			'tmi_value',
			[ 'tmi_key' => $key ],
			__METHOD__
		);

		if ( is_string( $value ) ) {
			$value = $this->unserialize( $value );
		} else {
			$value = null;
		}

		return $value;
	}

	protected function store( array $array, array $diff ) {
		$updates = [];

		foreach ( [ $diff['add'], $diff['mod'] ] as $changes ) {
			foreach ( $changes as $key => $data ) {
				[ , $new ] = $data;
				$updates[] = [
					'tmi_key' => $key,
					'tmi_value' => $this->serialize( $new ),
				];
			}
		}

		$index = [ 'tmi_key' ];
		$deletions = array_keys( $diff['del'] );

		$dbw = wfGetDB( DB_PRIMARY );
		$dbw->startAtomic( __METHOD__ );

		if ( $updates !== [] ) {
			$dbw->replace( 'translate_messageindex', [ $index ], $updates, __METHOD__ );
		}

		if ( $deletions !== [] ) {
			$dbw->delete( 'translate_messageindex', [ 'tmi_key' => $deletions ], __METHOD__ );
		}

		$dbw->endAtomic( __METHOD__ );

		$this->index = $array;
	}
}

/**
 * Storage on the object cache.
 *
 * This can be faster than DatabaseMessageIndex, but it doesn't
 * provide random access, and the data is not guaranteed to be persistent.
 *
 * This is unlikely to be the best backend for you, so don't use it.
 */
class CachedMessageIndex extends MessageIndex {
	protected $key = 'translate-messageindex';
	protected $cache;
	/** @var array|null */
	protected $index;

	protected function __construct() {
		parent::__construct();
		$this->cache = ObjectCache::getInstance( CACHE_ANYTHING );
	}

	/**
	 * @param bool $forRebuild
	 * @return array
	 */
	public function retrieve( $forRebuild = false ) {
		if ( $this->index !== null ) {
			return $this->index;
		}

		$key = $this->cache->makeKey( $this->key );
		$data = $this->cache->get( $key );
		if ( is_array( $data ) ) {
			$this->index = $data;
		} else {
			$this->index = $this->rebuild();
		}

		return $this->index;
	}

	protected function store( array $array, array $diff ) {
		$key = $this->cache->makeKey( $this->key );
		$this->cache->set( $key, $array );

		$this->index = $array;
	}
}

/**
 * Storage on CDB files.
 *
 * This is improved version of SerializedMessageIndex. It uses CDB files
 * for storage, which means it provides random access. The CDB files are
 * about double the size of serialized files (~7M for 50000 keys).
 *
 * Loading the whole index is slower than serialized, but about the same
 * as for database. Suitable for single-server setups where
 * SerializedMessageIndex is too slow for loading the whole index.
 *
 * @since 2012-04-10
 */
class CDBMessageIndex extends MessageIndex {
	/** @var array|null */
	protected $index;
	/** @var Reader|null */
	protected $reader;
	/** @var string */
	protected $filename = 'translate_messageindex.cdb';

	/**
	 * @param bool $forRebuild
	 * @return array
	 */
	public function retrieve( $forRebuild = false ) {
		$reader = $this->getReader();
		// This must be below the line above, which may fill the index
		if ( $this->index !== null ) {
			return $this->index;
		}

		$this->index = [];
		foreach ( $this->getKeys() as $key ) {
			$this->index[$key] = $this->unserialize( $reader->get( $key ) );
		}

		return $this->index;
	}

	public function getKeys() {
		$reader = $this->getReader();
		$keys = [];
		while ( true ) {
			$key = $keys === [] ? $reader->firstkey() : $reader->nextkey();
			if ( $key === false ) {
				break;
			}
			$keys[] = $key;
		}

		return $keys;
	}

	protected function get( $key ) {
		$reader = $this->getReader();
		// We might have the full cache loaded
		if ( $this->index !== null ) {
			return $this->index[$key] ?? null;
		}

		$value = $reader->get( $key );
		if ( !is_string( $value ) ) {
			$value = null;
		} else {
			$value = $this->unserialize( $value );
		}

		return $value;
	}

	protected function store( array $array, array $diff ) {
		$this->reader = null;

		$file = TranslateUtils::cacheFile( $this->filename );
		$cache = Writer::open( $file );

		foreach ( $array as $key => $value ) {
			$value = $this->serialize( $value );
			$cache->set( $key, $value );
		}

		$cache->close();

		$this->index = $array;
	}

	protected function getReader() {
		if ( $this->reader ) {
			return $this->reader;
		}

		$file = TranslateUtils::cacheFile( $this->filename );
		if ( !file_exists( $file ) ) {
			// Create an empty index to allow rebuild
			$this->store( [], [] );
			$this->index = $this->rebuild();
		}

		$this->reader = Reader::open( $file );
		return $this->reader;
	}
}

/**
 * Storage on hash.
 *
 * For testing.
 *
 * @since 2015.04
 */
class HashMessageIndex extends MessageIndex {
	/** @var array */
	protected $index = [];

	/**
	 * @param bool $forRebuild
	 * @return array
	 */
	public function retrieve( $forRebuild = false ) {
		return $this->index;
	}

	/**
	 * @param string $key
	 *
	 * @return mixed
	 */
	protected function get( $key ) {
		return $this->index[$key] ?? null;
	}

	protected function store( array $array, array $diff ) {
		$this->index = $array;
	}

	protected function clearMessageGroupStats( array $diff ) {
	}
}
