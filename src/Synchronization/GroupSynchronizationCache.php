<?php
declare( strict_types = 1 );

namespace MediaWiki\Extensions\Translate\Synchronization;

use BagOStuff;
use DateTime;

/**
 * Message group synchronization cache. Handles storage of data in the cache
 * to track which groups are currently being synchronized
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 * @since 2020.06
 */
class GroupSynchronizationCache {
	private const CACHE_PREFIX = 'translate-msg-group-sync';

	private const OP_ADD = 'add';

	private const OP_DEL = 'remove';

	/** @var BagOStuff */
	private $cache;

	/** @var int */
	private $timeout;

	public function __construct( BagOStuff $cache, int $timeout = 600 ) {
		$this->cache = $cache;
		$this->timeout = $timeout;
	}

	/**
	 * Get the groups currently in sync
	 * @return string[]
	 */
	public function getGroupsInSync(): array {
		$groupsCacheKey = $this->getGroupsKey();
		$groupsInSync = $this->cache->get( $groupsCacheKey );

		return $groupsInSync === false ? [] : $groupsInSync;
	}

	/** Start the synchronization process for a group with the given groupId */
	public function startSync( string $groupId ): void {
		$this->cache->set( $this->getSyncTimeKey( $groupId ), ( new DateTime() )->getTimestamp() );
		$this->cache->set( $this->getGroupKey( $groupId ), [] );

		$this->modifyGroupsInSync( $groupId, self::OP_ADD );
	}

	public function getSyncStartTime( string $groupId ): ?int {
		$timestamp = $this->cache->get( $this->getSyncTimeKey( $groupId ) );
		if ( $timestamp === false ) {
			return null;
		}

		return (int)$timestamp;
	}

	/**
	 * End synchronization for a group. Removes the sync time, deletes the group key, and
	 * removes the groupId from groups in sync list
	 */
	public function endSync( string $groupId ): void {
		// Remove all the messages for the group
		$groupKey = $this->getGroupKey( $groupId );
		$groupMessageKeys = $this->cache->get( $groupKey );
		$this->removeMessages( ...$groupMessageKeys );

		// Remove the group message list
		$this->cache->delete( $groupKey );

		// Delete the group sync start time
		$this->cache->delete( $this->getSyncTimeKey( $groupId ) );

		// Remove the group from groups in sync list
		$this->modifyGroupsInSync( $groupId, self::OP_DEL );
	}

	/** Add multiple messages from a group to the cache */
	public function addMessages( string $groupId, MessageUpdateParameter ...$messageParams ): void {
		$messagesToAdd = [];
		foreach ( $messageParams as $messageParam ) {
			$messagesToAdd[ $this->getMessageTitleKey( $messageParam->getPageName() ) ] =
				$messageParam;
		}

		$this->cache->setMulti( $messagesToAdd );
		$this->modifyGroupMessagesInSync( $groupId, $messageParams, self::OP_ADD );
	}

	/** Check if the group is in synchronization */
	public function isGroupBeingProcessed( string $groupId ): bool {
		$groupMessages = $this->cache->get( $this->getGroupKey( $groupId ) );
		return $groupMessages !== false;
	}

	/**
	 * Return messages keys belonging to group Id currently in synchronization.
	 * @param string $groupId
	 * @return string[]
	 */
	public function getGroupMessageKeys( string $groupId ): array {
		$groupMessages = $this->cache->get( $this->getGroupKey( $groupId ) );
		if ( $groupMessages === false ) {
			return [];
		}

		return $groupMessages;
	}

	/**
	 * Return values for multiple messages from the cache.
	 * @param string ...$messageKeys
	 * @return MessageUpdateParameter[] Returns a key value pair, with the key being the
	 * messageKey and value being MessageUpdateParameter or null if the key is not available
	 * in the cache.
	 */
	public function getMessages( string ...$messageKeys ): array {
		$messageCacheKeys = [];
		foreach ( $messageKeys as $messageKey ) {
			$messageCacheKeys[] = $this->getMessageTitleKey( $messageKey );
		}

		$messageParams = $this->cache->getMulti( $messageCacheKeys );

		$allMessageParams = [];
		foreach ( $messageCacheKeys as $index => $messageCacheKey ) {
			$allMessageParams[$messageKeys[$index]] = $messageParams[$messageCacheKey] ?? null;
		}

		return $allMessageParams;
	}

