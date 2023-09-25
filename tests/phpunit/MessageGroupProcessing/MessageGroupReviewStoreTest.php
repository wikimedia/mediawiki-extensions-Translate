<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use MediaWiki\Extension\Translate\Services;
use MediaWiki\Extension\Translate\SystemUsers\FuzzyBot;
use MediaWikiIntegrationTestCase;
use WikiMessageGroup;

/**
 * Unit tests for message group state change api.
 * @author Niklas Laxström
 * @group Database
 * @covers \MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroupReviewStore
 */
class MessageGroupReviewStoreTest extends MediaWikiIntegrationTestCase {
	public function testGetSetState(): void {
		$group = new WikiMessageGroup( 'testgroup', 'wewgweg' );
		$messageGroupReviewStore = Services::getInstance()->getMessageGroupReviewStore();
		$ok = $messageGroupReviewStore->changeState( $group, 'fi', 'newstate', FuzzyBot::getUser() );
		$this->assertTrue( $ok, 'state was changed' );

		$state = $messageGroupReviewStore->getState( $group, 'fi' );
		$this->assertEquals( 'newstate', $state, 'state was changed to expected value' );

		$ok = $messageGroupReviewStore->changeState( $group, 'fi', 'newstate', FuzzyBot::getUser() );
		$this->assertFalse( $ok, 'state was not changed again' );
	}
}
