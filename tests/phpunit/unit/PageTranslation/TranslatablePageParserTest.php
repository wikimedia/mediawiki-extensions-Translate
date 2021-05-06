<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

use MediaWiki\Extension\Translate\Utilities\ParsingPlaceholderFactory;
use MediaWikiUnitTestCase;

/**
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @covers \MediaWiki\Extension\Translate\PageTranslation\TranslatablePageParser
 */
class TranslatablePageParserTest extends MediaWikiUnitTestCase {
	/** @dataProvider provideTestContainsMarkup */
	public function testContainsMarkup( string $input, bool $expected ) {
		 $parser = new TranslatablePageParser( new ParsingPlaceholderFactory() );
		 $this->assertSame( $expected, $parser->containsMarkup( $input ) );
	}

	public function provideTestContainsMarkup() {
		yield [ 'Plain page', false ];

		yield [ '<languages/>', false ];

		yield [ '<translate>Board, Run!</translate>', true ];

		yield [ '<translate nowrap>Board, Run!</translate>', true ];

		yield [ '<translate unknown="attributes">Board, Run!</translate>', true ];

		yield [ '</translate>', true ];

		yield [ '<nowiki><translate></nowiki>', false ];
	}

	/** @dataProvider provideTestCleanupTags */
	public function testCleanupTags( string $input, string $expected ) {
		$parser = new TranslatablePageParser( new ParsingPlaceholderFactory() );
		$this->assertSame( $expected, $parser->cleanupTags( $input ) );
	}

	public function provideTestCleanupTags() {
		yield 'Unbalanced tag in a section preview' => [
			"== Hello ==\n</translate>",
			'== Hello ==',
		];

		yield 'Unbalanced tags, no whitespace' => [
			"</translate><translate>",
			'',
		];

		yield 'Balanced tags, non-removable whitespace' => [
			"1\n2<translate>3\n4</translate>5\n6",
			"1\n23\n45\n6",
		];

		yield 'Balanced tags, removable whitespace' => [
			"1<translate>\n\n</translate>2",
			'12',
		];

		yield 'Old style translation variable tag is collapsed' => [
			'[[<tvar|wmf>Special:MyLanguage/Wikimedia Foundation</>|Wikimedia Foundation]].',
			'[[Special:MyLanguage/Wikimedia Foundation|Wikimedia Foundation]].',
		];

		yield 'Translation variable tag is collapsed' => [
			'[[<tvar name=wmf>Special:MyLanguage/Wikimedia Foundation</tvar>|Wikimedia Foundation]].',
			'[[Special:MyLanguage/Wikimedia Foundation|Wikimedia Foundation]].',
		];

		yield 'Tag inside a nowiki is retained' => [
			'You can use the <nowiki><translate></nowiki> tag.',
			'You can use the <nowiki><translate></nowiki> tag.',
		];

		yield 'Broken tag is retained' => [
			'What if I <translate and </translate>.',
			'What if I <translate and .',
		];

		yield 'Tag with nowrap is removed' => [
			'<abbr title="<translate nowrap>Careful unselfish true engineer</translate>">CUTE</abbr>',
			'<abbr title="Careful unselfish true engineer">CUTE</abbr>',
		];

		yield 'No content to remove' => [
			'Plain page',
			'Plain page',
		];

		yield 'Language tag should not be removed by this method' => [
			'<languages/>',
			'<languages/>',
		];

		yield 'Unclosed tag is removed' => [
			'<translate>No worries, I will try to remember to close this tag',
			'No worries, I will try to remember to close this tag',

		];

		yield 'Complex old translation variable syntax is parsed and replaced with contents' => [
			'<translate nowrap>I have <tvar|!><:D></></translate>!',
			'I have <:D>!',
		];

		yield 'Complex translation variable syntax is parsed and replaced with contents' => [
			'<translate nowrap>I have <tvar name="--$"><:D></tvar></translate>!',
			'I have <:D>!',
		];

		yield 'No extra newlines is added' => [
			'A<translate>B<translate>C</translate>D</translate>E',
			'ABCDE',
		];

		yield 'Reasonable amount of newlines is stripped' => [
			"A\n<translate>\n\nB</translate>\nC\n<translate>D\n\n\n\n</translate>E",
			"A\n\nB\nC\nD\n\n\nE",

		];

		yield 'Section markers are removed from headings' => [
			"<translate>\n== Head of the header == <!--T:1-->\n</translate>",
			"== Head of the header ==",
		];

		yield 'Section markers are removed, but not other text' => [
			' <!--T:10--> text <!--T:11--> more text <!--T:12--> even more',
			' text more text even more',
		];
	}

