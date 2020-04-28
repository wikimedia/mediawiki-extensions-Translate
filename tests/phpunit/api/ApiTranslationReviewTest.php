<?php
/**
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */

use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\SlotRecord;

/**
 * @group Database
 */
class ApiTranslationReviewTest extends MediaWikiIntegrationTestCase {
	protected function setUp() : void {
		parent::setUp();

		$this->setMwGlobals( [
			'wgGroupPermissions' => [
				'sysop' => [
					'translate-messagereview' => true,
				],
			],
			'wgTranslateMessageNamespaces' => [ NS_MEDIAWIKI ],
		] );
		$this->setTemporaryHook( 'TranslatePostInitGroups', [ $this, 'getTestGroups' ] );

		$mg = MessageGroups::singleton();
		$mg->setCache( new WANObjectCache( [ 'cache' => wfGetCache( 'hash' ) ] ) );
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
		$superUser1 = $this->getMutableTestUser( [ 'sysop', 'bureaucrat' ] )->getUser();

		$superUser2 = $this->getMutableTestUser( [ 'sysop', 'bureaucrat' ] )->getUser();

		$plainUser = $this->getMutableTestUser()->getUser();

		$summary = CommentStoreComment::newUnsavedComment( __METHOD__ );

		$title = Title::makeTitle( NS_MEDIAWIKI, 'Ugakey1/fi' );
		$content = ContentHandler::makeContent( 'trans1', $title );
		$updater = WikiPage::factory( $title )->newPageUpdater( $superUser1 );
		$updater->setContent( SlotRecord::MAIN, $content );
		$updater->saveRevision( $summary );

		$title = Title::makeTitle( NS_MEDIAWIKI, 'Ugakey2/fi' );
		$content = ContentHandler::makeContent( '!!FUZZY!!trans2', $title );
		$updater = WikiPage::factory( $title )->newPageUpdater( $superUser2 );
		$updater->setContent( SlotRecord::MAIN, $content );
		$updater->saveRevision( $summary );

		$title = Title::makeTitle( NS_MEDIAWIKI, 'Ugakey3/fi' );
		$content = ContentHandler::makeContent( 'unknown message', $title );
		$updater = WikiPage::factory( $title )->newPageUpdater( $superUser1 );
		$updater->setContent( SlotRecord::MAIN, $content );
		$updater->saveRevision( $summary );

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
			$revRecord = MediaWikiServices::getInstance()
				->getRevisionLookup()
				->getRevisionByTitle( new TitleValue( NS_MEDIAWIKI, $page ) );
			$ok = ApiTranslationReview::getReviewBlockers( $user, $revRecord );
			$this->assertEquals( $expected, $ok, $comment );
		}
	}
}
