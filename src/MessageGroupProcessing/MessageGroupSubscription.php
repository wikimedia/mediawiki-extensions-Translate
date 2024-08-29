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
	 * @param string[] $groupIds
	 * @return void
	 */
	public function queueMessage(
		Title $messageTitle,
		string $state,
		array $groupIds
	): void {
		foreach ( $groupIds as $groupId ) {
			$this->queuedMessages[ $groupId ][ $state ][] = $messageTitle->getPrefixedDBkey();
		}
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

		$groupIds = array_keys( $changesToProcess );
		$allGroupSubscribers = $this->getSubscriberIdsForGroups( $groupIds );

		// No subscribers found for the groups
		if ( !$allGroupSubscribers ) {
			$this->logger->info( 'No subscribers for groups.' );
			return;
		}

		$groups = MessageGroups::getGroupsById( $groupIds );
		foreach ( $changesToProcess as $groupId => $state ) {
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

			Event::create( [
				'type' => 'translate-mgs-message-added',
				'extra' => [
					'groupId' => $groupId,
					'groupLabel' => $group->getLabel(),
					'changes' => $state
				]
			] );

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
}
