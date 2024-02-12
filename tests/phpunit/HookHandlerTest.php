<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate;

use ContentHandler;
use HashBagOStuff;
use MediaWiki\CommentStore\CommentStoreComment;
use MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroups;
use MediaWiki\Extension\Translate\MessageLoading\HashMessageIndex;
use MediaWiki\HookContainer\HookContainer;
use MediaWiki\Revision\SlotRecord;
use MediaWiki\Title\Title;
use MediaWikiLangTestCase;
use MessageIndex;
use MockWikiMessageGroup;
use WANObjectCache;

/**
 * Test for various code using hooks.
 * @group Database
 * @group medium
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @covers MediaWiki\Extension\Translate\HookHandler
 */
class HookHandlerTest extends MediaWikiLangTestCase {
	protected function setUp(): void {
		parent::setUp();

		$this->setMwGlobals( [
			'wgTranslateDocumentationLanguageCode' => 'qqq',
			'wgTranslateTranslationServices' => [],
			'wgTranslateMessageNamespaces' => [ NS_MEDIAWIKI ],
		] );
		$this->setTemporaryHook( 'TranslateInitGroupLoaders', HookContainer::NOOP );
		$this->setTemporaryHook( 'TranslatePostInitGroups', [ $this, 'getTestGroups' ] );

		$mg = MessageGroups::singleton();
		$mg->setCache( new WANObjectCache( [ 'cache' => new HashBagOStuff() ] ) );
		$mg->recache();

		MessageIndex::setInstance( new HashMessageIndex() );
		$index = Services::getInstance()->getMessageIndex();
		$index->rebuild();
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
		$title = Title::makeTitle( NS_MEDIAWIKI, 'Ugakey1/fi' );
		$wikipage = $this->getServiceContainer()->getWikiPageFactory()->newFromTitle( $title );
		$content = ContentHandler::makeContent( '[[Category:Shouldnotbe]]', $title );

		$updater = $wikipage
			->newPageUpdater( self::getTestSysop()->getUser() )
			->setContent( SlotRecord::MAIN, $content );
		$updater->saveRevision( CommentStoreComment::newUnsavedComment( __METHOD__ ) );

		$this->assertEquals(
			[],
			$title->getParentCategories(),
			'translation of known message'
		);

		$title = Title::makeTitle( NS_MEDIAWIKI, 'Ugakey2/qqq' );
		$wikipage = $this->getServiceContainer()->getWikiPageFactory()->newFromTitle( $title );
		$content = ContentHandler::makeContent( '[[Category:Shouldbe]]', $title );
		$updater = $wikipage
			->newPageUpdater( self::getTestSysop()->getUser() )
			->setContent( SlotRecord::MAIN, $content );
		$updater->saveRevision( CommentStoreComment::newUnsavedComment( __METHOD__ ) );

		$this->assertEquals(
			[ 'Category:Shouldbe' => 'MediaWiki:Ugakey2/qqq' ],
			$title->getParentCategories(),
			'message docs'
		);

		$title = Title::makeTitle( NS_MEDIAWIKI, 'Ugakey3/no' );
		$wikipage = $this->getServiceContainer()->getWikiPageFactory()->newFromTitle( $title );
		$content = ContentHandler::makeContent( '[[Category:Shouldbealso]]', $title );

		$updater = $wikipage
			->newPageUpdater( self::getTestSysop()->getUser() )
			->setContent( SlotRecord::MAIN, $content );
		$updater->saveRevision( CommentStoreComment::newUnsavedComment( __METHOD__ ) );
		$this->assertEquals( [], $title->getParentCategories(), 'unknown message' );
	}

	public function testSearchProfile() {
		$profiles = [
			'files' => [],
			'all' => [],
			'advanced' => []
		];

		$expected = [ 'files', 'translation', 'all', 'advanced' ];

		HookHandler::searchProfile( $profiles );

		$this->assertEquals( $expected, array_keys( $profiles ) );
	}

}
