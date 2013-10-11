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
			'wgTranslateCC' => array(),
			'wgTranslateMessageIndex' => array( 'DatabaseMessageIndex' ),
			'wgTranslateWorkflowStates' => false,
			'wgEnablePageTranslation' => true,
			'wgTranslateGroupFiles' => array(),
			'wgTranslateTranslationServices' => array(),
		) );
		TranslateHooks::setupTranslate();
		$wgHooks['TranslatePostInitGroups'] = array();
		MessageGroups::clearCache();
		MessageIndexRebuildJob::newJob()->run();
	}

	public function testNormalPage() {
		$title = Title::newFromText( 'Fréttinga' );
		$this->assertNotNull( $title, 'Title is valid' );
		$page = WikiPage::factory( $title );
		$this->assertNotNull( $page, 'WikiPage is valid' );
		$translatablePage = TranslatablePage::newFromTitle( $title );

		$page->doEdit( 'kissa', 'Test case' );

		$this->assertFalse( $translatablePage->getReadyTag(), 'No ready tag was added' );
		$this->assertFalse( $translatablePage->getMarkedTag(), 'No marked tag was added' );
	}

	public function testTranslatablePage() {
		$title = Title::newFromText( 'Fréttinga' );
		$this->assertNotNull( $title, 'Title is valid' );
		$page = WikiPage::factory( $title );
		$this->assertNotNull( $page, 'WikiPage is valid' );
		$translatablePage = TranslatablePage::newFromTitle( $title );

		$status = $page->doEdit( '<translate>kissa</translate>', 'Test case' );
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

		$status = $page->doEdit( '<translate>koira</translate>', 'Test case' );
		$latest = $status->value['revision']->getId();

		$translatablePage->addMarkedTag( $latest, array( 'foo' ) );
		$this->assertSame( $latest, $translatablePage->getReadyTag(), 'Ready tag was added' );
		$this->assertSame( $latest, $translatablePage->getMarkedTag(), 'Marked tag was added' );
		// @todo FIXME: Deprecated! Needs a user to replace.
		$page->updateRestrictions( array( 'edit' => 'sysop' ), 'Test case' );
		/*
		$page->doUpdateRestrictions(
			array( 'edit' => 'sysop' ),
			array(), // expiry
			false, // cascade allowed
			'Test case', // reason
			$user // user
		);
		*/

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
		$status = $page->doEdit(
			'<translate>Hello</translate>',
			'New page',
			0,
			false,
			$superUser
		);
		$revision = $status->value['revision']->getId();
		$translatablePage = TranslatablePage::newFromRevision( $title, $revision );
		$translatablePage->addMarkedTag( $revision );
		MessageGroups::clearCache();

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
