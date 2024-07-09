<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use MediaWiki\Extension\Notifications\Formatters\EchoEventPresentationModel;
use MediaWiki\SpecialPage\SpecialPage;

/**
 * Presentation model for Echo notifications sent out for message group subscriptions
 * @since 2024.04
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 */
class MessageGroupSubscriptionPresentationModel extends EchoEventPresentationModel {
	public function getIconType() {
		return 'translate-mgs-icon';
	}

	public function getPrimaryLink() {
		if ( $this->isBundled() ) {
			return false;
		}

		$groupId = $this->event->getExtraParam( 'groupId' );
		return [
			'url' => SpecialPage::getTitleFor( 'Translate', $groupId )->getFullURL(),
			'label' => $this->msg( 'notification-link-mgs-group-translate' )
				->params( $this->event->getExtraParam( 'groupLabel' ) )
		];
	}

	public function getHeaderMessage() {
		$msg = $this->msg( 'notification-header-message-group-subscription' );
		$msg->params( $this->event->getExtraParam( 'groupLabel' ) );
		return $msg;
	}

	public function getCompactHeaderMessageKey(): string {
		$type = $this->event->getType();
		if ( $type === 'translate-mgs-message-added' ) {
			return 'notification-body-translate-mgs-message-added';
		}

		return parent::getCompactHeaderMessageKey();
	}

	public function getCompactHeaderMessage() {
		$msg = $this->msg( $this->getCompactHeaderMessageKey() );
		$msg->params( $this->getNumberOfChangedMessages() );
		return $msg;
	}

	public function getBodyMessage() {
		$type = $this->event->getType();
		if ( $type === 'translate-mgs-message-added' ) {
			if ( $this->isBundled() ) {
				$events = $this->getBundledEvents();
				$events[] = $this->event;
			} else {
				$events = [ $this->event ];
			}

			$addedMessages = 0;
			foreach ( $events as $event ) {
				$changes = $event->getExtraParam( 'changes' );
				$addedMessages += count( $changes[ MessageGroupSubscription::STATE_ADDED ] ?? [] );
			}

			$msg = $this->msg( 'notification-body-translate-mgs-message-added' );
			$msg->params( $addedMessages );
			return $msg;
		}
	}

	public function getNumberOfChangedMessages(): int {
		$changes = $this->event->getExtraParam( 'changes' );
		$messageCount = 0;
		foreach ( $changes as $changeType ) {
			$messageCount += count( $changeType );
		}

		return $messageCount;
	}
}
