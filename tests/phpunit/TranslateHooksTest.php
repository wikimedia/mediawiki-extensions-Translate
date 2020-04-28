<?php
/**
 * Test for various code using hooks.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */

use MediaWiki\Revision\SlotRecord;

/**
 * @group Database
 * @group medium
 */
class TranslateHooksTest extends MediaWikiLangTestCase {
	protected function setUp() : void {
		parent::setUp();

		$this->setMwGlobals( [
			'wgTranslateDocumentationLanguageCode' => 'qqq',
			'wgTranslateTranslationServices' => [],
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

	public function testPreventCategorization() {
		$user = $this->getTestSysop()->getUser();
		$title = Title::makeTitle( NS_MEDIAWIKI, 'ugakey1/fi' );
		$summary = CommentStoreComment::newUnsavedComment( __METHOD__ );
		$wikipage = WikiPage::factory( $title );
		$content = ContentHandler::makeContent( '[[Category:Shouldnotbe]]', $title );

		$updater = $wikipage->newPageUpdater( $user );
		$updater->setContent( SlotRecord::MAIN, $content );
		$updater->saveRevision( $summary );

		$this->assertEquals(
			[],
			$title->getParentCategories(),
			'translation of known message'
		);

		$title = Title::makeTitle( NS_MEDIAWIKI, 'ugakey2/qqq' );
		$wikipage = WikiPage::factory( $title );
		$content = ContentHandler::makeContent( '[[Category:Shouldbe]]', $title );

		$updater = $wikipage->newPageUpdater( $user );
		$updater->setContent( SlotRecord::MAIN, $content );
		$updater->saveRevision( $summary );

		$this->assertEquals(
			[ 'Category:Shouldbe' => 'MediaWiki:ugakey2/qqq' ],
			$title->getParentCategories(),
			'message docs'
		);

		$title = Title::makeTitle( NS_MEDIAWIKI, 'ugakey3/no' );
		$wikipage = WikiPage::factory( $title );
		$content = ContentHandler::makeContent( '[[Category:Shouldbealso]]', $title );

		$updater = $wikipage->newPageUpdater( $user );
		$updater->setContent( SlotRecord::MAIN, $content );
		$updater->saveRevision( $summary );

		$this->assertEquals( [], $title->getParentCategories(), 'unknown message' );
	}

	public function testSearchProfile() {
		$profiles = [
			'files' => [],
			'all' => [],
			'advanced' => []
		];

		$expected = [ 'files', 'translation', 'all', 'advanced' ];

		TranslateHooks::searchProfile( $profiles );

		$this->assertEquals( $expected, array_keys( $profiles ) );
	}

}
