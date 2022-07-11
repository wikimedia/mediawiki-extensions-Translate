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
 * @coversDefaultClass \namespace MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroupReview;
 */
class MessageGroupReviewTest extends MediaWikiIntegrationTestCase {
	public function testGetSetState(): void {
		$group = new WikiMessageGroup( 'testgroup', 'wewgweg' );
		$messageGroupReview = Services::getInstance()->getMessageGroupReview();
		$ok = $messageGroupReview->changeState( $group, 'fi', 'newstate', FuzzyBot::getUser() );
		$this->assertTrue( $ok, 'state was changed' );

		$state = $messageGroupReview->getState( $group, 'fi' );
		$this->assertEquals( 'newstate', $state, 'state was changed to expected value' );

		$ok = $messageGroupReview->changeState( $group, 'fi', 'newstate', FuzzyBot::getUser() );
		$this->assertFalse( $ok, 'state was not changed again' );
	}
}
