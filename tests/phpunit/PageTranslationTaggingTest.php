<?php

/**
 * @group Database
 * @group medium
 */
class PageTranslationTaggingText extends MediaWikiTestCase {
	protected function setUp() {
		parent::setUp();

		global $wgHooks;
		$this->setMwGlobals( array(
			'wgHooks' => $wgHooks,
			'wgEnablePageTranslation' => true,
			'wgTranslateTranslationServices' => array(),
		) );
		TranslateHooks::setupTranslate();
		$wgHooks['TranslatePostInitGroups'] = array( 'MessageGroups::getTranslatablePages' );

		$mg = MessageGroups::singleton();
		$mg->setCache( wfGetCache( 'hash' ) );
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

		$page->doEditContent( $content,  'Test case' );

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
		$status = $page->doEditContent( $content, 'Test case' );
		$latest = $status->value['revision']->getId();

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
		$status = $page->doEditContent( $content, 'Test case' );
		$latest = $status->value['revision']->getId();

		$translatablePage->addMarkedTag( $latest, array( 'foo' ) );
		$this->assertSame( $latest, $translatablePage->getReadyTag(), 'Ready tag was added' );
		$this->assertSame( $latest, $translatablePage->getMarkedTag(), 'Marked tag was added' );

		global $wgUser;
		$cascade = false;
		$page->doUpdateRestrictions(
			array( 'edit' => 'sysop' ),
			array(),
			$cascade,
			'Test case',
			$wgUser
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
	}

	public function testTranslationPageRestrictions() {
		$superUser = new MockSuperUser();
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
		TranslateRenderJob::newJob( $translationPage )->run();
		$this->assertTrue( $translationPage->userCan( 'read', $superUser ),
			'Users can read existing translation pages' );
		$this->assertFalse( $translationPage->userCan( 'edit', $superUser ),
			'Users can not edit existing translation pages' );

		$translationPage = Title::newFromText( 'Translatable page/ab' );
		$this->assertTrue( $translationPage->userCan( 'read', $superUser ),
			'Users can read non-existing translation pages' );
		$this->assertFalse( $translationPage->userCan( 'edit', $superUser ),
			'Users can not edit non-existing translation pages' );
	}
}
