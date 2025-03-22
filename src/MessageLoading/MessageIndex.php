<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageLoading;

use Exception;
use JobQueueGroup;
use MapCacheLRU;
use MediaWiki\Config\ServiceOptions;
use MediaWiki\Extension\Translate\HookRunner;
use MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroups;
use MediaWiki\Extension\Translate\Statistics\RebuildMessageGroupStatsJob;
use MediaWiki\Title\Title;
use MessageGroup;
use Psr\Log\LoggerInterface;
use Wikimedia\ObjectCache\BagOStuff;
use Wikimedia\ObjectCache\WANObjectCache;
use Wikimedia\Rdbms\IConnectionProvider;

/**
 * Creates a database of keys in all groups, so that namespace and key can be
 * used to get the groups they belong to. This is used as a fallback when
 * loadgroup parameter is not provided in the request, which happens if someone
 * reaches a messages from somewhere else than Special:Translate. Also used
 * by Special:TranslationStats and alike which need to map lots of titles
 * to message groups.
 *
 * @author Niklas Laxstrom
 * @copyright Copyright © 2008-2013, Niklas Laxström
 * @license GPL-2.0-or-later
 */
class MessageIndex {
	// TODO: Use dependency injection
	private const CACHE_KEY = 'Translate-MessageIndex-interim';
	private const READ_LATEST = true;
	private MessageIndexStore $messageIndexStore;
	private MapCacheLRU $keysCache;
	protected BagOStuff $interimCache;
	private WANObjectCache $statusCache;
	private JobQueueGroup $jobQueueGroup;
	private HookRunner $hookRunner;
	private LoggerInterface $logger;
	private IConnectionProvider $dbProvider;
	private array $translateMessageNamespaces;
	public const SERVICE_OPTIONS = [
		'TranslateMessageNamespaces'
	];

	public function __construct(
		MessageIndexStore $store,
		WANObjectCache $statusCache,
		JobQueueGroup $jobQueueGroup,
		HookRunner $hookRunner,
		LoggerInterface $logger,
		BagOStuff $interimCache,
		IConnectionProvider $dbProvider,
		ServiceOptions $options
	) {
		$this->messageIndexStore = $store;
		$this->keysCache = new MapCacheLRU( 30 );
		$this->statusCache = $statusCache;
		$this->jobQueueGroup = $jobQueueGroup;
		$this->hookRunner = $hookRunner;
		$this->logger = $logger;
		$this->interimCache = $interimCache;
		$this->dbProvider = $dbProvider;
		$options->assertRequiredOptions( self::SERVICE_OPTIONS );
		$this->translateMessageNamespaces = $options->get( 'TranslateMessageNamespaces' );
	}

	/** Converts page name and namespace to message index format. */
	private function normaliseKey( int $namespace, string $key ): string {
		$key = lcfirst( $key );

		return strtr( "$namespace:$key", ' ', '_' );
	}

	/**
	 * Retrieves a list of groups given MessageHandle belongs to.
	 * @return string[]
	 */
	public function getGroupIds( MessageHandle $handle ): array {
		$title = $handle->getTitle();

		if ( !$title->inNamespaces( $this->translateMessageNamespaces ) ) {
			return [];
		}

		$namespace = $title->getNamespace();
		$key = $handle->getKey();
		$normalisedKey = $this->normaliseKey( $namespace, $key );

		$value = $this->keysCache->get( $normalisedKey );
		if ( $value === null ) {
			$value = (array)$this->getWithCache( $normalisedKey );
			$this->keysCache->set( $normalisedKey, $value );
		}

		return $value;
	}

	/**
	 * Fast-path to retrieve groups for database titles.
	 *
	 * Performance is critical for stats that need to check groups for many rows.
	 * Do not include the language code subpage!
	 * @return string[]
	 */
	public function getGroupIdsForDatabaseTitle( int $namespace, string $title ): array {
		$normalisedKey = $this->normaliseKey( $namespace, $title );

		// Optimization 1: skip LRU cache assuming that hit rate is very low for this use case
		// Optimization 2: skip interim cache as not essential

		return (array)$this->get( $normalisedKey );
	}

	public function getPrimaryGroupId( MessageHandle $handle ): ?string {
		$groups = $this->getGroupIds( $handle );

		return count( $groups ) ? array_shift( $groups ) : null;
	}

