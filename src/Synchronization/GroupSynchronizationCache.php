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
	private $initialTimeoutSeconds;
	/** @var int */
	private $incrementalTimeoutSeconds;

	/** @var string Cache tag used for groups */
	private const GROUP_LIST_TAG = 'gsc_%group_in_sync%';
	/** @var string Cache tag used for tracking groups that have errors */
	private const GROUP_ERROR_TAG = 'gsc_%group_with_error%';
	/** @var string Cache tag used for tracking groups that are in review */
	private const GROUP_IN_REVIEW_TAG = 'gsc_%group_in_review%';

	// The timeout is set to 40 minutes initially, and then incremented by 10 minutes
	// each time a message is marked as processed if group is about to expire.
	public function __construct(
		PersistentCache $cache,
		int $initialTimeoutSeconds = 2400,
		int $incrementalTimeoutSeconds = 600

	) {
		$this->cache = $cache;
		$this->initialTimeoutSeconds = $initialTimeoutSeconds;
		$this->incrementalTimeoutSeconds = $incrementalTimeoutSeconds;
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
		$expTime = $this->getExpireTime( $this->initialTimeoutSeconds );
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

	public function addGroupErrors( GroupSynchronizationResponse $response ): void {
		$groupId = $response->getGroupId();
		$remainingMessages = $response->getRemainingMessages();

		if ( !$remainingMessages ) {
			throw new LogicException( 'Cannot add a group without any remaining messages to the errors list' );
		}

		$groupMessageErrorTag = $this->getGroupMessageErrorTag( $groupId );

		$entriesToSave = [];
		foreach ( $remainingMessages as $messageParam ) {
			$titleErrorKey = $this->getMessageErrorKey( $groupId, $messageParam->getPageName() )[0];
			$entriesToSave[] = new PersistentCacheEntry(
				$titleErrorKey,
				$messageParam,
				null,
				$groupMessageErrorTag
			);
		}

		$this->cache->set( ...$entriesToSave );

		$groupErrorKey = $this->getGroupErrorKey( $groupId );

		// Check if the group already has errors
		$groupInfo = $this->cache->get( $groupErrorKey );
		if ( $groupInfo ) {
			return;
		}

		// Group did not have an error previously, add it now. When adding,
		// remove the remaining messages from the GroupSynchronizationResponse to
		// avoid the value in the cache becoming too big. The remaining messages
		// are stored as separate items in the cache.
		$trimmedGroupSyncResponse = new GroupSynchronizationResponse(
			$groupId,
			[],
			$response->hasTimedOut()
		);

		$entriesToSave[] = new PersistentCacheEntry(
			$groupErrorKey,
			$trimmedGroupSyncResponse,
			null,
			self::GROUP_ERROR_TAG
		);

		$this->cache->set( ...$entriesToSave );
	}

	/**
	 * Return the groups that have errors
	 * @return string[]
	 */
	public function getGroupsWithErrors(): array {
		$groupsInSyncEntries = $this->cache->getByTag( self::GROUP_ERROR_TAG );
		/** @var string[] */
		$groupIds = [];
		foreach ( $groupsInSyncEntries as $entry ) {
			$groupResponse = $entry->value();
			if ( $groupResponse instanceof GroupSynchronizationResponse ) {
				$groupIds[] = $groupResponse->getGroupId();
			} else {
				// Should not happen, but handle primarily to keep phan happy.
				throw $this->invalidArgument( $groupResponse, GroupSynchronizationResponse::class );
			}
		}

		return $groupIds;
	}

	/** Fetch information about a particular group that has errors including messages that failed */
	public function getGroupErrorInfo( string $groupId ): GroupSynchronizationResponse {
		$groupMessageErrorTag = $this->getGroupMessageErrorTag( $groupId );
		$groupMessageEntries = $this->cache->getByTag( $groupMessageErrorTag );

		$groupErrorKey = $this->getGroupErrorKey( $groupId );
		$groupResponseEntry = $this->cache->get( $groupErrorKey );
		$groupResponse = $groupResponseEntry[0] ? $groupResponseEntry[0]->value() : null;
		if ( $groupResponse ) {
			if ( !$groupResponse instanceof GroupSynchronizationResponse ) {
				// Should not happen, but handle primarily to keep phan happy.
				throw $this->invalidArgument( $groupResponse, GroupSynchronizationResponse::class );
			}
		} else {
			throw new LogicException( 'Requested to fetch errors for a group that has no errors.' );
		}

		$messageParams = [];
		foreach ( $groupMessageEntries as $messageEntries ) {
			$messageParam = $messageEntries->value();
			if ( $messageParam instanceof MessageUpdateParameter ) {
				$messageParams[] = $messageParam;
			} else {
				// Should not happen, but handle primarily to keep phan happy.
				throw $this->invalidArgument( $messageParam, MessageUpdateParameter::class );
			}
		}

		return new GroupSynchronizationResponse(
			$groupId,
			$messageParams,
			$groupResponse->hasTimedOut()
		);
	}

	/** Marks all messages in a group and the group itself as resolved */
	public function markGroupAsResolved( string $groupId ): GroupSynchronizationResponse {
		$groupSyncResponse = $this->getGroupErrorInfo( $groupId );
		$errorMessages = $groupSyncResponse->getRemainingMessages();

		$errorMessageKeys = [];
		foreach ( $errorMessages as $message ) {
			$errorMessageKeys[] = $this->getMessageErrorKey( $groupId, $message->getPageName() )[0];
		}

		$this->cache->delete( ...$errorMessageKeys );
		return $this->syncGroupErrors( $groupId );
	}

	/** Marks errors for a message as resolved */
	public function markMessageAsResolved( string $groupId, string $messagePageName ): void {
		$messageErrorKey = $this->getMessageErrorKey( $groupId, $messagePageName )[0];
		$messageInCache = $this->cache->get( $messageErrorKey );
		if ( !$messageInCache ) {
			throw new InvalidArgumentException(
				'Message does not appear to have synchronization errors'
			);
		}

		$this->cache->delete( $messageErrorKey );
	}

	/** Checks if the group has errors */
	public function groupHasErrors( string $groupId ): bool {
		$groupErrorKey = $this->getGroupErrorKey( $groupId );
		return $this->cache->has( $groupErrorKey );
	}

	/** Checks if group has unresolved error messages. If not clears the group from error list */
	public function syncGroupErrors( string $groupId ): GroupSynchronizationResponse {
		$groupSyncResponse = $this->getGroupErrorInfo( $groupId );
		if ( $groupSyncResponse->getRemainingMessages() ) {
			return $groupSyncResponse;
		}

		// No remaining messages left, remove group from errors list.
		$groupErrorKey = $this->getGroupErrorKey( $groupId );
		$this->cache->delete( $groupErrorKey );

		return $groupSyncResponse;
	}

	public function markGroupAsInReview( string $groupId ): void {
		$groupReviewKey = $this->getGroupReviewKey( $groupId );
		$this->cache->set(
			new PersistentCacheEntry(
				$groupReviewKey,
				$groupId,
				null,
				self::GROUP_IN_REVIEW_TAG
			)
		);
	}

	public function markGroupAsReviewed( string $groupId ): void {
		$groupReviewKey = $this->getGroupReviewKey( $groupId );
		$this->cache->delete( $groupReviewKey );
	}

	public function isGroupInReview( string $groupId ): bool {
		return $this->cache->has( $this->getGroupReviewKey( $groupId ) );
	}

	public function extendGroupExpiryTime( string $groupId ): void {
		$groupKey = $this->getGroupKey( $groupId );
		$groupEntry = $this->cache->get( $groupKey );

		if ( $groupEntry === [] ) {
			// Group is currently not being processed.
			throw new LogicException(
				'Requested extension of expiry time for a group that is not being processed. ' .
				'Check if group is being processed by calling isGroupBeingProcessed() first'
			);
		}

		if ( $groupEntry[0]->hasExpired() ) {
			throw new InvalidArgumentException(
				'Cannot extend expiry time for a group that has already expired.'
			);
		}

		$newExpiryTime = $this->getExpireTime( $this->incrementalTimeoutSeconds );

		// We start with the initial timeout minutes, we only change the timeout if the group
		// is actually about to expire.
		if ( $newExpiryTime < $groupEntry[0]->exptime() ) {
			return;
		}

		$this->cache->setExpiry( $groupKey, $newExpiryTime );
	}

	/** @internal - Internal; For testing use only */
	public function getGroupExpiryTime( $groupId ): int {
		$groupKey = $this->getGroupKey( $groupId );
		$groupEntry = $this->cache->get( $groupKey );
		if ( $groupEntry === [] ) {
			throw new InvalidArgumentException( "$groupId currently not in processing!" );
		}

		return $groupEntry[0]->exptime();
	}

	private function hasGroupTimedOut( int $syncExpTime ): bool {
		return ( new DateTime() )->getTimestamp() > $syncExpTime;
	}

	private function getExpireTime( int $timeoutSeconds ): int {
		$currentTime = ( new DateTime() )->getTimestamp();
		$expTime = ( new DateTime() )
			->setTimestamp( $currentTime + $timeoutSeconds )
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

	private function getGroupErrorKey( string $groupId ): string {
		$hash = substr( hash( 'sha256', $groupId ), 0, 40 );
		return substr( "{$hash}_gsc_error_$groupId", 0, 255 );
	}

	/** @return string[] */
	private function getMessageErrorKey( string $groupId, string ...$messages ): array {
		$messageKeys = [];
		foreach ( $messages as $message ) {
			$key = $groupId . '_' . $message;
			$hash = substr( hash( 'sha256', $key ), 0, 40 );
			$finalKey = substr( $hash . '_gsc_error_' . $key, 0, 255 );
			$messageKeys[] = $finalKey;
		}

		return $messageKeys;
	}

	private function getGroupMessageErrorTag( string $groupId ): string {
		return "gsc_%error%_$groupId";
	}

	private function getGroupReviewKey( string $groupId ): string {
		$hash = substr( hash( 'sha256', $groupId ), 0, 40 );
		return substr( "{$hash}_gsc_%review%_$groupId", 0, 255 );
	}
}
