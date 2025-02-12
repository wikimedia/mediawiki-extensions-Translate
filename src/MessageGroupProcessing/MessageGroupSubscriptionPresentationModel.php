<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use MediaWiki\Extension\Notifications\Formatters\EchoEventPresentationModel;
use MediaWiki\Extension\Notifications\Model\Event;
use MediaWiki\SpecialPage\SpecialPage;

/**
 * Presentation model for Echo notifications sent out for message group subscriptions
 * @since 2024.04
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 */
class MessageGroupSubscriptionPresentationModel extends EchoEventPresentationModel {
	/** @inheritDoc */
	public function getIconType() {
		return 'translate-mgs-icon';
	}

	/** @inheritDoc */
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

	/** @inheritDoc */
	public function getHeaderMessage() {
		$msg = $this->msg( 'notification-header-message-group-subscription' );
		$msg->params( $this->event->getExtraParam( 'groupLabel' ) );
		return $msg;
	}

	/** @inheritDoc */
	public function getCompactHeaderMessageKey(): string {
		$type = $this->event->getType();
		if ( $type === 'translate-mgs-message-added' ) {
			return 'notification-body-translate-mgs-message-added';
		}

		return parent::getCompactHeaderMessageKey();
	}

	/** @inheritDoc */
	public function getCompactHeaderMessage() {
		$msg = $this->msg( $this->getCompactHeaderMessageKey() );
		$msg->params( $this->getNumberOfChangedMessages( $this->event ) );
		return $msg;
	}

	/** @inheritDoc */
	public function getBodyMessage() {
		$type = $this->event->getType();
		if ( $type === 'translate-mgs-message-added' ) {
			if ( $this->isBundled() ) {
				$events = $this->getBundledEvents();
				$events[] = $this->event;
			} else {
				$events = [ $this->event ];
			}

			$addedOrUpdatedMessages = 0;
			foreach ( $events as $event ) {
				$addedOrUpdatedMessages += $this->getNumberOfChangedMessages( $event );
			}

			$msg = $this->msg( 'notification-body-translate-mgs-message-added' );
			$msg->params( $addedOrUpdatedMessages );
			return $msg;
		}
	}

	private function getNumberOfChangedMessages( Event $event ): int {
		$changes = $event->getExtraParam( 'changes' );
		$messageCount = 0;
		$messageCount += count( $changes[MessageGroupSubscription::STATE_ADDED] ?? [] );
		$messageCount += count( $changes[MessageGroupSubscription::STATE_UPDATED] ?? [] );

		return $messageCount;
	}
}
