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
class ApiTranslationReviewTest extends MediaWikiTestCase {
	protected function setUp() {
		parent::setUp();

		global $wgHooks;
		$this->setMwGlobals( array(
			'wgHooks' => $wgHooks,
			'wgTranslateAC' => array(),
			'wgTranslateCC' => array(),
			'wgTranslateEC' => array(),
			'wgTranslateMessageIndex' => array( 'DatabaseMessageIndex' ),
			'wgTranslateWorkflowStates' => false,
			'wgEnablePageTranslation' => false,
			'wgTranslateGroupFiles' => array(),
			'wgGroupPermissions' => array(),
		) );
		$wgHooks['TranslatePostInitGroups'] = array( array( $this, 'getTestGroups' ) );
		MessageGroups::clearCache();
		MessageIndexRebuildJob::newJob()->run();
	}

	public function getTestGroups( &$list ) {
		$messages = array(
			'key1' => 'value1',
			'key2' => 'value2',
		);

		$list['testgroup'] = new MockWikiMessageGroup( 'testgroup', $messages );
		return false;
	}

	public function testgetReviewBlockers() {
		$superUser1 = new MockSuperUser();
		$superUser1->setId( 1 );

		$superUser2 = new MockSuperUser();
		$superUser2->setId( 2 );

		$plainUser = User::newFromName( 'PlainUser' );

		$title = Title::makeTitle( NS_MEDIAWIKI, 'Key1/fi' );
		WikiPage::factory( $title )->doEdit( 'trans1', __METHOD__, 0, false, $superUser1 );

		$title = Title::makeTitle( NS_MEDIAWIKI, 'Key2/fi' );
		WikiPage::factory( $title )->doEdit( '!!FUZZY!!trans2', __METHOD__, 0, false, $superUser2 );

		$title = Title::makeTitle( NS_MEDIAWIKI, 'Key3/fi' );
		WikiPage::factory( $title )->doEdit( 'unknown message', __METHOD__, 0, false, $superUser1 );

		$testcases = array(
			array(
				'permissiondenied',
				$plainUser,
				'Key1/fi',
				'Unpriviledged user is not allowed to change state'
			),
			array(
				'owntranslation',
				$superUser1,
				'Key1/fi',
				'Cannot approve own translation'
			),
			array(
				'fuzzymessage',
				$superUser1,
				'Key2/fi',
				'Cannot approve fuzzy translation'
			),
			array(
				'unknownmessage',
				$superUser1,
				'Key3/fi',
				'Cannot approve unknown translation'
			),
			array(
				'',
				$superUser2,
				'Key1/fi',
				'Can approve non-fuzzy known non-own translation'
			),
		);

		foreach ( $testcases as $case ) {
			list( $expected, $user, $page, $comment ) = $case;
			$revision = Revision::newFromTitle( Title::makeTitle( NS_MEDIAWIKI, $page ) );
			$ok = ApiTranslationReview::getReviewBlockers( $user, $revision );
			$this->assertEquals( $expected, $ok, $comment );
		}
	}
}