	/** @return string|array|null */
	private function getWithCache( string $key ) {
		$interimCacheValue = $this->getInterimCache()->get( self::CACHE_KEY );
		if ( $interimCacheValue && isset( $interimCacheValue['newKeys'][$key] ) ) {
			$this->logger->debug(
				'[MessageIndex] interim cache hit: {messageKey} with value {groupId}',
				[ 'messageKey' => $key, 'groupId' => $interimCacheValue['newKeys'][$key] ]
			);
			return $interimCacheValue['newKeys'][$key];
		}

		return $this->messageIndexStore->get( $key );
	}

	/** @return mixed|null */
	public function get( string $key ) {
		return $this->messageIndexStore->get( $key );
	}

	/** @return string[] */
	public function getKeys(): array {
		return $this->messageIndexStore->getKeys();
	}

	private function lock(): bool {
		$dbw = $this->dbProvider->getPrimaryDatabase();

		// Any transaction should be flushed after getting the lock to avoid
		// stale pre-lock REPEATABLE-READ snapshot data.
		$ok = $dbw->lock( 'translate-messageindex', __METHOD__, 5 );
		if ( $ok ) {
			$dbw->commit( __METHOD__, 'flush' );
		}

		return $ok;
	}

	private function unlock(): void {
		$fname = __METHOD__;
		$dbw = $this->dbProvider->getPrimaryDatabase();
		// Unlock once the rows are actually unlocked to avoid deadlocks
		if ( !$dbw->trxLevel() ) {
			$dbw->unlock( 'translate-messageindex', $fname );
		} else {
			$dbw->onTransactionResolution( static function () use ( $dbw, $fname ) {
				$dbw->unlock( 'translate-messageindex', $fname );
			}, $fname );
		}
	}

	/**
	 * Creates the index from scratch.
	 *
	 * @param float|null $timestamp Purge interim caches older than this timestamp.
	 * @throws Exception
	 */
	public function rebuild( ?float $timestamp = null ): array {
		static $recursion = 0;

		if ( $recursion > 0 ) {
			$msg = __METHOD__ . ': trying to recurse - building the index first time?';
			wfWarn( $msg );

			$recursion--;
			return [];
		}
		$recursion++;

		$this->logger->info( '[MessageIndex] Started rebuild.' );

		$tsStart = microtime( true );
		if ( !$this->lock() ) {
			throw new MessageIndexException( __CLASS__ . ': unable to acquire lock' );
		}

		$lockWaitDuration = microtime( true ) - $tsStart;
		$this->logger->info(
			'[MessageIndex] Got lock in {duration}',
			[ 'duration' => $lockWaitDuration ]
		);

		$groups = MessageGroups::singleton()->getGroups();
		$this->keysCache->clear();

		$new = [];
		$old = $this->messageIndexStore->retrieve( self::READ_LATEST );
		$postponed = [];

		foreach ( $groups as $messageGroup ) {
			if ( !$messageGroup->exists() ) {
				$id = $messageGroup->getId();
				wfWarn( __METHOD__ . ": group '$id' is registered but does not exist" );
				continue;
			}

			# Skip meta thingies
			if ( $messageGroup->isMeta() ) {
				$postponed[] = $messageGroup;
				continue;
			}

			$this->checkAndAdd( $new, $messageGroup );
		}

		foreach ( $postponed as $messageGroup ) {
			$this->checkAndAdd( $new, $messageGroup, true );
		}

		$diff = self::getArrayDiff( $old, $new );
		$this->messageIndexStore->store( $new, $diff['keys'] );

		$cache = $this->getInterimCache();
		$interimCacheValue = $cache->get( self::CACHE_KEY );
		if ( $interimCacheValue ) {
			$timestamp ??= microtime( true );
			if ( $interimCacheValue['timestamp'] <= $timestamp ) {
				$cache->delete( self::CACHE_KEY );
				$this->logger->debug(
					'[MessageIndex] Deleted interim cache with timestamp {cacheTimestamp} <= {currentTimestamp}.',
					[
						'cacheTimestamp' => $interimCacheValue['timestamp'],
						'currentTimestamp' => $timestamp,
					]
				);
			} else {
				// Cache has a later timestamp. This may be caused due to
				// job deduplication. Just in case, spin off a new job to clean up the cache.
				$job = RebuildMessageIndexJob::newJob( __METHOD__ );
				$this->jobQueueGroup->push( $job );
				$this->logger->debug(
					'[MessageIndex] Kept interim cache with timestamp {cacheTimestamp} > {currentTimestamp}.',
					[
						'cacheTimestamp' => $interimCacheValue['timestamp'],
						'currentTimestamp' => $timestamp,
					]
				);
			}
		}

		$this->unlock();
		$criticalSectionDuration = microtime( true ) - $tsStart - $lockWaitDuration;
		$this->logger->info(
			'[MessageIndex] Finished critical section in {duration}',
			[ 'duration' => $criticalSectionDuration ]
		);

		// Other caches can check this key to know when they need to refresh
		$this->statusCache->touchCheckKey( $this->getStatusCacheKey() );

		$this->clearMessageGroupStats( $diff );

		$recursion--;

		return $new;
	}

