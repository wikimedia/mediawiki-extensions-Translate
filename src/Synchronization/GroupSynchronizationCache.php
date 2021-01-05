<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Synchronization;

use DateTime;
use InvalidArgumentException;
use LogicException;
use MediaWiki\Extension\Translate\Cache\PersistentCache;
use MediaWiki\Extension\Translate\Cache\PersistentCacheEntry;
use RuntimeException;

/**
 * Message group synchronization cache. Handles storage of data in the cache
 * to track which groups are currently being synchronized.
 * Stores:
 *
 * 1. Groups in sync:
 *   - Key: {hash($groupId)}_$groupId
 *   - Value: $groupId
 *   - Tag: See GroupSynchronizationCache::getGroupsTag()
 *   - Exptime: Set when startSyncTimer is called
 *
 * 2. Message under each group being modified:
 *   - Key: {hash($groupId_$messageKey)}_$messageKey
 *   - Value: MessageUpdateParameter
 *   - Tag: gsc_$groupId
 *   - Exptime: none
 *
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 * @since 2020.06
 */
class GroupSynchronizationCache {
	/** @var PersistentCache */
	private $cache;
	/** @var int */
	private $timeoutSeconds;

	/** @var string Cache tag used for groups */
	private const GROUP_LIST_TAG = 'gsc_%group_in_sync%';

	// TODO: Decide timeout based on monitoring. Also check if it needs to be configurable
	// based on the number of messages in the group.
	public function __construct( PersistentCache $cache, int $timeoutSeconds = 2400 ) {
		$this->cache = $cache;
		$this->timeoutSeconds = $timeoutSeconds;
	}

	/**
	 * Get the groups currently in sync
	 * @return string[]
	 */
	public function getGroupsInSync(): array {
		$groupsInSyncEntries = $this->cache->getByTag( self::GROUP_LIST_TAG );
		/** @var string[] */
		$groups = [];
		foreach ( $groupsInSyncEntries as $entry ) {
			$groups[] = $entry->value();
		}

		return $groups;
	}

	/** Start synchronization process for a group and starts the expiry time */
	public function markGroupForSync( string $groupId ): void {
		$expTime = $this->getExpireTime();
		$this->cache->set(
			new PersistentCacheEntry(
				$this->getGroupKey( $groupId ),
				$groupId,
				$expTime,
				self::GROUP_LIST_TAG
			)
		);
	}

	public function getSyncEndTime( string $groupId ): ?int {
		$cacheEntry = $this->cache->get( $this->getGroupKey( $groupId ) );
		return $cacheEntry ? $cacheEntry[0]->exptime() : null;
	}

	/** End synchronization for a group. Deletes the group key */
	public function endSync( string $groupId ): void {
		if ( $this->cache->hasEntryWithTag( $this->getGroupTag( $groupId ) ) ) {
			throw new InvalidArgumentException(
				'Cannot end synchronization for a group that still has messages to be processed.'
			);
		}

		$groupKey = $this->getGroupKey( $groupId );
		$this->cache->delete( $groupKey );
	}

	/** End synchronization for a group. Deletes the group key and messages */
	public function forceEndSync( string $groupId ): void {
		$this->cache->deleteEntriesWithTag( $this->getGroupTag( $groupId ) );
		$this->endSync( $groupId );
	}

	/** Add messages for a group to the cache */
	public function addMessages( string $groupId, MessageUpdateParameter ...$messageParams ): void {
		$messagesToAdd = [];
		$groupTag = $this->getGroupTag( $groupId );
		foreach ( $messageParams as $messageParam ) {
			$titleKey = $this->getMessageKeys( $groupId, $messageParam->getPageName() )[0];
			$messagesToAdd[] = new PersistentCacheEntry(
				$titleKey,
				$messageParam,
				null,
				$groupTag
			);
		}

		$this->cache->set( ...$messagesToAdd );
	}

	/** Check if the group is in synchronization */
	public function isGroupBeingProcessed( string $groupId ): bool {
		$groupEntry = $this->cache->get( $this->getGroupKey( $groupId ) );
		return $groupEntry !== [];
	}

