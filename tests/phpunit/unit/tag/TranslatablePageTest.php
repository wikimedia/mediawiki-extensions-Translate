<?php
declare( strict_types = 1 );

use MediaWiki\Linker\LinkTarget;

/**
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @covers \TranslatablePage
 */
class TranslatablePageTest extends \MediaWikiUnitTestCase {
	/** @dataProvider provideTestGetTranslationPageText */
	public function testGetTranslationPageText( string $pageContents, string $expected ) {
		$title = Title::makeTitle( NS_MAIN, __CLASS__ );
		$page = TranslatablePage::newFromText( $title, $pageContents );
		$prefix = $title->getPrefixedDBkey() . '/';
		$parse = $page->getParse();

		$collection = [];
		$actual = $parse->getTranslationPageText( $collection );
		$this->assertEquals(
			$expected,
			$actual,
			'Variable declarations are substituted when no translation'
		);

		foreach ( $parse->sections as $section ) {
			$key = $prefix . $section->id;
			$message = new FatMessage( $key, $section->getText() );
			$message->setTranslation( $section->getText() );
			$collection[$key] = $message;
		}

		$actual = $parse->getTranslationPageText( $collection );
		$this->assertEquals(
			$expected,
			$actual,
			'Variable declarations are substituted in source language'
		);

		foreach ( $parse->sections as $section ) {
			$key = $prefix . $section->id;
			$message = new FatMessage( $key, $section->getText() );
			$message->setTranslation( $section->getTextForTrans() );
			$collection[$key] = $message;
		}
		$actual = $parse->getTranslationPageText( $collection );
		$this->assertEquals(
			$expected,
			$actual,
			'Variable declarations are substituted in translation'
		);
	}

	public function provideTestGetTranslationPageText() {
		yield [
			'<translate>Hello <tvar|abc>peter!</></translate>',
			'Hello peter!'
		];

		yield [
			'<translate nowrap>Hello <tvar|abc>peter!</></translate>',
			'Hello peter!'
		];
	}

	/** @dataProvider provideTestSectionise */
	public function testSectionise( string $input, string $pattern, string $comment ) {
		$canWrap = true;
		$result = TranslatablePage::sectionise( $input, $canWrap );
		$pattern = addcslashes( $pattern, '~' );
		$this->assertRegExp( "~^$pattern$~", $result['template'], $comment );
	}

	public static function provideTestSectionise() {
		// Ugly implicit assumption
		$ph = "\x7fUNIQ[a-z0-9]{8,16}-\d+";

		$cases = [];

		yield [
			'Hello',
			"$ph",
			'No surrounding whitespace',
		];

		yield [
			"\nHello",
			"\n$ph",
			'With surrounding whitespace',
		];

		yield [
			"\nHello world\n\nBunny\n",
			"\n$ph\n\n$ph\n",
			'Splitting at one empty line',
		];

		yield [
			"First\n\n\n\n\nSecond\n\nThird",
			"$ph\n\n\n\n\n$ph\n\n$ph",
			'Splitting with multiple empty lines',
		];

		return $cases;
	}

	/** @dataProvider provideTestCleanupTags */
	public function testCleanupTags( string $input, string $expected, string $comment ) {
		$output = TranslatablePage::cleanupTags( $input );
		$this->assertEquals( $expected, $output, $comment );
	}

	public static function provideTestCleanupTags() {
		yield [
			"== Hello ==\n</translate>",
			'== Hello ==',
			'Unbalanced tag in a section preview',
		];

		yield [
			"</translate><translate>",
			'',
			'Unbalanced tags, no whitespace',
		];

		yield [
			"1\n2<translate>3\n4</translate>5\n6",
			"1\n23\n45\n6",
			'Balanced tags, non-removable whitespace',
		];

		yield [
			"1<translate>\n\n</translate>2",
			'12',
			'Balanced tags, removable whitespace',
		];

		yield [
			'[[<tvar|wmf>Special:MyLanguage/Wikimedia Foundation</>|Wikimedia Foundation]].',
			'[[Special:MyLanguage/Wikimedia Foundation|Wikimedia Foundation]].',
			'TVAR tag is collapsed',
		];

		yield [
			'You can use the <nowiki><translate></nowiki> tag.',
			'You can use the <nowiki><translate></nowiki> tag.',
			'Tag inside a nowiki is retained',
		];

		yield [
			'What if I <translate and </translate>.',
			'What if I <translate and .',
			'Broken tag is retained',
		];

		yield [
			'<abbr title="<translate nowrap>Careful unselfish true engineer</translate>">CUTE</abbr>',
			'<abbr title="Careful unselfish true engineer">CUTE</abbr>',
			'Nowrap is removed',
		];
	}

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
