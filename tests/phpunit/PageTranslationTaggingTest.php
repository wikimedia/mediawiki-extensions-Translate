<?php

use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\SlotRecord;

/**
 * @group Database
 * @group medium
 */
class PageTranslationTaggingTest extends MediaWikiIntegrationTestCase {
	protected function setUp() : void {
		parent::setUp();

		$this->setMwGlobals( [
			'wgEnablePageTranslation' => true,
			'wgTranslateTranslationServices' => [],
		] );
		TranslateHooks::setupTranslate();
		$this->setTemporaryHook( 'TranslateInitGroupLoaders',
			[ 'TranslatablePageMessageGroupStore::registerLoader' ] );

		$mg = MessageGroups::singleton();
		$mg->setCache( new WANObjectCache( [ 'cache' => wfGetCache( 'hash' ) ] ) );
		$mg->recache();

		MessageIndex::setInstance( new HashMessageIndex() );
		MessageIndex::singleton()->rebuild();
	}

	public function testNormalPage() {
		$title = Title::newFromText( 'Fréttinga' );
		$this->assertNotNull( $title, 'Title is valid' );
		$page = WikiPage::factory( $title );
		$this->assertNotNull( $page, 'WikiPage is valid' );
		$translatablePage = TranslatablePage::newFromTitle( $title );
		$content = ContentHandler::makeContent( 'kissa', $title );

		$updater = $page->newPageUpdater( $this->getTestSysop()->getUser() );
		$updater->setContent( SlotRecord::MAIN, $content );
		$summary = CommentStoreComment::newUnsavedComment( 'Test case' );
		$updater->saveRevision( $summary );

		$this->assertFalse( $translatablePage->getReadyTag(), 'No ready tag was added' );
		$this->assertFalse( $translatablePage->getMarkedTag(), 'No marked tag was added' );
	}

	public function testTranslatablePage() {
		$title = Title::newFromText( 'Fréttinga' );
		$this->assertNotNull( $title, 'Title is valid' );
		$page = WikiPage::factory( $title );
		$this->assertNotNull( $page, 'WikiPage is valid' );
		$translatablePage = TranslatablePage::newFromTitle( $title );

		$content = ContentHandler::makeContent( '<translate>kissa</translate>', $title );

		$updater = $page->newPageUpdater( $this->getTestSysop()->getUser() );
		$updater->setContent( SlotRecord::MAIN, $content );
		$summary = CommentStoreComment::newUnsavedComment( 'Test case' );
		$revRecord = $updater->saveRevision( $summary );

		$latest = $revRecord->getId();

		$this->assertSame( $latest, $translatablePage->getReadyTag(), 'Ready tag was added' );
		$this->assertFalse( $translatablePage->getMarkedTag(), 'No marked tag was added' );
	}

	public function testTranslatablePageWithMarked() {
		$title = Title::newFromText( 'Fréttinga' );
		$this->assertNotNull( $title, 'Title is valid' );
		$page = WikiPage::factory( $title );
		$this->assertNotNull( $page, 'WikiPage is valid' );
		$translatablePage = TranslatablePage::newFromTitle( $title );

		$content = ContentHandler::makeContent( '<translate>koira</translate>', $title );
		$user = $this->getTestSysop()->getUser();

		$updater = $page->newPageUpdater( $user );
		$updater->setContent( SlotRecord::MAIN, $content );
		$summary = CommentStoreComment::newUnsavedComment( 'Test case' );
		$revRecord = $updater->saveRevision( $summary );

		$latest = $revRecord->getId();

		$translatablePage->addMarkedTag( $latest, [ 'foo' ] );
		$this->assertSame( $latest, $translatablePage->getReadyTag(), 'Ready tag was added' );
		$this->assertSame( $latest, $translatablePage->getMarkedTag(), 'Marked tag was added' );

		$cascade = false;
		$page->doUpdateRestrictions(
			[ 'edit' => 'sysop' ],
			[],
			$cascade,
			'Test case',
			$user
		);

		$newLatest = $latest + 1;
		$this->assertSame(
			$newLatest,
			$translatablePage->getReadyTag(),
			'Ready tag was updated after protection'
		);
		$this->assertSame(
			$latest,
			$translatablePage->getMarkedTag(),
			'Marked tag was not updated after protection'
		);

		$page->doUpdateRestrictions( [], [], $cascade, 'Test case', $user );
	}

	public function testTranslationPageRestrictions() {
		$superUser = $this->getTestSysop()->getUser();
		$title = Title::newFromText( 'Translatable page' );
		$page = WikiPage::factory( $title );
		$content = ContentHandler::makeContent( '<translate>Hello</translate>', $title );

		$status = $page->doEditContent(
			$content,
			'New page',
			0,
			false,
			$superUser
		);

		$revision = $status->value['revision']->getId();
		$translatablePage = TranslatablePage::newFromRevision( $title, $revision );
		$translatablePage->addMarkedTag( $revision );
		MessageGroups::singleton()->recache();

		$translationPage = Title::newFromText( 'Translatable page/fi' );
		$pm = MediaWikiServices::getInstance()->getPermissionManager();
		TranslateRenderJob::newJob( $translationPage )->run();
		$this->assertTrue( $pm->userCan( 'read', $superUser, $translationPage ),
			'Users can read existing translation pages' );
		$this->assertFalse( $pm->userCan( 'edit', $superUser, $translationPage ),
			'Users can not edit existing translation pages' );

		$translationPage = Title::newFromText( 'Translatable page/ab' );
		$this->assertTrue( $pm->userCan( 'read', $superUser, $translationPage ),
			'Users can read non-existing translation pages' );
		$this->assertFalse( $pm->userCan( 'edit', $superUser, $translationPage ),
			'Users can not edit non-existing translation pages' );
	}
}
