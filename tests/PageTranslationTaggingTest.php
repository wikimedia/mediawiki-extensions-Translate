<?php

/**
 * @group Database
 */
class PageTranslationTaggingText extends MediaWikiTestCase {

	protected function setUp() {
		parent::setUp();
		global $wgEnablePageTranslation;
		$wgEnablePageTranslation = true;
	}

	protected function tearDown() {
		parent::tearDown();
	}

	public function testNormalPage() {
		$title = Title::newFromText( 'Fréttinga' );
		$this->assertNotNull( $title, 'Title is valid' );
		$page = WikiPage::factory( $title );
		$this->assertNotNull( $page, 'WikiPage is valid' );
		$translatablePage = TranslatablePage::newFromTitle( $title );

		$page->doEdit( 'kissa', 'Test case' );

		$this->assertFalse( $translatablePage->getReadyTag( DB_MASTER ), 'No ready tag was added' );
		$this->assertFalse( $translatablePage->getMarkedTag( DB_MASTER ), 'No marked tag was added' );
	}

	public function testTranslatablePage() {
		$title = Title::newFromText( 'Fréttinga' );
		$this->assertNotNull( $title, 'Title is valid' );
		$page = WikiPage::factory( $title );
		$this->assertNotNull( $page, 'WikiPage is valid' );
		$translatablePage = TranslatablePage::newFromTitle( $title );

		$status = $page->doEdit( '<translate>kissa</translate>', 'Test case' );
		$latest = $status->value['revision']->getId();

		$this->assertSame( $latest, $translatablePage->getReadyTag( DB_MASTER ), 'Ready tag was added' );
		$this->assertFalse( $translatablePage->getMarkedTag( DB_MASTER ), 'No marked tag was added' );
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
		$this->assertSame( $latest, $translatablePage->getReadyTag( DB_MASTER ), 'Ready tag was added' );
		$this->assertSame( $latest, $translatablePage->getMarkedTag( DB_MASTER ), 'Marked tag was added' );
		$page->updateRestrictions( array( 'edit' => 'sysop' ), 'Test case' );

		$newLatest = $latest+1;
		$this->assertSame( $newLatest, $translatablePage->getReadyTag( DB_MASTER ), 'Ready tag was updated after protection' );
		$this->assertSame( $latest, $translatablePage->getMarkedTag( DB_MASTER ), 'Marked tag was not updated after protection' );
	}

}
