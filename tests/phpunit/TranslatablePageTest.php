<?php
/**
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @file
 */

use MediaWiki\MediaWikiServices;

/** @covers \TranslatablePage */
class TranslatablePageTest extends MediaWikiIntegrationTestCase {
	use TranslatablePageTestTrait;

	public function setUp(): void {
		parent::setUp();

		$this->setMwGlobals( [
			'wgEnablePageTranslation' => true
		] );
	}

	/** @dataProvider provideTestSectionise */
	public function testSectionise( $input, $pattern, $comment ) {
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
	public function testCleanupTags( $input, $expected, $comment ) {
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

	/**
	 * @dataProvider provideTestParseTranslationUnit
	 */
	public function testParseTranslationUnit( Title $input, array $expected ) {
		$output = TranslatablePage::parseTranslationUnit( $input );
		$this->assertEquals( $expected, $output );
	}

	public static function provideTestParseTranslationUnit() {
		yield [
			Title::newFromText( 'Translations:Template:Foo/bar/SectionName/LanguageCode' ),
			[
				'sourcepage' => 'Template:Foo/bar',
				'section' => 'SectionName',
				'language' => 'LanguageCode',
			]
		];

		yield [
			Title::newFromText( 'Translations:Template:Foo/bar/SectionName' ),
			[
				'sourcepage' => 'Template:Foo',
				'section' => 'bar',
				'language' => 'SectionName',
			]
		];

		yield [
			Title::newFromText( 'Translations:Foo' ),
			[
				'sourcepage' => '',
				'section' => '',
				'language' => 'Foo',
			]
		];
	}

	public function testIsSourcePage() {
		$translatablePage = $this->createMarkedTranslatablePage(
			'Test page', 'Testing page', $this->getTestSysop()->getUser()
		);

		$this->assertTrue(
			TranslatablePage::isSourcePage( $translatablePage->getTitle() )
		);

		$translatablePage->unmarkTranslatablePage();

		MediaWikiServices::getInstance()->getMainWANObjectCache()->clearProcessCache();

		$this->assertFalse(
			TranslatablePage::isSourcePage( $translatablePage->getTitle() )
		);
	}
}
