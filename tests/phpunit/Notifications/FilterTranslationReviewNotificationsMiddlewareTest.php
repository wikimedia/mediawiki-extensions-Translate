<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

use MediaWiki\Extension\Translate\Notifications\FilterTranslationReviewNotificationsMiddleware;
use MediaWiki\Notification\NotificationEnvelope;
use MediaWiki\Notification\NotificationsBatch;
use MediaWiki\Notification\RecipientSet;
use MediaWiki\RecentChanges\RecentChange;
use MediaWiki\Title\Title;
use MediaWiki\Watchlist\RecentChangeNotification;
use MediaWikiIntegrationTestCase;

/**
 * @covers \MediaWiki\Extension\Translate\Notifications\FilterTranslationReviewNotificationsMiddleware
 * @group Database
 */
class FilterTranslationReviewNotificationsMiddlewareTest extends MediaWikiIntegrationTestCase {

	public function testRemovesTranslationNotifications() {
		$title = Title::makeTitle( NS_MAIN, 'Foobar' );
		$alice = $this->getTestSysop()->getUser();
		// phpcs:ignore Generic.PHP.NoSilencedErrors.Discouraged
		$rc1 = @RecentChange::newFromRow( (object)[
			'rc_timestamp' => '20200624000000',
			'rc_comment' => 'Adding something',
			'rc_minor' => false,
			'rc_title' => $title->getDBkey(),
			'rc_namespace' => $title->getNamespace(),
			'rc_deleted' => false,
			'rc_last_oldid' => false,
			'rc_log_type' => 'translationreview',
			'rc_user' => $alice->getId(),
		] );
		// phpcs:ignore Generic.PHP.NoSilencedErrors.Discouraged
		$rc2 = @RecentChange::newFromRow( (object)[
			'rc_timestamp' => '20200624000000',
			'rc_comment' => 'Adding something',
			'rc_minor' => false,
			'rc_title' => $title->getDBkey(),
			'rc_namespace' => $title->getNamespace(),
			'rc_deleted' => false,
			'rc_last_oldid' => false,
			'rc_log_type' => null,
			'rc_user' => $alice->getId(),
		] );

		$first = new RecentChangeNotification( $alice, $title, $rc1, 'changed',
			RecentChangeNotification::WATCHLIST_NOTIFICATION
		);
		$second = new RecentChangeNotification( $alice, $title, $rc2, 'changed',
			RecentChangeNotification::WATCHLIST_NOTIFICATION
		);

		$batch = new NotificationsBatch(
			new NotificationEnvelope( $first, new RecipientSet( [ $alice ] ) ),
			new NotificationEnvelope( $second, new RecipientSet( [ $alice ] ) ),
		);

		$middleware = new FilterTranslationReviewNotificationsMiddleware();
		$middleware->handle( $batch, static fn () => true );
		/** @var NotificationEnvelope<RecentChangeNotification>[] $notifications */
		$notifications = iterator_to_array( $batch );

		$this->assertCount( 1, $notifications );
		$this->assertSame( $notifications[0]->getNotification()->getRecentChange(), $rc2 );
	}

}
