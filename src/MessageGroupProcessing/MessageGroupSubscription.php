<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use MediaWiki\Config\ServiceOptions;
use MediaWiki\User\UserIdentity;
use MessageGroup;
use StatusValue;
use User;

/**
 * Manage user subscriptions to message groups and trigger notifications
 * @since 2024.04
 * @license GPL-2.0-or-later
 * @author Abijeet Patro
 */
class MessageGroupSubscription {
	private MessageGroupSubscriptionStore $groupSubscriptionStore;
	private bool $isMessageGroupSubscriptionEnabled;
	public const CONSTRUCTOR_OPTIONS = [ 'TranslateEnableMessageGroupSubscription' ];

	public const NOT_ENABLED = 'mgs-not-enabled';
	public const UNNAMED_USER_UNSUPPORTED = 'mgs-unnamed-user-unsupported';
	public const DYNAMIC_GROUP_UNSUPPORTED = 'mgs-dynamic-group-unsupported';

	public function __construct(
		MessageGroupSubscriptionStore $groupSubscriptionStore,
		ServiceOptions $options
	) {
		$this->groupSubscriptionStore = $groupSubscriptionStore;
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
		return $this->groupSubscriptionStore->getSubscriptions( $group->getId(), $user->getId() )->count() !== 0;
	}

	public function unsubscribeFromGroup( MessageGroup $group, UserIdentity $user ): void {
		$this->groupSubscriptionStore->removeSubscriptions( $group->getId(), $user->getId() );
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
