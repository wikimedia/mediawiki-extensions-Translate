<?php
/**
 * Test for various code using hooks.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */

use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\RevisionRecord;
use Wikimedia\TestingAccessWrapper;

/**
 * @group Database
 * @group medium
 */
class PageTranslationHooksTest extends MediaWikiIntegrationTestCase {
	protected function setUp(): void {
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
		$this->setTemporaryHook( 'TranslateInitGroupLoaders',
			[ 'TranslatablePageMessageGroupStore::registerLoader' ] );

		$this->setTemporaryHook(
			'TranslatePostInitGroups',
			[ $this, 'getTestGroups' ]
		);

		$mg = MessageGroups::singleton();
		$mg->setCache( new WANObjectCache( [ 'cache' => new HashBagOStuff() ] ) );
		$mg->recache();

		MessageIndex::setInstance( new HashMessageIndex() );
		MessageIndex::singleton()->rebuild();
	}

	public function getTestGroups( array &$groups, array &$deps, array &$autoload ) {
		$messages = [
			'translated' => 'bunny',
			'untranslated' => 'fanny',
		];
		$groups['test-group'] = new MockWikiValidationMessageGroup( 'test-group', $messages );

		return false;
	}

	public function testRenderTagPage() {
		// Setup objects
		$superUser = $this->getTestSysop()->getUser();
		$translatablePageTitle = Title::newFromText( 'Vuosaari' );
		$page = WikiPage::factory( $translatablePageTitle );
		$text = '<translate>pupu</translate>';
		$content = ContentHandler::makeContent( $text, $translatablePageTitle );
		$translatablePage = TranslatablePage::newFromTitle( $translatablePageTitle );
		$parser = MediaWikiServices::getInstance()->getParser()->getFreshParser();
		$options = ParserOptions::newFromUser( $superUser );
		$messageGroups = MessageGroups::singleton();

		// Create the page
		$editStatus = $page->doUserEditContent( $content, $superUser, __METHOD__ );
		$messageGroups->recache();

		// Check that we don't interfere with non-translatable pages at all
		$parserOutput = $parser->parse( $text, $translatablePageTitle, $options );
		$actual = $parserOutput->getExtensionData( 'translate-translation-page' );
		$expected = null;
		$this->assertSame( $expected, $actual, 'Extension data is not set on unmarked source page' );

		// Mark the page for translation
		$latestRevisionId = $editStatus->value['revision-record']->getId();
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
		$this->assertIsArray( $actual, 'Extension data is set on marked page' );
		$actualTitle = Title::makeTitle(
			$actual[ 'sourcepagetitle' ][ 'namespace' ],
			$actual[ 'sourcepagetitle' ][ 'dbkey' ]
		);
		$this->assertSame(
			'Vuosaari',
			$actualTitle->getPrefixedText(),
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
		$status = new Status();

		$requestContext = new RequestContext();
		$requestContext->setLanguage( 'en-gb' );
		$requestContext->setTitle( $title );

		TranslateHooks::validateMessage( $requestContext, $content, $status, '', $plainUser );

		$this->assertFalse( $status->isOK(),
			'translation with errors is not saved if a normal user is translating.' );
		$this->assertGreaterThan( 0, $status->getErrorsArray(),
			'errors are specified when translation fails validation.' );

		$newStatus = new Status();
		$superUser = $this->getTestSysop()->getUser();

		TranslateHooks::validateMessage( $requestContext, $content, $newStatus, '', $superUser );

		$this->assertTrue( $newStatus->isOK(),
			"translation with errors is saved if user with 'translate-manage' permission is translating." );
	}

	/** @covers PageTranslationHooks::updateTranstagOnNullRevisions */
	public function testTagNullRevision() {
		$title = Title::newFromText( 'translated' );
		$status = $this->editPage(
			$title->getPrefixedDBkey(),
			'<translate>Test text</translate>'
		);
		$this->assertTrue( $status->isGood(), 'Sanity: must create revision 1' );
		/** @var RevisionRecord $rev1 */
		$rev1 = $status->getValue()['revision-record'];

		$translatablePage = TranslatablePage::newFromTitle( $title );
		$this->assertEquals(
			$rev1->getId(),
			$translatablePage->getReadyTag(),
			'Sanity: must tag revision 1 ready for translate'
		);

		$wikiPage = WikiPage::newFromID( $title->getArticleID() );

		if ( method_exists( MediaWikiServices::class, 'getProtectPageFactory' ) ) {
			// MW 1.38+
			$protectPage = TestingAccessWrapper::newFromObject(
				$this->getServiceContainer()->getProtectPageFactory()
					->newProtectPage( $wikiPage, $this->getTestUser()->getUser() )
			);

			$nullRev = $protectPage->insertNullProtectionRevision(
				'test comment',
				[ 'edit' => 'sysop' ],
				[ 'edit' => '20200101040404' ],
				false,
				'Testing',
				$this->getTestUser()->getUser()
			);
		} else {
			$nullRev = $wikiPage->insertNullProtectionRevision(
				'test comment',
				[ 'edit' => 'sysop' ],
				[ 'edit' => '20200101040404' ],
				false,
				'Testing',
				$this->getTestUser()->getUser()
			);
		}

		$this->assertNotNull( $nullRev, 'Sanity: must create null revision' );
		$this->assertEquals(
			$translatablePage->getReadyTag(),
			$nullRev->getId(),
			'Must update ready tag for null revision'
		);

		$status = $this->editPage( $title->getPrefixedDBkey(), 'Modified test text' );
		$this->assertTrue( $status->isGood(), 'Sanity: must create revision 2' );
		$this->assertEquals(
			$translatablePage->getReadyTag(),
			$nullRev->getId(),
			'Must not update ready tag for non-null revision'
		);
	}
}
