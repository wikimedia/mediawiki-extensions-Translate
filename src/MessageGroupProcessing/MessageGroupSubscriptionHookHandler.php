<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use MediaWiki\Extension\Notifications\AttributeManager;
use MediaWiki\Extension\Notifications\Hooks\BeforeCreateEchoEventHook;
use MediaWiki\Extension\Notifications\Hooks\EchoGetBundleRulesHook;
use MediaWiki\Extension\Notifications\Model\Event;
use MediaWiki\Extension\Translate\Services;
use MediaWiki\User\UserFactory;

/**
 * Hook handler to handle user subscriptions to message groups
 * @since 2024.04
 * @license GPL-2.0-or-later
 * @author Abijeet Patro
 */
class MessageGroupSubscriptionHookHandler implements BeforeCreateEchoEventHook, EchoGetBundleRulesHook {
	private MessageGroupSubscription $messageGroupSubscription;
	private UserFactory $userFactory;
	private const SUPPORTED_NOTIFICATION_TYPES = [ 'translate-mgs-message-added' ];

	public function __construct(
		MessageGroupSubscription $messageGroupSubscription,
		UserFactory $userFactory
	) {
		$this->messageGroupSubscription = $messageGroupSubscription;
		$this->userFactory = $userFactory;
	}

	public static function registerHooks( array &$hooks ): void {
		$hooks['BeforeCreateEchoEvent'][] = static function (
			array &$notifications,
			array &$notificationCategories,
			array &$notificationIcons
		) {
			Services::getInstance()->getMessageGroupSubscriptionHookHandler()->onBeforeCreateEchoEvent(
				$notifications,
				$notificationCategories,
				$notificationIcons
			);
		};

		$hooks['EchoGetBundleRules'][] = static function ( Event $event, string &$bundleKey ) {
			Services::getInstance()->getMessageGroupSubscriptionHookHandler()->onEchoGetBundleRules(
				$event,
				$bundleKey
			);
		};
	}

	public function onBeforeCreateEchoEvent(
		array &$notifications,
		array &$notificationCategories,
		array &$notificationIcons
	) {
		$messageGroupSubscription = $this->messageGroupSubscription;
		$userFactory = $this->userFactory;
		$notificationCategories[ 'translate-message-group-subscription' ] = [
			'tooltip' => 'echo-pref-tooltip-translate-message-group-subscription'
		];

		$notifications[ 'translate-mgs-message-added' ] = [
			'category' => 'translate-message-group-subscription',
			'group' => 'neutral',
			'section' => 'message',
			'presentation-model' => MessageGroupSubscriptionPresentationModel::class,
			'bundle' => [
				'web' => true,
				'expandable' => true,
			],
			AttributeManager::ATTR_LOCATORS => static function ( Event $event ) use (
				$messageGroupSubscription,
				$userFactory
			) {
				$extra = $event->getExtra();
				$sourceGroupIds = $extra['sourceGroupIds'] ?? [];

				$commonUserIds = [];
				if ( $sourceGroupIds ) {
					// Find the list of users who will receive more specific notification about updates
					// and remove them from this group notification.
					// If an aggregate group has *two* source message group, remove users who
					// have to be subscribed to both those two source message groups.
					$commonUserIds = $messageGroupSubscription->getGroupSubscriberUnion( $sourceGroupIds );
				}

				$iterator = $messageGroupSubscription->getGroupSubscribers( $extra['groupId'] );
				$usersToNotify = [];
				foreach ( $iterator as $userIdentityValue ) {
					if ( in_array( $userIdentityValue->getId(), $commonUserIds ) ) {
						continue;
					}
					$usersToNotify[] = $userFactory->newFromUserIdentity( $userIdentityValue );
				}

				return $usersToNotify;
			}
		];

		$notificationIcons[ 'translate-mgs-icon' ] = [
			'path' => 'Translate/resources/images/bell.svg'
		];
	}

	/** Notifications for subscriptions are bundled by message group */
	public function onEchoGetBundleRules( Event $event, string &$bundleKey ) {
		if ( in_array( $event->getType(), self::SUPPORTED_NOTIFICATION_TYPES ) ) {
			$bundleKey = $event->getExtraParam( 'groupId' );
		}
	}
}
