<?php

use MediaWiki\Content\ContentHandler;
use MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroups;
use MediaWiki\Extension\Translate\PageTranslation\RenderTranslationPageJob;
use MediaWiki\Extension\Translate\PageTranslation\TranslatablePage;
use MediaWiki\Title\Title;

/**
 * @group Database
 * @group medium
 * @covers \MediaWiki\Extension\Translate\PageTranslation\TranslatablePage
 */
class PageTranslationTaggingTest extends MediaWikiIntegrationTestCase {
	use MessageGroupTestTrait;

	protected function setUp(): void {
		parent::setUp();
		$this->setupGroupTestEnvironment( $this );
	}

	public function testNormalPage() {
		$title = Title::newFromText( 'Fréttinga' );
		$this->assertNotNull( $title, 'Title is valid' );
		$page = $this->getServiceContainer()->getWikiPageFactory()->newFromTitle( $title );
		$this->assertNotNull( $page, 'WikiPage is valid' );

		$this->editPage( $title, ContentHandler::makeContent( 'kissa', $title ), 'Test case' );

		$translatablePage = TranslatablePage::newFromTitle( $title );
		$this->assertNull( $translatablePage->getReadyTag(), 'No ready tag was added' );
		$this->assertNull( $translatablePage->getMarkedTag(), 'No marked tag was added' );
	}

	public function testTranslatablePage() {
		$title = Title::newFromText( 'Fréttinga' );
		$this->assertNotNull( $title, 'Title is valid' );
		$page = $this->getServiceContainer()->getWikiPageFactory()->newFromTitle( $title );
		$this->assertNotNull( $page, 'WikiPage is valid' );

		$status = $this->editPage(
			$title,
			ContentHandler::makeContent( '<translate>kissa</translate>', $title ),
			'Test case'
		);
		$latest = $status->value['revision-record']->getId();

		$translatablePage = TranslatablePage::newFromTitle( $title );
		$this->assertSame( $latest, $translatablePage->getReadyTag(), 'Ready tag was added' );
		$this->assertNull( $translatablePage->getMarkedTag(), 'No marked tag was added' );
	}

	public function testTranslatablePageWithMarked() {
		$title = Title::newFromText( 'Fréttinga' );
		$this->assertNotNull( $title, 'Title is valid' );
		$page = $this->getServiceContainer()->getWikiPageFactory()->newFromTitle( $title );
		$this->assertNotNull( $page, 'WikiPage is valid' );

		$status = $this->editPage(
			$title,
			ContentHandler::makeContent( '<translate>koira</translate>', $title ),
			'Test case'
		);
		$latest = $status->value['revision-record']->getId();

		$translatablePage = TranslatablePage::newFromTitle( $title );
		$translatablePage->addMarkedTag( $latest, [ 'foo' ] );
		$this->assertSame( $latest, $translatablePage->getReadyTag(), 'Ready tag was added' );
		$this->assertSame( $latest, $translatablePage->getMarkedTag(), 'Marked tag was added' );

		$cascade = false;
		$user = $this->getTestSysop()->getUser();
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

		$status = $this->editPage(
			$title,
			ContentHandler::makeContent( '<translate>Hello</translate>', $title ),
			'New page',
			NS_MAIN,
			$superUser
		);

		$revisionId = $status->value['revision-record']->getId();
		$translatablePage = TranslatablePage::newFromRevision( $title, $revisionId );
		$translatablePage->addMarkedTag( $revisionId );
		MessageGroups::singleton()->recache();

		$translationPage = Title::newFromText( 'Translatable page/fi' );
		$pm = $this->getServiceContainer()->getPermissionManager();
		RenderTranslationPageJob::newJob( $translationPage )->run();
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
