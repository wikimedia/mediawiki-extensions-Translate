<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use MediaWiki\Extension\Translate\SystemUsers\FuzzyBot;
use MediaWikiIntegrationTestCase;
use WikiMessageGroup;

/**
 * Unit tests for message group state change api.
 * @author Niklas Laxström
 * @group Database
 */
class GroupReviewActionApiTest extends MediaWikiIntegrationTestCase {
	public function testGetSetState() {
		$group = new WikiMessageGroup( 'testgroup', 'wewgweg' );

		$ok = GroupReviewActionApi::changeState( $group, 'fi', 'newstate', FuzzyBot::getUser() );
		$this->assertTrue( $ok, 'state was changed' );

		$state = GroupReviewActionApi::getState( $group, 'fi' );
		$this->assertEquals( 'newstate', $state, 'state was changed to expected value' );

		$ok = GroupReviewActionApi::changeState( $group, 'fi', 'newstate', FuzzyBot::getUser() );
		$this->assertFalse( $ok, 'state was not changed again' );
	}
}
