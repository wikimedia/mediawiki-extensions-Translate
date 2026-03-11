<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use MediaWikiIntegrationTestCase;

/**
 * @group Database
 * @covers \MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroupSubscriptionStore
 */
class MessageGroupSubscriptionStoreTest extends MediaWikiIntegrationTestCase {
	public function testGetSubscriptionsWithLongGroupIdsReturnsOriginalGroupIds(): void {
		$store = new MessageGroupSubscriptionStore( $this->getServiceContainer()->getConnectionProvider() );
		$userId = $this->getTestUser()->getUser()->getId();

		$commonPrefix = str_repeat( 'x', 500 );
		$longGroupIdA = $commonPrefix . str_repeat( '-group-a', 8 );
		$longGroupIdB = $commonPrefix . str_repeat( '-group-b', 8 );

		$store->addSubscription( $longGroupIdA, $userId );
		$store->addSubscription( $longGroupIdB, $userId );

		$subscriptions = $store->getSubscriptions( [ $longGroupIdA, $longGroupIdB ], $userId );

		$this->assertArrayHasKey( $longGroupIdA, $subscriptions );
		$this->assertArrayHasKey( $longGroupIdB, $subscriptions );
		$this->assertSame( [ $userId ], $subscriptions[$longGroupIdA] );
		$this->assertSame( [ $userId ], $subscriptions[$longGroupIdB] );
		$this->assertArrayNotHasKey(
			MessageGroupSubscriptionStore::getGroupIdForDatabase( $longGroupIdA ),
			$subscriptions
		);
		$this->assertArrayNotHasKey(
			MessageGroupSubscriptionStore::getGroupIdForDatabase( $longGroupIdB ),
			$subscriptions
		);
	}
}
