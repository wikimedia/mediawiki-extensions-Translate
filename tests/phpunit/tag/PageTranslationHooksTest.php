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

		$this->setMwGlobals( [
			'wgHooks' => [],
			'wgEnablePageTranslation' => true,
			'wgTranslateTranslationServices' => [],
			'wgTranslateMessageNamespaces' => [ NS_MEDIAWIKI ],
			'wgGroupPermissions' => [
				'sysop' => [
					'translate-manage' => true,
				],
			],
		] );

		TranslateHooks::setupTranslate();

		$this->setTemporaryHook(
			'TranslatePostInitGroups',
			[ $this, 'getTestGroups' ]
		);

		$mg = MessageGroups::singleton();
		$mg->setCache( new WANObjectCache( [ 'cache' => wfGetCache( 'hash' ) ] ) );
		$mg->recache();

		MessageIndex::setInstance( new HashMessageIndex() );
		MessageIndex::singleton()->rebuild();
	}

	public function getTestGroups( array &$groups, array &$deps, array &$autoload ) {
		MessageGroups::getTranslatablePages( $groups, $deps, $autoload );
		$messages = [
			'translated' => 'bunny',
			'untranslated' => 'fanny',
		];
		$groups['test-group'] = new MockWikiMessageGroup( 'test-group', $messages );

		return false;
	}

	public function testRenderTagPage() {
		global $wgParser;

		// Setup objects
		$superUser = $this->getTestSysop()->getUser();
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

	public function testValidateMessagePermission() {
		$plainUser = $this->getMutableTestUser()->getUser();

		$title = Title::newFromText( 'MediaWiki:translated/fi' );
		$content = ContentHandler::makeContent( 'pupuliini', $title );
		$status = new \Status();

		$requestContext = new RequestContext();
		$requestContext->setLanguage( 'en-gb' );
		$requestContext->setTitle( $title );

		PageTranslationHooks::validateMessage( $requestContext, $content, $status, '', $plainUser );

		$this->assertFalse( $status->isOK(),
			'translation with errors is not saved if a normal user is translating.' );
		$this->assertGreaterThan( 0, $status->getErrorsArray(),
			'errors are specified when translation fails validation.' );

		$newStatus = new \Status();
		$superUser = $this->getTestSysop()->getUser();

		PageTranslationHooks::validateMessage( $requestContext, $content, $newStatus, '', $superUser );

		$this->assertTrue( $newStatus->isOK(),
			'translation with errors is saved if user with "translate-manage" permission is translating.' );
	}
}
