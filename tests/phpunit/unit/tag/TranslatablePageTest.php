<?php
declare( strict_types = 1 );

use MediaWiki\Linker\LinkTarget;

/**
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @covers \TranslatablePage
 */
class TranslatablePageTest extends MediaWikiUnitTestCase {
	/** @dataProvider provideTestParseTranslationUnit */
	public function testParseTranslationUnit( LinkTarget $input, array $expected ) {
		$output = TranslatablePage::parseTranslationUnit( $input );
		$this->assertEquals( $expected, $output );
	}

	public static function provideTestParseTranslationUnit() {
		// The namespace constant is not defined in unit tests. But it is ignored anway.
		$ns = 1198;

		yield [
			new TitleValue( $ns, 'Template:Foo/bar/SectionName/LanguageCode' ),
			[
				'sourcepage' => 'Template:Foo/bar',
				'section' => 'SectionName',
				'language' => 'LanguageCode',
			]
		];

		yield [
			new TitleValue( $ns, 'Template:Foo/bar/SectionName' ),
			[
				'sourcepage' => 'Template:Foo',
				'section' => 'bar',
				'language' => 'SectionName',
			]
		];

		yield [
			new TitleValue( $ns, 'Foo' ),
			[
				'sourcepage' => '',
				'section' => '',
				'language' => 'Foo',
			]
		];
	}
}