	/**
	 * Return all messages in a group
	 * @param string $groupId
	 * @return MessageUpdateParameter[] Returns a key value pair, with the key being the
	 * messageKey and value being MessageUpdateParameter
	 */
	public function getGroupMessages( string $groupId ): array {
		$messageEntries = $this->cache->getByTag( $this->getGroupTag( $groupId ) );

		$allMessageParams = [];
		foreach ( $messageEntries as $entry ) {
			$message = $entry->value();
			if ( $message instanceof MessageUpdateParameter ) {
				$allMessageParams[$message->getPageName()] = $message;
			} else {
				// Should not happen, but handle primarily to keep phan happy.
				throw $this->invalidArgument( $message, MessageUpdateParameter::class );
			}
		}

		return $allMessageParams;
	}

	/** Check if a message is being processed */
	public function isMessageBeingProcessed( string $groupId, string $messageKey ): bool {
		$messageCacheKey = $this->getMessageKeys( $groupId, $messageKey );
		return $this->cache->has( $messageCacheKey[0] );
	}

	/** Get the current synchronization status of the group. Does not perform any updates. */
	public function getSynchronizationStatus( string $groupId ): GroupSynchronizationResponse {
		if ( !$this->isGroupBeingProcessed( $groupId ) ) {
			// Group is currently not being processed.
			throw new LogicException(
				'Sync requested for a group currently not being processed. Check if ' .
				'group is being processed by calling isGroupBeingProcessed() first'
			);
		}

		$remainingMessages = $this->getGroupMessages( $groupId );

		// No messages are present
		if ( !$remainingMessages ) {
			return new GroupSynchronizationResponse( $groupId, [], false );
		}

		$syncExpTime = $this->getSyncEndTime( $groupId );
		if ( $syncExpTime === null ) {
			// This should not happen
			throw new RuntimeException(
				"Unexpected condition. Group: $groupId; Messages present, but group key not found."
			);
		}

		$hasTimedOut = $this->hasGroupTimedOut( $syncExpTime );

		return new GroupSynchronizationResponse(
			$groupId,
			$remainingMessages,
			$hasTimedOut
		);
	}

	/** Remove messages from the cache. */
	public function removeMessages( string $groupId, string ...$messageKeys ): void {
		$messageCacheKeys = $this->getMessageKeys( $groupId, ...$messageKeys );

		$this->cache->delete( ...$messageCacheKeys );
	}

	private function hasGroupTimedOut( int $syncExpTime ): bool {
		return ( new DateTime() )->getTimestamp() > $syncExpTime;
	}

	private function getExpireTime(): int {
		$currentTime = ( new DateTime() )->getTimestamp();
		$expTime = ( new DateTime() )
			->setTimestamp( $currentTime + $this->timeoutSeconds )
			->getTimestamp();

		return $expTime;
	}

	private function invalidArgument( $value, string $expectedType ): RuntimeException {
		$valueType = $value ? get_class( $value ) : gettype( $value );
		return new RuntimeException( "Expected $expectedType, got $valueType" );
	}

	// Cache keys / tag related functions start here.

	private function getGroupTag( string $groupId ): string {
		return 'gsc_' . $groupId;
	}

	private function getGroupKey( string $groupId ): string {
		$hash = substr( hash( 'sha256', $groupId ), 0, 40 );
		return substr( "{$hash}_$groupId", 0, 255 );
	}

	/** @return string[] */
	private function getMessageKeys( string $groupId, string ...$messages ): array {
		$messageKeys = [];
		foreach ( $messages as $message ) {
			$key = $groupId . '_' . $message;
			$hash = substr( hash( 'sha256', $key ), 0, 40 );
			$finalKey = substr( $hash . '_' . $key, 0, 255 );
			$messageKeys[] = $finalKey;
		}

		return $messageKeys;
	}
}

class_alias( GroupSynchronizationCache::class, '\MediaWiki\Extensions\Translate\GroupSynchronizationCache' );
