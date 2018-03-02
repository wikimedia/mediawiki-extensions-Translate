<?php
/**
 * Test for various code using hooks.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */

/**
 * @group Database
 * @group medium
 */
class PageTranslationHooksTest extends MediaWikiTestCase {
	protected function setUp() {
		parent::setUp();

		global $wgHooks;
		$this->setMwGlobals( [
			'wgHooks' => [],
			'wgEnablePageTranslation' => true,
			'wgTranslateTranslationServices' => [],
		] );
		TranslateHooks::setupTranslate();
		$wgHooks['TranslatePostInitGroups'] = [ 'MessageGroups::getTranslatablePages' ];

		$mg = MessageGroups::singleton();
		$mg->setCache( wfGetCache( 'hash' ) );
		$mg->recache();

		MessageIndex::setInstance( new HashMessageIndex() );
		MessageIndex::singleton()->rebuild();
	}

	public function testRenderTagPage() {
		global $wgParser;

		// Setup objects
		$superUser = new MockSuperUser();
		$translatablePageTitle = Title::newFromText( 'Vuosaari' );
		$page = WikiPage::factory( $translatablePageTitle );
		$text = '<translate>pupu</translate>';
		$content = ContentHandler::makeContent( $text, $translatablePageTitle );
		$translatablePage = TranslatablePage::newFromTitle( $translatablePageTitle );
		$parser = $wgParser->getFreshParser();
		$options = ParserOptions::newFromUser( $superUser );
		$messageGroups = MessageGroups::singleton();

		// Create the page
		$editStatus = $page->doEditContent( $content, __METHOD__, 0, false, $superUser );
		$messageGroups->recache();

		// Check that we don't interfere with non-translatable pages at all
		$parserOutput = $parser->parse( $text, $translatablePageTitle, $options );
		$actual = $parserOutput->getExtensionData( 'translate-translation-page' );
		$expected = null;
		$this->assertSame( $expected, $actual, 'Extension data is not set on unmarked source page' );

		// Mark the page for translation
		$latestRevisionId = $editStatus->value['revision']->getId();
		$translatablePage->addMarkedTag( $latestRevisionId );
		$messageGroups->recache();
		$translationPageTitle = Title::newFromText( 'Vuosaari/fi' );
		TranslateRenderJob::newJob( $translationPageTitle )->run();

		// Check that we don't add data to translatable pages
		$parserOutput = $parser->parse( $text, $translatablePageTitle, $options );
		$actual = $parserOutput->getExtensionData( 'translate-translation-page' );
		$expected = null;
		$this->assertSame( $expected, $actual, 'Extension data is not set on marked source page' );

		// Check that our code works for translation pages
		$parserOutput = $parser->parse( 'fi-pupu', $translationPageTitle, $options );
		$actual = $parserOutput->getExtensionData( 'translate-translation-page' );
		$expected = [
			'sourcepagetitle' => $translatablePageTitle,
			'languagecode' => 'fi',
			'messagegroupid' => 'page-Vuosaari',
		];
		$this->assertTrue( is_array( $actual ), 'Extension data is set on marked page' );
		$this->assertSame(
			'Vuosaari',
			$actual[ 'sourcepagetitle' ]->getPrefixedText(),
			'Source page title is correct'
		);
		$this->assertSame(
			'fi',
			$actual[ 'languagecode' ],
			'Language code is correct'
		);
		$this->assertSame(
			'page-Vuosaari',
			$actual[ 'messagegroupid' ],
			'Message group id is correct'
		);
	}
}
