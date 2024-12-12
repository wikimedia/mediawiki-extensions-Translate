<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use EmptyIterator;
use Iterator;
use JobQueueGroup;
use MediaWiki\Config\ServiceOptions;
use MediaWiki\Extension\Notifications\Model\Event;
use MediaWiki\Title\Title;
use MediaWiki\User\User;
use MediaWiki\User\UserIdentity;
use MediaWiki\User\UserIdentityLookup;
use MessageGroup;
use Psr\Log\LoggerInterface;
use StatusValue;

/**
 * Manage user subscriptions to message groups and trigger notifications
 * @since 2024.04
 * @license GPL-2.0-or-later
 * @author Abijeet Patro
 */
class MessageGroupSubscription {
	private MessageGroupSubscriptionStore $groupSubscriptionStore;
	private JobQueueGroup $jobQueueGroup;
	private bool $isMessageGroupSubscriptionEnabled;
	private UserIdentityLookup $userIdentityLookup;
	private array $queuedMessages = [];
	private LoggerInterface $logger;
	private ?MockEventCreator $mockEventCreator = null;

	public const STATE_ADDED = 'added';
	public const STATE_UPDATED = 'updated';
	public const CONSTRUCTOR_OPTIONS = [ 'TranslateEnableMessageGroupSubscription' ];

	public const NOT_ENABLED = 'mgs-not-enabled';
	public const UNNAMED_USER_UNSUPPORTED = 'mgs-unnamed-user-unsupported';
	public const DYNAMIC_GROUP_UNSUPPORTED = 'mgs-dynamic-group-unsupported';

	public function __construct(
		MessageGroupSubscriptionStore $groupSubscriptionStore,
		JobQueueGroup $jobQueueGroup,
		UserIdentityLookup $userIdentityLookup,
		LoggerInterface $logger,
		ServiceOptions $options
	) {
		$this->groupSubscriptionStore = $groupSubscriptionStore;
		$this->jobQueueGroup = $jobQueueGroup;
		$this->userIdentityLookup = $userIdentityLookup;
		$this->logger = $logger;
		$options->assertRequiredOptions( self::CONSTRUCTOR_OPTIONS );
		$this->isMessageGroupSubscriptionEnabled = $options->get( 'TranslateEnableMessageGroupSubscription' );
	}

	public function isEnabled(): bool {
		return $this->isMessageGroupSubscriptionEnabled;
	}

	public function subscribeToGroup( MessageGroup $group, User $user ): StatusValue {
		$status = $this->canUserSubscribeToGroup( $group, $user );
		if ( !$status->isOK() ) {
			return $status;
		}

		$this->groupSubscriptionStore->addSubscription( $group->getId(), $user->getId() );
		return StatusValue::newGood();
	}

	public function isUserSubscribedTo( MessageGroup $group, UserIdentity $user ): bool {
		return $this->groupSubscriptionStore->getSubscriptions( [ $group->getId() ], $user->getId() )->count() !== 0;
	}

	public function unsubscribeFromGroup( MessageGroup $group, UserIdentity $user ): void {
		$this->groupSubscriptionStore->removeSubscriptions( $group->getId(), $user->getId() );
	}

	public function unsubscribeFromGroupsById( array $groupIds, UserIdentity $user ): void {
		$uniqueGroupIds = array_unique( $groupIds );
		foreach ( $uniqueGroupIds as $groupId ) {
			$this->groupSubscriptionStore->removeSubscriptions( $groupId, $user->getId() );
		}
	}

	public function subscribeToGroupsById( array $groupIds, UserIdentity $user ): void {
		$uniqueGroupIds = array_unique( $groupIds );
		foreach ( $uniqueGroupIds as $groupId ) {
			$this->groupSubscriptionStore->addSubscription( $groupId, $user->getId() );
		}
	}

	/** @return string[] */
	public function getUserSubscriptions( UserIdentity $user ): array {
		$subscriptions = [];
		$result = $this->groupSubscriptionStore->getSubscriptions( null, $user->getId() );
		foreach ( $result as $row ) {
			$subscriptions[] = $row->tmgs_group;
		}
		return $subscriptions;
	}

	/**
	 * Queue a message / group to send notifications for
	 * @param Title $messageTitle
	 * @param string $state
	 * @return void
	 */
	public function queueMessage( Title $messageTitle, string $state, string $groupId ): void {
		$this->queuedMessages[ $groupId ][ $state ][] = $messageTitle->getPrefixedDBkey();
	}