	/** @dataProvider provideTestParse */
	public function testParse(
		string $input,
		string $expectedTranslationTemplate,
		string $expectedSourceTemplate,
		array $expectedUnits
	) {
		$parser = new TranslatablePageParser( new TestingParsingPlaceholderFactory() );
		$output = $parser->parse( $input );
		$this->assertSame( $expectedTranslationTemplate, $output->translationPageTemplate() );
		$this->assertSame( $expectedSourceTemplate, $output->sourcePageTemplate() );
		$this->assertEquals( $expectedUnits, $output->units() );
	}

	public function provideTestParse() {
		// Test case 1 //
		$s1 = new TranslationUnit( '== Unit tests ==' );
		$s2 = new TranslationUnit( 'Introduction to unit tests.' );
		$s3 = new TranslationUnit( 'They are fun.' );

		$s4 = new TranslationUnit( 'Smilie' );
		$s4->setCanWrap( false );
		$s4->setIsInline( true );

		yield [
			<<<INPUT
<languages/>
<translate>
== Unit tests ==

Introduction to unit tests.

They are fun.
</translate>

<abbr title="<translate nowrap>Smilie</translate>">:)</abbr>
INPUT
			, <<<TRANSLATION_TEMPLATE
<languages/>
<1>

<2>

<3>

<abbr title="<5>">:)</abbr>
TRANSLATION_TEMPLATE
			, <<<SOURCE_TEMPLATE
<languages/>
<translate>
<1>

<2>

<3>
</translate>

<abbr title="<translate nowrap><5></translate>">:)</abbr>
SOURCE_TEMPLATE
			,
			[
				'<1>' => $s1,
				'<2>' => $s2,
				'<3>' => $s3,
				'<5>' => $s4,
			]
		];

		// Test case 2 //
		$s1 = new TranslationUnit( '== Unit tests ==', '11' );
		$s2 = new TranslationUnit( 'Introduction to unit tests.', '22' );
		$s3 = new TranslationUnit( 'They are fun.', '33' );

		$s4 = new TranslationUnit( 'Smilie', '44' );
		$s4->setCanWrap( false );
		$s4->setIsInline( true );

		yield [
			<<<INPUT
<languages/>
<translate>
== Unit tests == <!--T:11-->

<!--T:22-->
Introduction to unit tests.

<!--T:33-->
They are fun.
</translate>

<abbr title="<translate nowrap><!--T:44--> Smilie</translate>">:)</abbr>
INPUT
			, <<<TRANSLATION_TEMPLATE
<languages/>
<1>

<2>

<3>

<abbr title="<5>">:)</abbr>
TRANSLATION_TEMPLATE
			, <<<SOURCE_TEMPLATE
<languages/>
<translate>
<1>

<2>

<3>
</translate>

<abbr title="<translate nowrap><5></translate>">:)</abbr>
SOURCE_TEMPLATE
			,
			[
				'<1>' => $s1,
				'<2>' => $s2,
				'<3>' => $s3,
				'<5>' => $s4,
			]
		];
	}

	/** @dataProvider provideTestParseSection */
	public function testParseSection(
		string $input,
		string $expectedTemplate,
		array $expectedUnits,
		string $comment
	) {
		$parser = new TranslatablePageParser( new TestingParsingPlaceholderFactory() );
		$canWrap = true;
		$result = $parser->parseSection( $input, $canWrap );
		$this->assertSame( $expectedTemplate, $result['template'], $comment );
		$this->assertEquals( $expectedUnits, $result['sections'], $comment );
	}

	public static function provideTestParseSection() {
		$u = new TranslationUnit( 'Hello' );
		$u->setIsInline( true );
		yield [
			'Hello',
			'<0>',
			[ '<0>' => $u ],
			'No surrounding whitespace',
		];

		$u = new TranslationUnit( 'Hello' );
		yield [
			"\nHello",
			"\n<0>",
			[ '<0>' => $u ],
			'With surrounding whitespace',
		];

		$u0 = new TranslationUnit( 'Hello world' );
		$u1 = new TranslationUnit( 'Bunny' );
		yield [
			"\nHello world\n\nBunny\n",
			"\n<0>\n\n<1>\n",
			[ '<0>' => $u0, '<1>' => $u1 ],
			'Splitting at one empty line',
		];

		$u0 = new TranslationUnit( 'First' );
		$u1 = new TranslationUnit( 'Second' );
		$u2 = new TranslationUnit( 'Third' );
		yield [
			"First\n\n\n\n\nSecond\n\nThird",
			"<0>\n\n\n\n\n<1>\n\n<2>",
			[ '<0>' => $u0, '<1>' => $u1, '<2>' => $u2 ],
			'Splitting with multiple empty lines',
		];
	}
}
