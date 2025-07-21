<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Notifications;

use MediaWiki\Notification\Middleware\FilterMiddleware;
use MediaWiki\Notification\Middleware\FilterMiddlewareAction;
use MediaWiki\Notification\NotificationEnvelope;
use MediaWiki\RecentChanges\RecentChangeNotification;

class FilterTranslationReviewNotificationsMiddleware extends FilterMiddleware {

	protected function filter( NotificationEnvelope $envelope ): FilterMiddlewareAction {
		$notification = $envelope->getNotification();

		if ( $notification instanceof RecentChangeNotification ) {
			$logType = $notification->getRecentChange()->getAttribute( 'rc_log_type' );
			return $logType === 'translationreview' ?
				FilterMiddlewareAction::REMOVE : FilterMiddlewareAction::KEEP;
		}
		return FilterMiddlewareAction::KEEP;
	}

}