	public function queueNotificationJob(): void {
		if ( !$this->isEnabled() || $this->queuedMessages === [] ) {
			return;
		}

		$this->jobQueueGroup->push( MessageGroupSubscriptionNotificationJob::newJob( $this->queuedMessages ) );
		$this->logger->debug(
			'Queued job with changes for {countGroups} groups',
			[ 'countGroups' => count( $this->queuedMessages ) ]
		);
		// Reset queued messages once job has been queued
		$this->queuedMessages = [];
	}

	/**
	 * @param array<string,array<string,array<int,string>>> $changesToProcess
	 *  Group ID → state → array of message prefixed DB keys map
	 */
	public function sendNotifications( array $changesToProcess ): void {
		if ( !$this->isEnabled() || $changesToProcess === [] ) {
			return;
		}

		$groupIdAggregateMapped = $this->getMappedAggregateGroupIds();

		// List of changes to process along with aggregate groups.
		$changesWithAggregateGroups = $changesToProcess;
		$sourceGroupIdMap = [];
		// Find aggregate groups which need to be notified.
		foreach ( $changesToProcess as $groupId => $stateValues ) {
			// Find the aggregate groups that the current group belongs to.
			$aggregateGroupIds = $groupIdAggregateMapped[$groupId] ?? [];
			if ( !$aggregateGroupIds ) {
				continue;
			}

			foreach ( $aggregateGroupIds as $aggregateGroupId ) {
				// The aggregate group might already be in the list of changes to process
				$currentGroupState = $changesWithAggregateGroups[$aggregateGroupId] ??
					$changesToProcess[$aggregateGroupId] ?? [];
				$changesWithAggregateGroups[$aggregateGroupId] =
					$this->appendToState( $currentGroupState, $stateValues );

				// If an aggregate group is added to the list of changes directly, don't bother finding other
				// groups that have this group as a parent and notify all subscribers; otherwise, add the source
				// message group id due to which notification is being sent to this aggregate group.
				if ( !isset( $changesToProcess[$aggregateGroupId] ) ) {
					$sourceGroupIdMap[$aggregateGroupId][$groupId] = true;
				}
			}
		}

		$groupIdsToNotify = array_keys( $changesWithAggregateGroups );
		$allGroupSubscribers = $this->getSubscriberIdsForGroups( $groupIdsToNotify );

		// No subscribers found for the groups
		if ( !$allGroupSubscribers ) {
			$this->logger->info( 'No subscribers for groups.' );
			return;
		}

		$groups = MessageGroups::getGroupsById( $groupIdsToNotify );
		foreach ( $changesWithAggregateGroups as $groupId => $state ) {
			$group = $groups[ $groupId ] ?? null;
			if ( !$group ) {
				$this->logger->debug(
					'Group not found {groupId}.',
					[ 'groupId' => $groupId ]
				);
				continue;
			}

			$groupSubscribers = $allGroupSubscribers[ $groupId ] ?? [];
			if ( $groupSubscribers === [] ) {
				$this->logger->info(
					'No subscribers found for {groupId} group.',
					[ 'groupId' => $groupId ]
				);
				continue;
			}

			$extraParams = [
				'groupId' => $groupId,
				'groupLabel' => $group->getLabel(),
				'changes' => $state,
			];

			if ( isset( $sourceGroupIdMap[ $groupId ] ) ) {
				$extraParams['sourceGroupIds'] = array_unique( array_keys( $sourceGroupIdMap[ $groupId ] ) );
			}

			if ( $this->mockEventCreator ) {
				$this->mockEventCreator->create( [
					'type' => 'translate-mgs-message-added',
					'extra' => $extraParams
				] );
			} else {
				Event::create( [
					'type' => 'translate-mgs-message-added',
					'extra' => $extraParams
				] );
			}

			$this->logger->info(
				'Event created for {groupId} with {subscriberCount} subscribers.',
				[
					'groupId' => $groupId,
					'subscriberCount' => count( $groupSubscribers )
				]
			);
		}
	}

