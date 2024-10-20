<?php
declare( strict_types = 1 );

use MediaWiki\Extension\Translate\PageTranslation\ParserOutput;
use MediaWiki\Extension\Translate\PageTranslation\ParsingFailure;
use MediaWiki\Extension\Translate\PageTranslation\TestingParsingPlaceholderFactory;
use MediaWiki\Extension\Translate\PageTranslation\TranslatablePageParser;
use MediaWiki\Extension\Translate\PageTranslation\TranslationPage;
use MediaWiki\Parser\Parser;
use MediaWiki\Title\Title;

/**
 * Custom testing framework for page translation parser.
 * @ingroup PageTranslation
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @covers \MediaWiki\Extension\Translate\PageTranslation\TranslatablePageParser
 */
class PageTranslationParserTest extends MediaWikiIntegrationTestCase {
	public static function provideTestFiles() {
		foreach ( glob( __DIR__ . '/pagetranslation/*.ptfile' ) as $file ) {
			yield [ basename( $file, '.ptfile' ), $file ];
		}
	}

	/** @dataProvider provideTestFiles */
	public function testParsing( string $name, string $file ) {
		if ( $name !== 'Whitespace' ) {
			$this->markTestSkipped( 'Extended validation not yet implemented' );
		}

		if ( str_starts_with( $name, 'Fail' ) ) {
			$this->expectException( ParsingFailure::class );
		}

		$title = Title::newFromText( $name );
		$inputSourceText = file_get_contents( $file );
		$parser = new TranslatablePageParser( new TestingParsingPlaceholderFactory() );
		$parserOutput = $parser->parse( $inputSourceText );

		$pattern = dirname( $file ) . "/$name";

		if ( file_exists( "$pattern.ptsource" ) ) {
			$source = $parserOutput->sourcePageTextForSaving();
			$this->assertSame(
				file_get_contents( "$pattern.ptsource" ),
				$source,
				'Marked source text is as expected'
			);
		}

		if ( file_exists( "$pattern.pttarget" ) ) {
			$translationPage = $this->getTranslationPage( $title, $parserOutput );
			$target = $translationPage->generateSourceFromTranslations(
				$this->createStub( Parser::class ), []
			);
			$this->assertEquals(
				file_get_contents( "$pattern.pttarget" ),
				$target,
				'Generated translation page text is as expected'
			);
		}

		// Custom tests written in php
		if ( file_exists( "$pattern.pttest" ) ) {
			require "$pattern.pttest";
		}
	}

	// This is copy of TranslatablePage::getTranslationPage, to mock WikiPageMessageGroup
	private function getTranslationPage(
		Title $title,
		ParserOutput $parserOutput
	): TranslationPage {
		$showOutdated = false;
		$wrapUntranslated = false;

		$languageFactory = $this->getServiceContainer()->getLanguageFactory();
		return new TranslationPage(
			$parserOutput,
			$this->createMock( WikiPageMessageGroup::class ),
			$languageFactory->getLanguage( 'en' ),
			$languageFactory->getLanguage( 'en' ),
			$showOutdated,
			$wrapUntranslated,
			$title
		);
	}
}
