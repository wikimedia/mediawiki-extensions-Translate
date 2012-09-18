<?php
/**
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2012, Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

/**
 * @group Database
 */
class ApiGroupReviewTest extends MediaWikiTestCase {

	public function testGetSetState() {
		$group = new WikiMessageGroup( 'testgroup', 'wewgweg' );

		$ok = ApiGroupReview::changeState( $group, 'fi', 'newstate', FuzzyBot::getUser() );
		$this->assertTrue( $ok, 'state was changed' );

		$state = ApiGroupReview::getState( $group, 'fi' );
		$this->assertEquals( 'newstate', $state, 'state was changed to expected value' );

		$ok = ApiGroupReview::changeState( $group, 'fi', 'newstate', FuzzyBot::getUser() );
		$this->assertFalse( $ok, 'state was not changed again' );
	}

}
