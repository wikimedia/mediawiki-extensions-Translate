<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate;

use MediaWiki\CommentStore\CommentStoreComment;
use MediaWiki\Content\ContentHandler;
use MediaWiki\Content\TextContent;
use MediaWiki\Context\IContextSource;
use MediaWiki\Revision\SlotRecord;
use MediaWiki\Title\Title;
use MediaWiki\User\User;
use MediaWikiLangTestCase;
use MessageGroupTestTrait;
use MockWikiMessageGroup;
use StatusValue;

/**
 * Test for various code using hooks.
 * @group Database
 * @group medium
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @covers \MediaWiki\Extension\Translate\HookHandler
 */
class HookHandlerTest extends MediaWikiLangTestCase {
	use MessageGroupTestTrait;

	protected function setUp(): void {
		parent::setUp();

		$this->overrideConfigValues( [
			'TranslateDocumentationLanguageCode' => 'qqq',
			'TranslateMessageNamespaces' => [ NS_MEDIAWIKI ],
		] );

		$this->setupGroupTestEnvironmentWithGroups( $this, $this->getTestGroups() );
	}

	public function getTestGroups() {
		$messages = [
			'ugakey1' => 'value1',
			'ugakey2' => 'value2',
		];

		$list['testgroup'] = new MockWikiMessageGroup( 'testgroup', $messages );

		return $list;
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

	public function testOnTitleIsAlwaysKnown_nonSpecialNamespace_leavesKnownUnset(): void {
		$isKnown = null;
		$result = HookHandler::onTitleIsAlwaysKnown( Title::makeTitle( NS_MAIN, 'Foo' ), $isKnown );
		$this->assertTrue( $result );
		$this->assertNull( $isKnown );
	}

	public function testOnTitleIsAlwaysKnown_specialNotMyLanguage_leavesKnownUnset(): void {
		$isKnown = null;
		$result = HookHandler::onTitleIsAlwaysKnown( Title::makeTitle( NS_SPECIAL, 'RecentChanges' ), $isKnown );
		$this->assertTrue( $result );
		$this->assertNull( $isKnown );
	}

	public function testOnTitleIsAlwaysKnown_myLanguageNoSubpage_leavesKnownUnset(): void {
		$isKnown = null;
		$result = HookHandler::onTitleIsAlwaysKnown( Title::makeTitle( NS_SPECIAL, 'MyLanguage' ), $isKnown );
		$this->assertTrue( $result );
		$this->assertNull( $isKnown );
	}

	public function testOnTitleIsAlwaysKnown_myLanguageSubpageNotExists_setsKnownFalse(): void {
		$isKnown = null;
		$result = HookHandler::onTitleIsAlwaysKnown(
			Title::makeTitle( NS_SPECIAL, 'MyLanguage/PageThatDoesNotExist' ),
			$isKnown
		);
		$this->assertFalse( $result );
		$this->assertFalse( $isKnown );
	}

	public function testOnTitleIsAlwaysKnown_myLanguageSubpageExists_leavesKnownUnset(): void {
		$this->editPage( 'ExistingPage', 'content' );
		$isKnown = null;
		$result = HookHandler::onTitleIsAlwaysKnown(
			Title::makeTitle( NS_SPECIAL, 'MyLanguage/ExistingPage' ),
			$isKnown
		);
		$this->assertTrue( $result );
		$this->assertNull( $isKnown );
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

	public function testValidateMessageWithNoTitle(): void {
		$context = $this->createMock( IContextSource::class );
		$context->method( 'getTitle' )->willReturn( null );

		$content = $this->createMock( TextContent::class );
		$status = new StatusValue();
		$user = $this->createMock( User::class );

		$result = HookHandler::validateMessage( $context, $content, $status, 'Summary', $user );
		$this->assertTrue( $result );
	}

}