	/**
	 * Update the group cache with the latest information with the status of message
	 * update jobs, then check if the group has timed out and returns the latest information
	 */
	public function getSynchronizationStatus( string $groupId ): GroupSynchronizationResponse {
		$this->syncGroup( $groupId );
		$syncStartTime = $this->getSyncStartTime( $groupId );
		if ( !$syncStartTime ) {
			// Processing is done
			return new GroupSynchronizationResponse( $groupId, [], false );
		}

		$hasTimedOut = $this->hasGroupTimedOut( $syncStartTime );
		$remainingMessages = $this->getGroupMessageKeys( $groupId );

		return new GroupSynchronizationResponse(
			$groupId,
			$remainingMessages,
			$hasTimedOut
		);
	}

	/**
	 * Remove messages from the cache. Removes the message keys, but DOES NOT the update group
	 * message key list.
	 */
	public function removeMessages( string ...$messageKeys ): void {
		$messageCacheKeys = [];
		foreach ( $messageKeys as $key ) {
			$messageCacheKeys[] = $this->getMessageTitleKey( $key );
		}

		$this->cache->deleteMulti( $messageCacheKeys );
	}

	/**
	 * Check messages keys that are still present in the cache and update the list of keys
	 * in the message group.
	 */
	private function syncGroup( string $groupId ): void {
		$groupCacheKey = $this->getGroupKey( $groupId );
		$groupMessages = $this->cache->get( $groupCacheKey );
		if ( $groupMessages === false ) {
			return;
		}

		$messageCacheKeys = [];
		foreach ( $groupMessages as $messageKey ) {
			$messageCacheKeys[] = $this->getMessageTitleKey( $messageKey );
		}

		$messageParams = $this->cache->getMulti( $messageCacheKeys );

		// No keys are present, delete the message and mark the group as synced
		if ( !$messageParams ) {
			$this->endSync( $groupId );
			return;
		}

		// Make a list of remaining jobs that are running.
		$remainingJobTitle = [];
		foreach ( $messageCacheKeys as $index => $messageCacheKey ) {
			if ( isset( $messageParams[$messageCacheKey] ) ) {
				$groupMessageTitle = $groupMessages[$index];
				$remainingJobTitle[] = $groupMessageTitle;
			}
		}

		// Set the group cache with the remaining job title.
		$this->cache->set( $groupCacheKey, $remainingJobTitle );
	}

	private function hasGroupTimedOut( int $syncStartTime ): bool {
		$secondsSinceSyncStart = ( new DateTime() )->getTimestamp() - $syncStartTime;
		return $secondsSinceSyncStart > $this->timeout;
	}

	private function modifyGroupsInSync( string $groupId, string $op ): void {
		$groupsCacheKey = $this->getGroupsKey();
		$this->cache->lock( $groupsCacheKey );

		$groupsInSync = $this->getGroupsInSync();
		if ( $groupsInSync === [] && $op === self::OP_DEL ) {
			return;
		}

		$this->modifyArray( $groupsInSync, $groupId, $op );

		$this->cache->set( $groupsCacheKey, $groupsInSync );
		$this->cache->unlock( $groupsCacheKey );
	}

	private function modifyGroupMessagesInSync(
		string $groupId, array $messageParams, string $op
	): void {
		$groupCacheKey = $this->getGroupKey( $groupId );

		$this->cache->lock( $groupCacheKey );

		$groupMessages = $this->getGroupMessageKeys( $groupId );
		if ( $groupMessages === [] && $op === self::OP_DEL ) {
			return;
		}

		/** @var MessageUpdateParameter $messageParam */
		foreach ( $messageParams as $messageParam ) {
			$messageTitle = $messageParam->getPageName();
			$this->modifyArray( $groupMessages, $messageTitle, $op );
		}

		$this->cache->set( $groupCacheKey, $groupMessages );
		$this->cache->unlock( $groupCacheKey );
	}

	private function modifyArray(
		array &$toModify, string $needle, string $op
	): void {
		$needleIndex = array_search( $needle, $toModify );
		if ( $op === self::OP_ADD && $needleIndex === false ) {
			$toModify[] = $needle;
		} elseif ( $op === self::OP_DEL && $needleIndex !== false ) {
			array_splice( $toModify, $needleIndex, 1 );
		}
	}

	// Cache keys related functions start here.

	private function getGroupsKey(): string {
		return $this->cache->makeKey( self::CACHE_PREFIX );
	}

	private function getSyncTimeKey( string $groupId ): string {
		return $this->cache->makeKey( self::CACHE_PREFIX, $groupId, 'time' );
	}

	private function getGroupKey( string $groupId ): string {
		return $this->cache->makeKey( self::CACHE_PREFIX, 'group', $groupId );
	}

	private function getMessageTitleKey( string $title ): string {
		return $this->cache->makeKey( self::CACHE_PREFIX, 'msg-title', $title );
	}

}
