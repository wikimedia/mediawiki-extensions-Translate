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
	public function testCleanupTags( string $input, string $expected, string $comment ) {
		$parser = new TranslatablePageParser( new ParsingPlaceholderFactory() );
		$this->assertSame( $expected, $parser->cleanupTags( $input ), $comment );
	}

	public function provideTestCleanupTags() {
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

		yield [
			'Plain page',
			'Plain page',
			'No content to remove'
		];

		yield [
			'<languages/>',
			'<languages/>',
			'Language tag should not be removed by this method'
		];

		yield [
			'<translate>No worries, I will try to remember to close this tag',
			'No worries, I will try to remember to close this tag',
			'Unclosed tag is removed'
		];

		yield [
			'<translate nowrap>I have <tvar|!><:D></></translate>!',
			'I have <:D>!',
			'Complex tvar syntax is parsed and replaced with contents'
		];

		yield [
			'A<translate>B<translate>C</translate>D</translate>E',
			'ABCDE',
			'Newline handling'
		];

		yield [
			"A\n<translate>\n\nB</translate>\nC\n<translate>D\n\n\n\n</translate>E",
			"A\n\nB\nC\nD\n\n\nE",
			'Newline handling'
		];

		yield [
			"<translate>\n== Head of the header == <!--T:1-->\n</translate>",
			"== Head of the header ==",
			'Section markers are removed from headings'
		];

		yield [
			' <!--T:10--> text <!--T:11--> more text <!--T:12--> even more',
			' text more text even more',
			'Section markers are removed, but not other text'
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
		$s1 = new TranslationUnit();
		$s1->text = '== Unit tests ==';
		$s1->id = -1;

		$s2 = new TranslationUnit();
		$s2->text = 'Introduction to unit tests.';
		$s2->id = -1;

		$s3 = new TranslationUnit();
		$s3->text = 'They are fun.';
		$s3->id = -1;

		$s4 = new TranslationUnit();
		$s4->text = 'Smilie';
		$s4->id = -1;
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
		$s1 = new TranslationUnit();
		$s1->text = '== Unit tests ==';
		$s1->id = '11';

		$s2 = new TranslationUnit();
		$s2->text = 'Introduction to unit tests.';
		$s2->id = '22';

		$s3 = new TranslationUnit();
		$s3->text = 'They are fun.';
		$s3->id = '33';

		$s4 = new TranslationUnit();
		$s4->text = 'Smilie';
		$s4->id = '44';
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
		$u = new TranslationUnit();
		$u->text = 'Hello';
		$u->id = -1;
		$u->setIsInline( true );
		yield [
			'Hello',
			'<0>',
			[ '<0>' => $u ],
			'No surrounding whitespace',
		];

		$u = new TranslationUnit();
		$u->text = 'Hello';
		$u->id = -1;
		yield [
			"\nHello",
			"\n<0>",
			[ '<0>' => $u ],
			'With surrounding whitespace',
		];

		$u0 = new TranslationUnit();
		$u0->text = 'Hello world';
		$u0->id = -1;

		$u1 = new TranslationUnit();
		$u1->text = 'Bunny';
		$u1->id = -1;
		yield [
			"\nHello world\n\nBunny\n",
			"\n<0>\n\n<1>\n",
			[ '<0>' => $u0, '<1>' => $u1 ],
			'Splitting at one empty line',
		];

		$u0 = new TranslationUnit();
		$u0->text = 'First';
		$u0->id = -1;

		$u1 = new TranslationUnit();
		$u1->text = 'Second';
		$u1->id = -1;

		$u2 = new TranslationUnit();
		$u2->text = 'Third';
		$u2->id = -1;
		yield [
			"First\n\n\n\n\nSecond\n\nThird",
			"<0>\n\n\n\n\n<1>\n\n<2>",
			[ '<0>' => $u0, '<1>' => $u1, '<2>' => $u2 ],
			'Splitting with multiple empty lines',
		];
	}
}
