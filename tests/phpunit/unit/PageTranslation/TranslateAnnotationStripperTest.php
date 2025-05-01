<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

use MediaWiki\Extension\Translate\Utilities\ParsingPlaceholderFactory;
use PHPUnit\Framework\TestCase;

class TranslateAnnotationStripperTest extends TestCase {
	/**
	 * @covers \MediaWiki\Extension\Translate\PageTranslation\TranslateAnnotationStripper::stripAnnotations
	 * @dataProvider provideTextToStrip
	 */
	public function testStripMarkup( string $text, string $expected, string $message ) {
		$te = new TranslateAnnotationStripper( new TranslatablePageParser( new ParsingPlaceholderFactory() ) );
		$res = $te->stripAnnotations( $text );
		self::assertEquals( $expected, $res );
	}

	public static function provideTextToStrip() {
		return [
		[
			'hello, world',
			'hello, world',
			'no markup'
		],
		[
			'<translate>some text</translate>',
			'some text',
			'basic translate markup'
		],
		[
			'<translate>some text with a <tvar name="var">var</tvar></translate>',
			'some text with a var',
			'translate markup with tvar'
		],
		[
			"<translate>\n<!--T:1--> some text with a\n\n<!--T:2--> <tvar name=\"var\">var</tvar>\n</translate>",
			"some text with a\n\nvar",
			'translate markup with tvar and markers'
		],
		[
			"\n\n<!--T:2-->\nsome text with only a marker",
			"\n\nsome text with only a marker",
			'marker-only markup'
		],
		[
			'some text with a <tvar name="var">var</tvar>',
			'some text with a var',
			'tvar-only markup',
		],
		[
			'<translate>some text with a <tvar|var>var</></translate>',
			'some text with a var',
			'translate markup with obsolete tvar markup'
		],
		];
	}
}
