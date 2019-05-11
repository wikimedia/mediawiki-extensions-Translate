<?php
/**
 * @group Database
 */
class TranslatablePageMessageGroupStoreTest extends MediaWikiTestCase {

	/**
	 * @var TranslatablePageMessageGroupStore
	 */
	protected $mgTranslateStore;

	public function setup() {
		parent::setup();

		$this->setMwGlobals( [
			'wgEnablePageTranslation' => true
		] );

		$this->mgTranslateStore = new TranslatablePageMessageGroupStore(
			TranslateUtils::getSafeReadDB(),
			new MessageGroupWANCache(
				new WANObjectCache( [ 'cache' => wfGetCache( 'hash' ) ] )
			)
		);
	}

	public function testRecache() {
		$prevGroupCount = count( $this->mgTranslateStore->getGroups() );

		$this->createTranslatePage( 'Upyog', 'Upyog' );

		$countBeforeRecache = count( $this->mgTranslateStore->getGroups() );
		$this->assertEquals( $prevGroupCount, $countBeforeRecache,
			'new groups do not appear unless recache is called' );

		$this->mgTranslateStore->recache();

		$updatedCount = count( $this->mgTranslateStore->getGroups() );
		$this->assertEquals( ( $prevGroupCount + 1 ), $updatedCount,
			'new groups appear after recache is called' );
	}

	public function testGlobalFlag() {
		$this->createTranslatePage( 'Upyon - 22', 'Upyog' );
		$this->mgTranslateStore->recache();
		$prevCount = count( $this->mgTranslateStore->getGroups() );
		$this->assertGreaterThanOrEqual( 1, $prevCount, 'there is atleast 1 ' .
			'translatable page returned' );

		$this->setMwGlobals( [
			'wgEnablePageTranslation' => false
		] );

		$this->mgTranslateStore->recache();
		$updatedCount = count( $this->mgTranslateStore->getGroups() );
		$this->assertEquals( 0, $updatedCount, 'no translatable pages returned' );
	}

	private function createTranslatePage( $title, $content ) {
		// Create new page
		$superUser = $this->getTestSysop()->getUser();
		$translatablePageTitle = Title::newFromText( $title );
		$page = WikiPage::factory( $translatablePageTitle );
		$text = "<translate>$content</translate>";
		$content = ContentHandler::makeContent( $text, $translatablePageTitle );
		$translatablePage = TranslatablePage::newFromTitle( $translatablePageTitle );

		// Create the page
		$editStatus = $page->doEditContent( $content, __METHOD__, 0, false, $superUser );

		// Mark the page for translation
		$latestRevisionId = $editStatus->value['revision']->getId();
		$translatablePage->addMarkedTag( $latestRevisionId );
	}
}
