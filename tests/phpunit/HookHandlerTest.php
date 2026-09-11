<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate;

use MediaWiki\CommentStore\CommentStoreComment;
use MediaWiki\Content\ContentHandler;
use MediaWiki\Content\TextContent;
use MediaWiki\Context\IContextSource;
use MediaWiki\Revision\SlotRecord;
use MediaWiki\Title\Title;
use MediaWiki\Title\TitleValue;
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

	private function getHookHandler(): HookHandler {
		return new HookHandler(
			$this->getServiceContainer()->getRevisionLookup(),
			$this->getServiceContainer()->getConnectionProvider(),
			$this->getServiceContainer()->getMainConfig(),
			$this->getServiceContainer()->getLanguageNameUtils(),
			$this->getServiceContainer()->getLinkBatchFactory(),
			$this->getServiceContainer()->getSpecialPageFactory()
		);
	}

	public function testOnLinkTargetIsAlwaysKnownBatch_nonSpecialNamespace_leavesKnownUnset(): void {
		$isAlwaysKnown = [ null ];
		$this->getHookHandler()->onLinkTargetIsAlwaysKnownBatch(
			[ new TitleValue( NS_MAIN, 'Foo' ) ], $isAlwaysKnown
		);
		$this->assertNull( $isAlwaysKnown[0] );
	}

	public function testOnLinkTargetIsAlwaysKnownBatch_specialNotMyLanguage_leavesKnownUnset(): void {
		$isAlwaysKnown = [ null ];
		$this->getHookHandler()->onLinkTargetIsAlwaysKnownBatch(
			[ new TitleValue( NS_SPECIAL, 'RecentChanges' ) ], $isAlwaysKnown
		);
		$this->assertNull( $isAlwaysKnown[0] );
	}

	public function testOnLinkTargetIsAlwaysKnownBatch_myLanguageNoSubpage_leavesKnownUnset(): void {
		$isAlwaysKnown = [ null ];
		$this->getHookHandler()->onLinkTargetIsAlwaysKnownBatch(
			[ new TitleValue( NS_SPECIAL, 'MyLanguage' ) ], $isAlwaysKnown
		);
		$this->assertNull( $isAlwaysKnown[0] );
	}

	public function testOnLinkTargetIsAlwaysKnownBatch_myLanguageSubpageNotExists_setsKnownFalse(): void {
		$isAlwaysKnown = [ null ];
		$this->getHookHandler()->onLinkTargetIsAlwaysKnownBatch(
			[ new TitleValue( NS_SPECIAL, 'MyLanguage/PageThatDoesNotExist' ) ], $isAlwaysKnown
		);
		$this->assertFalse( $isAlwaysKnown[0] );
	}

	public function testOnLinkTargetIsAlwaysKnownBatch_myLanguageSubpageExists_leavesKnownUnset(): void {
		$this->editPage( 'ExistingPage', 'content' );
		$isAlwaysKnown = [ null ];
		$this->getHookHandler()->onLinkTargetIsAlwaysKnownBatch(
			[ new TitleValue( NS_SPECIAL, 'MyLanguage/ExistingPage' ) ], $isAlwaysKnown
		);
		$this->assertNull( $isAlwaysKnown[0] );
	}

	public function testOnLinkTargetIsAlwaysKnownBatch_respectsPriorDecision(): void {
		$isAlwaysKnown = [ true ];
		$this->getHookHandler()->onLinkTargetIsAlwaysKnownBatch(
			[ new TitleValue( NS_SPECIAL, 'MyLanguage/PageThatDoesNotExist' ) ], $isAlwaysKnown
		);
		$this->assertTrue( $isAlwaysKnown[0] );
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
