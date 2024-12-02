<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

use JobQueueGroup;
use MediaWiki\Context\RequestContext;
use MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroups;
use MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroupSubscription;
use MediaWiki\Extension\Translate\MessageGroupProcessing\TranslatablePageStore;
use MediaWiki\Extension\Translate\MessageLoading\MessageIndex;
use MediaWiki\Extension\Translate\MessageProcessing\MessageGroupMetadata;
use MediaWiki\Language\FormatterFactory;
use MediaWiki\Languages\LanguageNameUtils;
use MediaWiki\Linker\LinkRenderer;
use MediaWiki\Page\PageRecord;
use MediaWiki\Page\WikiPageFactory;
use MediaWiki\Revision\SlotRecord;
use MediaWiki\Title\TitleFormatter;
use MediaWiki\Title\TitleParser;
use MediaWikiIntegrationTestCase;
use Wikimedia\Rdbms\IConnectionProvider;
use Wikimedia\Rdbms\IDBAccessObject;

/**
 * @covers \MediaWiki\Extension\Translate\PageTranslation\TranslatablePageMarker
 * @covers \MediaWiki\Extension\Translate\PageTranslation\TranslatablePageMarkOperation
 * @group Database
 */
class TranslatablePageMarkerTest extends MediaWikiIntegrationTestCase {
	protected function setUp(): void {
		parent::setUp();
		$this->overrideConfigValue( 'TranslateMessageIndex', 'hash' );
	}

	private function createTranslatableMarkPage( array $services = [] ): TranslatablePageMarker {
		$getServiceOrMock = fn ( string $className ) => $services[$className] ?? $this->createNoOpMock( $className );
		return new TranslatablePageMarker(
			$getServiceOrMock( IConnectionProvider::class ),
			$getServiceOrMock( JobQueueGroup::class ),
			$getServiceOrMock( LinkRenderer::class ),
			MessageGroups::singleton(),
			$getServiceOrMock( MessageIndex::class ),
			$getServiceOrMock( TitleFormatter::class ),
			$getServiceOrMock( TitleParser::class ),
			$getServiceOrMock( TranslatablePageParser::class ),
			$getServiceOrMock( TranslatablePageStore::class ),
			$getServiceOrMock( TranslatablePageStateStore::class ),
			$getServiceOrMock( TranslationUnitStoreFactory::class ),
			$getServiceOrMock( MessageGroupMetadata::class ),
			$getServiceOrMock( WikiPageFactory::class ),
			$getServiceOrMock( TranslatablePageView::class ),
			$getServiceOrMock( MessageGroupSubscription::class ),
			$getServiceOrMock( FormatterFactory::class )
		);
	}

	private function createMockWithMethods( string $className, array $methods, int $callCounts = 1 ): array {
		$mock = $this->createNoOpMock( $className, array_keys( $methods ) );
		foreach ( $methods as $name => $returnValue ) {
			$mock->expects( $this->atMost( $callCounts ) )->method( $name )->willReturn( $returnValue );
		}
		return [ $className => $mock ];
	}

	private function insertPageAndGetRecord( string $text, string $pageName = 'Fréttinga' ): PageRecord {
		return $this->insertPage( $pageName, $text )['title']->toPageRecord( IDBAccessObject::READ_LATEST );
	}

	public static function provideGetMarkOperation(): array {
		return [
			[ '<translate>Foo</translate>', true, 'tpt-oldrevision' ],
			[ 'Foo', false, 'tpt-notsuitable' ],
			[ '<translate>Foo</translate>', false, null ],
		];
	}

	/** @dataProvider provideGetMarkOperation */
	public function testGetMarkOperation( string $content, bool $changeRevId, ?string $expectedException ): void {
		$services = $this->getServiceContainer();
		$markPage = $this->createTranslatableMarkPage(
			$this->createMockWithMethods( LinkRenderer::class, [ 'makeKnownLink' => 'LINK' ] ) +
			$this->createMockWithMethods( TitleFormatter::class, [ 'getPrefixedText' => 'TITLE' ], 2 ) +
			$this->createMockWithMethods(
				TranslatablePageParser::class, [ 'parse' => new ParserOutput( '', [], [] ) ]
			) +
			[
				TranslationUnitStoreFactory::class => $services->get( 'Translate:TranslationUnitStoreFactory' ),
				TitleParser::class => $services->getTitleParser(),
				MessageGroupMetadata::class => $services->get( 'Translate:MessageGroupMetadata' ),
			]
		);

		$page = $this->insertPageAndGetRecord( $content );
		$revId = $page->getLatest();
		if ( $changeRevId ) {
			++$revId;
		}

		$exception = null;
		try {
			$markPage->getMarkOperation( $page, $revId, true );
		} catch ( TranslatablePageMarkException $e ) {
			$exception = $e;
		}

		if ( $expectedException === null ) {
			$this->assertNull( $exception, 'TranslatablePageMarker should throw no exception' );
		} else {
			$this->assertEquals(
				$expectedException,
				$exception->getMessageObject()->getKey(),
				"TranslatablePageMarker should throw a MarkPageException with message key '$expectedException'"
			);
		}
	}

