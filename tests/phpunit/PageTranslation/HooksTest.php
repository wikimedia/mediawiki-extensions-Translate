<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

use ContentHandler;
use MediaWiki\Extension\Translate\HookHandler;
use MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroups;
use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\Status\Status;
use MediaWiki\Title\Title;
use MediaWikiIntegrationTestCase;
use MessageGroupTestTrait;
use MockWikiValidationMessageGroup;
use ParserOptions;
use RequestContext;
use Wikimedia\TestingAccessWrapper;

/**
 * Test for various code using hooks.
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @group Database
 * @group medium
 * @covers MediaWiki\Extension\Translate\HookHandler
 * @covers MediaWiki\Extension\Translate\PageTranslation\Hooks
 */
class HooksTest extends MediaWikiIntegrationTestCase {
	use MessageGroupTestTrait;

	protected $tablesUsed = [ 'revtag' ];

	protected function setUp(): void {
		parent::setUp();
		$this->setupGroupTestEnvironment( $this );

		$this->setGroupPermissions( 'sysop', 'translate-manage', true );
	}

	public function getTestGroups() {
		$messages = [
			'translated' => 'bunny',
			'untranslated' => 'fanny',
		];
		$groups['test-group'] = new MockWikiValidationMessageGroup( 'test-group', $messages );

		return $groups;
	}

	public function testRenderTagPage() {
		// Setup objects
		$translatablePageTitle = Title::newFromText( 'Vuosaari' );
		$text = '<translate>pupu</translate>';
		$translatablePage = TranslatablePage::newFromTitle( $translatablePageTitle );
		$parser = $this->getServiceContainer()->getParserFactory()->getInstance();
		$options = ParserOptions::newFromAnon();
		$messageGroups = MessageGroups::singleton();

		// Create the page
		$latestRevisionId = $this->editPage( $translatablePageTitle, $text )->getNewRevision()->getId();
		$messageGroups->recache();

		// Check that we don't interfere with non-translatable pages at all
		$parserOutput = $parser->parse( $text, $translatablePageTitle, $options );
		$actual = $parserOutput->getExtensionData( 'translate-translation-page' );
		$expected = null;
		$this->assertSame( $expected, $actual, 'Extension data is not set on unmarked source page' );

		// Mark the page for translation
		$translatablePage->addMarkedTag( $latestRevisionId );
		$messageGroups->recache();
		$translationPageTitle = Title::newFromText( 'Vuosaari/fi' );
		RenderTranslationPageJob::newJob( $translationPageTitle )->run();

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

	public function testTRANSLATABLEPAGE() {
		// Setup objects
		$translatablePageTitle = Title::newFromText( 'TRANSLATABLEPAGE test' );
		$text = '<translate>pupu</translate>';
		$translatablePage = TranslatablePage::newFromTitle( $translatablePageTitle );
		$parser = $this->getServiceContainer()->getParserFactory()->getInstance();
		$options = ParserOptions::newFromAnon();
		$messageGroups = MessageGroups::singleton();

		// Create the page
		$latestRevisionId = $this->editPage( $translatablePageTitle, $text )->getNewRevision()->getId();
		$messageGroups->recache();

		// Check unmarked source page
		$this->assertSame(
			'',
			$parser->preprocess( '{{TRANSLATABLEPAGE}}', $translatablePageTitle, $options ),
			'TRANSLATABLEPAGE returns empty string on unmarked source page'
		);

		// Mark the page for translation
		$translatablePage->addMarkedTag( $latestRevisionId );
		$messageGroups->recache();
		$translationPageTitle = Title::newFromText( 'TRANSLATABLEPAGE test/fi' );
		RenderTranslationPageJob::newJob( $translationPageTitle )->run();

		// Check marked source page
		$this->assertSame(
			'TRANSLATABLEPAGE test',
			$parser->preprocess( '{{TRANSLATABLEPAGE}}', $translatablePageTitle, $options ),
			'TRANSLATABLEPAGE returns the page title on marked source page'
		);

		// Check translation page
		$this->assertSame(
			'TRANSLATABLEPAGE test',
			$parser->preprocess( '{{TRANSLATABLEPAGE}}', $translationPageTitle, $options ),
			'TRANSLATABLEPAGE returns the source page title on translation page'
		);
	}

	public function testValidateMessagePermission() {
		$this->setupGroupTestEnvironmentWithGroups( $this, $this->getTestGroups() );

		$plainUser = $this->getMutableTestUser()->getUser();

		$title = Title::newFromText( 'MediaWiki:translated/fi' );
		$content = ContentHandler::makeContent( 'pupuliini', $title );
		$status = new Status();

		$requestContext = new RequestContext();
		$requestContext->setLanguage( 'en-gb' );
		$requestContext->setTitle( $title );

		HookHandler::validateMessage( $requestContext, $content, $status, '', $plainUser );

		$this->assertFalse( $status->isOK(),
			'translation with errors is not saved if a normal user is translating.' );
		$this->assertGreaterThan( 0, $status->getErrors(),
			'errors are specified when translation fails validation.' );

		$newStatus = new Status();
		$superUser = $this->getTestSysop()->getUser();

		HookHandler::validateMessage( $requestContext, $content, $newStatus, '', $superUser );

		$this->assertTrue( $newStatus->isOK(),
			"translation with errors is saved if user with 'translate-manage' permission is translating." );
	}

	/** @covers MediaWiki\Extension\Translate\PageTranslation\Hooks::updateTranstagOnNullRevisions */
	public function testTagNullRevision() {
		$title = Title::newFromText( 'translated' );
		$status = $this->editPage(
			$title->getPrefixedDBkey(),
			'<translate>Test text</translate>'
		);
		$this->assertStatusGood( $status, 'Sanity: must create revision 1' );
		/** @var RevisionRecord $rev1 */
		$rev1 = $status->getValue()['revision-record'];

		$translatablePage = TranslatablePage::newFromTitle( $title );
		$this->assertEquals(
			$rev1->getId(),
			$translatablePage->getReadyTag(),
			'Sanity: must tag revision 1 ready for translate'
		);

		$wikiPage = $this->getServiceContainer()->getWikiPageFactory()->newFromID( $title->getArticleID() );

		if ( method_exists( MediaWikiServices::class, 'getProtectPageFactory' ) ) {
			// Planned future change (T292683)
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
		$this->assertStatusGood( $status, 'Sanity: must create revision 2' );
		$this->assertEquals(
			$translatablePage->getReadyTag(),
			$nullRev->getId(),
			'Must not update ready tag for non-null revision'
		);
	}
}
