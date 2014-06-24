<?php
/**
 * Unit tests for page translation parser
 *
 * @author Niklas Laxström
 * @copyright Copyright © 2010-2013, Niklas Laxström
 * @license GPL-2.0+
 * @file
 */

/**
 * Custom testing framework for page translation parser.
 * @ingroup PageTranslation
 * @group Database
 */
class PageTranslationParserTest extends MediaWikiTestCase {
	public static function provideTestFiles() {
		$dir = __DIR__;
		$testFiles = glob( "$dir/pagetranslation/*.ptfile" );
		foreach ( $testFiles as $i => $file ) {
			$testFiles[$i] = array( $file );
		}

		return $testFiles;
	}

	/**
	 * @dataProvider provideTestFiles
	 */
	public function testParsing( $file ) {

		$filename = basename( $file );
		list( $pagename, ) = explode( '.', $filename, 2 );
		$title = Title::newFromText( $pagename );
		$translatablePage = TranslatablePage::newFromText( $title, file_get_contents( $file ) );

		$pattern = $file;

		if ( $filename === 'FailNotAtomic.ptfile' ) {
			$this->markTestSkipped( 'Extended validation not yet implemented' );
		}

		$failureExpected = strpos( $pagename, 'Fail' ) === 0;

		if ( $failureExpected ) {
			$this->setExpectedException( 'TPException' );
		}

		$parse = $translatablePage->getParse();
		$this->assertInstanceOf( 'TPParse', $parse );

		if ( file_exists( "$pattern.ptsource" ) ) {
			$source = $parse->getSourcePageText();
			$this->assertEquals( $source, file_get_contents( "$pattern.ptsource" ) );
		}

		if ( file_exists( "$pattern.pttarget" ) ) {
			$target = $parse->getTranslationPageText( MessageCollection::newEmpty( 'foo' ) );
			$this->assertEquals( $target, file_get_contents( "$pattern.pttarget" ) );
		}

		// Custom tests written in php
		if ( file_exists( "$pattern.pttest" ) ) {
			require "$pattern.pttest";
		}
	}
}
