<?php
/**
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */

/**
 * @group Database
 */
class ApiTranslationReviewTest extends MediaWikiTestCase {
	protected function setUp() {
		parent::setUp();

		global $wgHooks;
		$this->setMwGlobals( [
			'wgHooks' => $wgHooks,
			'wgGroupPermissions' => [],
			'wgTranslateMessageNamespaces' => [ NS_MEDIAWIKI ],
		] );
		$wgHooks['TranslatePostInitGroups'] = [ [ $this, 'getTestGroups' ] ];
		$mg = MessageGroups::singleton();
		$mg->setCache( wfGetCache( 'hash' ) );
		$mg->recache();

		MessageIndex::setInstance( new HashMessageIndex() );
		MessageIndex::singleton()->rebuild();
	}

	public function getTestGroups( &$list ) {
		$messages = [
			'ugakey1' => 'value1',
			'ugakey2' => 'value2',
		];

		$list['testgroup'] = new MockWikiMessageGroup( 'testgroup', $messages );

		return false;
	}

	public function testgetReviewBlockers() {
		$superUser1 = new MockSuperUser();
		$superUser1->setId( 1 );

		$superUser2 = new MockSuperUser();
		$superUser2->setId( 2 );

		$plainUser = User::newFromName( 'PlainUser' );

		$title = Title::makeTitle( NS_MEDIAWIKI, 'Ugakey1/fi' );
		$content = ContentHandler::makeContent( 'trans1', $title );
		WikiPage::factory( $title )->doEditContent( $content, __METHOD__, 0, false, $superUser1 );

		$title = Title::makeTitle( NS_MEDIAWIKI, 'Ugakey2/fi' );
		$content = ContentHandler::makeContent( '!!FUZZY!!trans2', $title );
		WikiPage::factory( $title )->doEditContent( $content, __METHOD__, 0, false, $superUser2 );

		$title = Title::makeTitle( NS_MEDIAWIKI, 'Ugakey3/fi' );
		$content = ContentHandler::makeContent( 'unknown message', $title );
		WikiPage::factory( $title )->doEditContent( $content, __METHOD__, 0, false, $superUser1 );

		$testcases = [
			[
				'permissiondenied',
				$plainUser,
				'Ugakey1/fi',
				'Unpriviledged user is not allowed to change state'
			],
			[
				'owntranslation',
				$superUser1,
				'Ugakey1/fi',
				'Cannot approve own translation'
			],
			[
				'fuzzymessage',
				$superUser1,
				'Ugakey2/fi',
				'Cannot approve fuzzy translation'
			],
			[
				'unknownmessage',
				$superUser1,
				'Ugakey3/fi',
				'Cannot approve unknown translation'
			],
			[
				'',
				$superUser2,
				'Ugakey1/fi',
				'Can approve non-fuzzy known non-own translation'
			],
		];

		foreach ( $testcases as $case ) {
			list( $expected, $user, $page, $comment ) = $case;
			$revision = Revision::newFromTitle( Title::makeTitle( NS_MEDIAWIKI, $page ) );
			$ok = ApiTranslationReview::getReviewBlockers( $user, $revision );
			$this->assertEquals( $expected, $ok, $comment );
		}
	}
}
