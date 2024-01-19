<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use MediaWiki\Extension\Notifications\Formatters\EchoEventPresentationModel;
use SpecialPage;

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
		if ( $this->isBundled() ) {
			$msg = $this->msg( 'notification-bundle-header-message-group-subscription' );
			$msg->params( $this->getBundleCount() );
		} else {
			$msg = $this->msg( 'notification-header-message-group-subscription' );
			$msg->params( $this->event->getExtraParam( 'groupLabel' ) );
		}

		return $msg;
	}

	public function getCompactHeaderMessage() {
		$msg = $this->msg( parent::getCompactHeaderMessage()->getKey() );
		$msg->params( $this->event->getExtraParam( 'groupLabel' ) );
		$msg->params( $this->getNumberOfChangedMessages() );
		return $msg;
	}

	public function getBodyMessage() {
		$type = $this->event->getType();
		if ( $type === 'translate-mgs-message-added-removed' ) {
			$changes = $this->event->getExtraParam( 'changes' );
			$msg = $this->msg( 'notification-body-translate-mgs-message-added-removed' );

			$addedMessages = count( $changes[ MessageGroupSubscription::STATE_ADDED ] ?? [] );
			$removedMessages = count( $changes[ MessageGroupSubscription::STATE_REMOVED ] ?? [] );

			$msg->params( $addedMessages );
			$msg->params( $removedMessages );
			return $msg;
		}
	}

	public function getNumberOfChangedMessages(): int {
		$changes = $this->event->getExtraParam( 'changes' );
		$messageCount = 0;
		foreach ( $changes as $type => $changeType ) {
			$messageCount += count( $changeType );
		}

		return $messageCount;
	}
}
