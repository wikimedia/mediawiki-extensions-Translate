<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use GenericParameterJob;
use Job;
use MediaWiki\Extension\Translate\Services;

/**
 * Send Echo notifications to subscribed users
 * @since 2024.04
 * @license GPL-2.0-or-later
 * @author Abijeet Patro
 */
class MessageGroupSubscriptionNotificationJob extends Job implements GenericParameterJob {
	public static function newJob( array $messageChanges ): self {
		$params = [ 'changes' => $messageChanges ];
		return new self( $params );
	}

	public function __construct( array $params ) {
		parent::__construct( 'MessageGroupSubscriptionNotificationJob', $params );
	}

	/** @inheritDoc */
	public function run(): bool {
		$changes = $this->params[ 'changes' ];
		$groupSubscription = Services::getInstance()->getMessageGroupSubscription();
		$groupSubscription->sendNotifications( $changes );

		return true;
	}
}