	public function getStatusCacheKey(): string {
		return $this->statusCache->makeKey( 'Translate', 'MessageIndex', 'status' );
	}

	private function getInterimCache(): BagOStuff {
		return $this->interimCache;
	}

	public function storeInterim( MessageGroup $group, array $newKeys ): void {
		$namespace = $group->getNamespace();
		$id = $group->getId();

		$normalizedNewKeys = [];
		foreach ( $newKeys as $key ) {
			$normalizedNewKeys[$this->normaliseKey( $namespace, $key )] = $id;
		}

		$cache = $this->getInterimCache();
		// Merge with existing keys (if present)
		$interimCacheValue = $cache->get( self::CACHE_KEY, $cache::READ_LATEST );
		if ( $interimCacheValue ) {
			$normalizedNewKeys = array_merge( $interimCacheValue['newKeys'], $normalizedNewKeys );
			$this->logger->debug(
				'[MessageIndex] interim cache: merging with existing cache of size {count}',
				[ 'count' => count( $interimCacheValue['newKeys'] ) ]
			);
		}

		$value = [
			'timestamp' => microtime( true ),
			'newKeys' => $normalizedNewKeys,
		];

		$cache->set( self::CACHE_KEY, $value, $cache::TTL_DAY );
		$this->logger->debug(
			'[MessageIndex] interim cache: added group {groupId} with new size {count} keys and ' .
			'timestamp {cacheTimestamp}',
			[ 'groupId' => $id, 'count' => count( $normalizedNewKeys ), 'cacheTimestamp' => $value['timestamp'] ]
		);
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
	public function getArrayDiff( array $old, array $new ): array {
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

	/** Purge stuff when set of keys have changed. */
	protected function clearMessageGroupStats( array $diff ): void {
		$job = RebuildMessageGroupStatsJob::newRefreshGroupsJob( $diff['values'] );
		$this->jobQueueGroup->push( $job );

		foreach ( $diff['keys'] as $keys ) {
			foreach ( $keys as $key => $data ) {
				[ $ns, $pageName ] = explode( ':', $key, 2 );
				$title = Title::makeTitle( (int)$ns, $pageName );
				$handle = new MessageHandle( $title );
				[ $oldGroups, $newGroups ] = $data;
				$this->hookRunner->onTranslateEventMessageMembershipChange(
					$handle, $oldGroups, $newGroups );
			}
		}
	}

	protected function checkAndAdd( array &$hugeArray, MessageGroup $g, bool $ignore = false ): void {
		$keys = $g->getKeys();
		$id = $g->getId();
		$namespace = $g->getNamespace();

		foreach ( $keys as $key ) {
			# Force all keys to lower case, because the case doesn't matter and it is
			# easier to do comparing when the case of first letter is unknown, because
			# mediawiki forces it to upper case
			$key = $this->normaliseKey( $namespace, $key );
			if ( isset( $hugeArray[$key] ) ) {
				if ( !$ignore ) {
					$to = implode( ', ', (array)$hugeArray[$key] );
					wfWarn( "Key $key already belongs to $to, conflict with $id" );
				}

				if ( is_array( $hugeArray[$key] ) ) {
					// Hard work is already done, just add a new reference
					$hugeArray[$key][] = & $id;
				} else {
					// Store the actual reference, then remove it from array, to not
					// replace the references value, but to store an array of new
					// references instead. References are hard!
					$value = & $hugeArray[$key];
					unset( $hugeArray[$key] );
					$hugeArray[$key] = [ &$value, &$id ];
				}
			} else {
				$hugeArray[$key] = & $id;
			}
		}
		unset( $id ); // Disconnect the previous references to this $id
	}
}
