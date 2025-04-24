<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Notifications;

use MediaWiki\Notification\Middleware\FilterMiddleware;
use MediaWiki\Notification\NotificationEnvelope;
use MediaWiki\Watchlist\RecentChangeNotification;

class FilterTranslationReviewNotificationsMiddleware extends FilterMiddleware {

	protected function filter( NotificationEnvelope $envelope ): bool {
		$notification = $envelope->getNotification();

		if ( $notification instanceof RecentChangeNotification ) {
			$logType = $notification->getRecentChange()->getAttribute( 'rc_log_type' );
			return $logType === 'translationreview' ? self::REMOVE : self::KEEP;
		}
		return self::KEEP;
	}

}