	/**
	 * Given a group id returns an iterator to the subscribers of that group.
	 * Returns an EmptyIterator if there are no subscribers
	 * @return Iterator<UserIdentity>
	 */
	public function getGroupSubscribers( string $groupId ): Iterator {
		$groupSubscriberIds = $this->getSubscriberIdsForGroups( [ $groupId ] );
		$groupSubscriberIds = $groupSubscriberIds[ $groupId ] ?? [];
		if ( $groupSubscriberIds === [] ) {
			return new EmptyIterator();
		}

		return $this->userIdentityLookup->newSelectQueryBuilder()
			->whereUserIds( $groupSubscriberIds )
			->caller( __METHOD__ )
			->fetchUserIdentities();
	}

	/**
	 * Return a list of users ids that belong to all the given groups
	 * @return int[]
	 */
	public function getGroupSubscriberUnion( array $groupIds ): array {
		$unionGroups = $this->groupSubscriptionStore->getSubscriptionByGroupUnion( $groupIds );
		$userList = [];

		foreach ( $unionGroups as $row ) {
			$userList[] = (int)$row;
		}

		return $userList;
	}

	public function setMockEventCreator( MockEventCreator $mockEventCreator ): void {
		$this->mockEventCreator = $mockEventCreator;
	}

	/**
	 * Get all subscribers for groups. Returns an array where the keys are the
	 * group ids and value is a list of integer user ids
	 * @param string[] $groupIds
	 * @return array[] [(str) groupId => (int[]) userId, ...]
	 */
	private function getSubscriberIdsForGroups( array $groupIds ): array {
		$dbGroupSubscriptions = $this->groupSubscriptionStore->getSubscriptions( $groupIds, null );
		$groupSubscriptions = [];

		foreach ( $dbGroupSubscriptions as $row ) {
			$groupSubscriptions[ $row->tmgs_group ][] = (int)$row->tmgs_user_id;
		}

		return $groupSubscriptions;
	}

	public function canUserSubscribeToGroup( MessageGroup $group, User $user ): StatusValue {
		if ( !$this->isEnabled() ) {
			return StatusValue::newFatal( self::NOT_ENABLED );
		}

		if ( MessageGroups::isDynamic( $group ) ) {
			return StatusValue::newFatal( self::DYNAMIC_GROUP_UNSUPPORTED );
		}

		if ( !$user->isNamed() ) {
			return StatusValue::newFatal( self::UNNAMED_USER_UNSUPPORTED );
		}

		return StatusValue::newGood();
	}

	/**
	 * Returns a map of group id mapped to the aggregate groups that it belongs to.
	 * @return array<string, string[]>
	 */
	private function getMappedAggregateGroupIds(): array {
		$groupStructure = MessageGroups::getGroupStructure();
		// Flatten the group structure for easy indexing
		$groupIdAggregateMapped = [];
		foreach ( $groupStructure as $groupId => $mappedGroups ) {
			if ( !is_array( $mappedGroups ) ) {
				// We don't care about non-aggregate groups
				continue;
			}

			// array_merge_recursive causes duplicates to appear in the mapped group ids, but that's
			// alright, we can deduplicate them when we use the values.
			$groupIdAggregateMapped = array_merge_recursive(
				$groupIdAggregateMapped,
				$this->mapGroups( $mappedGroups, $groupId )
			);
		}
		return $groupIdAggregateMapped;
	}

	/** @return array<string, string[]> */
	private function mapGroups( array $subGroupList, string $groupId ): array {
		$groupIdAggregateMapped = [];
		foreach ( $subGroupList as $subGroups ) {
			if ( is_array( $subGroups ) && $subGroups ) {
				// First group in the array is the aggregate group
				$subGroupId = ( $subGroups[0] )->getId();
				$groupIdAggregateMapped = $this->mapGroups( array_slice( $subGroups, 1 ), $subGroupId );
				foreach ( array_keys( $groupIdAggregateMapped ) as $mappedGubGroupId ) {
					$groupIdAggregateMapped[$mappedGubGroupId][] = $groupId;
				}
				$groupIdAggregateMapped[$subGroupId][] = $groupId;
			} else {
				$groupIdAggregateMapped[$subGroups->getId()][] = $groupId;
			}
		}
		return $groupIdAggregateMapped;
	}

	private function appendToState( array $existingState, array $newState ): array {
		foreach ( $newState as $stateType => $stateValues ) {
			$existingState[$stateType] = array_unique(
				array_merge( $existingState[$stateType] ?? [], $stateValues )
			);
		}

		return $existingState;
	}
}