	public function testMarkAndUnmarkPage(): void {
		$services = $this->getServiceContainer();
		$markPage = $this->createTranslatableMarkPage(
			$this->createMockWithMethods( JobQueueGroup::class, [ 'push' => null ] ) +
			$this->createMockWithMethods( LanguageNameUtils::class, [ 'getLanguageNames' => [ 'en' => 'English' ] ] ) +
			[
				IConnectionProvider::class => $services->getConnectionProvider(),
				TitleParser::class => $services->getTitleParser(),
				TitleFormatter::class => $services->getTitleFormatter(),
				WikiPageFactory::class => $services->getWikiPageFactory(),
				MessageIndex::class => $services->get( 'Translate:MessageIndex' ),
				TranslatablePageParser::class => $services->get( 'Translate:TranslatablePageParser' ),
				TranslatablePageStateStore::class => $services->get( 'Translate:TranslatablePageStateStore' ),
				TranslationUnitStoreFactory::class => $services->get( 'Translate:TranslationUnitStoreFactory' ),
				MessageGroupMetadata::class => $services->get( 'Translate:MessageGroupMetadata' ),
				TranslatablePageView::class => $services->get( 'Translate:TranslatablePageView' ),
				MessageGroupSubscription::class => $services->get( 'Translate:MessageGroupSubscription' ),
				FormatterFactory::class => $services->getFormatterFactory(),
			]
		);

		$pageTitle = str_repeat( 'A', 220 );
		$page = $this->insertPageAndGetRecord( '<translate>Foo</translate><translate>Bar</translate>', $pageTitle );
		$validateUnitTitle = true;
		$operation = $markPage->getMarkOperation( $page, null, $validateUnitTitle );

		$this->assertCount( 3, $operation->getUnits() );
		$this->assertEquals( [], $operation->getDeletedUnits() );
		$this->assertStatusGood( $operation->getUnitValidationStatus() );

		$priorityLanguages = [ 'en', 'de', 'fr' ];
		$translateTitle = true;
		$noFuzzyUnits = [];
		$priorityReason = 'Testing!';
		$forcePriorityLanguages = false;
		$forceLatestSyntaxVersion = true;
		$enableTransclusion = true;
		$units = $markPage->markForTranslation(
			$operation,
			new TranslatablePageSettings(
				$priorityLanguages,
				$forcePriorityLanguages,
				$priorityReason,
				$noFuzzyUnits,
				$translateTitle,
				$forceLatestSyntaxVersion,
				$enableTransclusion
			),
			RequestContext::getMain(),
			$this->getTestSysop()->getUser()
		);
		$this->assertEquals(
			3,
			$units,
			'Marking a page with two translation units and title translation enabled should return 3'
		);

		$page = $this->insertPageAndGetRecord(
			<<<'TEXT'
			<translate>
			<!--T:1-->
			Foo

			<!--T:Page display title-->
			Display title second time

			<!--T:a/b-->
			Slashes

			<!--T:Page display title in very long version-->
			This won’t fit

			<!--T:a|b-->
			MediaWiki doesn’t like this

			<!--T:<a>-->
			You really thought HTML will go through? This should be
			caught by MediaWiki, not our own regex.
			</translate>
			TEXT,
			$pageTitle
		);

		$operation = $markPage->getMarkOperation( $page, null, !$validateUnitTitle );
		$this->assertCount( 7, $operation->getUnits() );
		$this->assertCount( 1, $operation->getDeletedUnits() );
		$validationResult = $operation->getUnitValidationStatus();
		$this->assertEquals(
			[
				[ 'tpt-duplicate', 'Page display title' ],
				[ 'tpt-invalid', 'a/b' ],
				[ 'tpt-unit-title-too-long', 'Page display title in very long version' ],
				[ 'tpt-unit-title-invalid', 'a|b' ],
				[ 'tpt-unit-title-invalid', '<a>' ],
			],
			array_map(
			// Drop everything but the message key and the unit ID parameter
				static fn ( array $error ) => [ $error[0], $error[1] ],
				$validationResult->getErrorsArray()
			)
		);

		// Verify metadata
		$messageGroupMetadata = $this->getServiceContainer()->get( 'Translate:MessageGroupMetadata' );
		$groupId = $operation->getPage()->getMessageGroupId();
		$dbPriorityLanguages = $messageGroupMetadata->get( $groupId, 'prioritylangs' );
		$dbPriorityLanguages = $dbPriorityLanguages ? explode( ',', $dbPriorityLanguages ) : [];
		$this->assertArrayEquals( $priorityLanguages, $dbPriorityLanguages );

		$this->assertEquals( $enableTransclusion, $operation->getPage()->supportsTransclusion() );
		$this->assertEquals( $priorityReason, $messageGroupMetadata->get( $groupId, 'priorityreason' ) );

		$this->assertEquals(
			$forcePriorityLanguages ? 'on' : false,
			$messageGroupMetadata->get( $groupId, 'priorityforce' )
		);

		$expectedSyntaxVersion = $forceLatestSyntaxVersion ? TranslatablePageMarker::LATEST_SYNTAX_VERSION :
				TranslatablePageMarker::DEFAULT_SYNTAX_VERSION;
		$this->assertEquals( $expectedSyntaxVersion, $messageGroupMetadata->get( $groupId, 'version' ) );

		// Test unmarking
		$markPage = $this->createTranslatableMarkPage( [
			WikiPageFactory::class => $services->getWikiPageFactory(),
			TranslatablePageStore::class => $services->get( 'Translate:TranslatablePageStore' ),
		] );
		$markPage->unmarkPage(
			TranslatablePage::newFromTitle( $page ),
			$this->getTestUser()->getUser(),
			RequestContext::getMain(),
			true
		);
		$currentText = $services->getRevisionLookup()->getRevisionByTitle( $page )
			->getContent( SlotRecord::MAIN )
			->getText();
		$this->assertEquals( 'FooBar', $currentText );
	}
}
