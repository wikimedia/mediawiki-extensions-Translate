<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use MediaWiki\User\UserIdentity;
use MessageGroup;

/**
 * Manage user subscriptions to message groups and trigger notifications
 * @since 2024.04
 * @license GPL-2.0-or-later
 * @author Abijeet Patro
 */
class MessageGroupSubscription {
	private MessageGroupSubscriptionStore $groupSubscriptionStore;

	public function __construct( MessageGroupSubscriptionStore $groupSubscriptionStore ) {
		$this->groupSubscriptionStore = $groupSubscriptionStore;
	}

	public function isEnabled(): bool {
		return $this->groupSubscriptionStore->doesTableExist();
	}

	public function subscribeToGroup( MessageGroup $group, UserIdentity $user ): void {
		$this->groupSubscriptionStore->addSubscription( $group->getId(), $user->getId() );
	}

	public function isUserSubscribedTo( MessageGroup $group, UserIdentity $user ): bool {
		return $this->groupSubscriptionStore->getSubscriptions( $group->getId(), $user->getId() )->count() !== 0;
	}
}
