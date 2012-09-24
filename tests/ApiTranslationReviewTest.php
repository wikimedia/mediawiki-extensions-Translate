<?php
/**
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2012, Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

require( './SuperUser.php' );

/**
 * @group Database
 */
class ApiTranslationReviewTest extends TranslateTestCase {
	// $users is already taken
	protected $myLittlePonies = array();

	public function setUp() {
		parent::setUp();

		global $wgHooks;
		$wgHooks['TranslatePostInitGroups'] = array( array( $this, 'getTestGroups' ) );

		MessageGroups::clearCache();
		MessageIndexRebuildJob::newJob()->run();

		$this->myLittlePonies[0] = User::newFromName( 'User1' );
		$this->myLittlePonies[1] = User::newFromName( 'User2' );
		foreach ( $this->myLittlePonies as $user ) {
			$user->addToDatabase();
		}

		$title = Title::makeTitle( NS_MEDIAWIKI, 'Key1/fi' );
		$user = User::newFromId( 100 );
		$user->addToDatabase();
		WikiPage::factory( $title )->doEdit( 'trans1', __METHOD__, 0, false, $this->myLittlePonies[0] );

		$title = Title::makeTitle( NS_MEDIAWIKI, 'Key2/fi' );
		WikiPage::factory( $title )->doEdit( '!!FUZZY!!trans2', __METHOD__, 0, false, $this->myLittlePonies[1] );

		$title = Title::makeTitle( NS_MEDIAWIKI, 'Key3/fi' );
		WikiPage::factory( $title )->doEdit( 'unknown message', __METHOD__, 0, false, $this->myLittlePonies[0] );
	}

	public function getTestGroups( &$list ) {
		$list['testgroup'] = new MockMessageGroup( 'testgroup' );
		return false;
	}

	/**
	 * @dataProvider checkProvider
	 */
	public function testSuperUserBlockers( $expected, $user, $page, $comment ) {
		$revision = Revision::newFromTitle( Title::makeTitle( NS_MEDIAWIKI, $page ) );
		$ok = ApiTranslationReview::getReviewBlockers( $user, $revision );
		$this->assertEquals( $expected, $ok, $comment );
	}

	public function checkProvider() {
		return array(
			array(
				'permissiondenied',
				User::newFromId( $this->myLittlePonies[0]->getId() ),
				'Key1/fi',
				'Unpriviledged user is not allowed to change state'
			),
			array(
				'owntranslation',
				MockUser::newMock( $this->myLittlePonies[0]->getId() ),
				'Key1/fi',
				'Cannot approve own translation'
			),
			array(
				'fuzzymessage',
				MockUser::newMock( $this->myLittlePonies[0]->getId() ),
				'Key2/fi',
				'Cannot approve fuzzy translation'
			),
			array(
				'unknownmessage',
				MockUser::newMock( $this->myLittlePonies[0]->getId() ),
				'Key3/fi',
				'Cannot approve unknown translation'
			),
			array(
				'',
				MockUser::newMock( $this->myLittlePonies[1]->getId() ),
				'Key1/fi',
				'Can approve non-fuzzy known non-own translation'
			),
		);
	}

}

class MockUser extends SuperUser {
	public static function newMock( $id ) {
		$u = new SuperUser();
		$u->mId = $id;
		return $u;
	}
}

class MockMessageGroup extends WikiMessageGroup {
	public function __construct( $id ) {
		$this->id = $id;
	}

	public function getNamespace() {
		return NS_MEDIAWIKI;
	}

	public function getDefinitions() {
		return array(
			'key1' => 'value1',
			'key2' => 'value2',
		);
	}
}
