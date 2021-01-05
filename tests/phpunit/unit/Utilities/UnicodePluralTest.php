<?php
/**
 * @file
 * @license GPL-2.0-or-later
 */

use MediaWiki\Extension\Translate\Utilities\UnicodePlural;

/** @coversDefaultClass \MediaWiki\Extension\Translate\Utilities\UnicodePlural */
class UnicodePluralTest extends MediaWikiUnitTestCase {
	/** @covers ::getPluralKeywords */
	public function testGetPluralKeywords() {
		$expected = [ 'one', 'other' ];
		$actual = UnicodePlural::getPluralKeywords( 'en' );
		$this->assertEquals( $expected, $actual, 'valid language code' );

		$expected = null;
		$actual = UnicodePlural::getPluralKeywords( 'EN' );
		$this->assertEquals( $expected, $actual, 'invalid language code' );
	}

	public static function provideHasPlural() {
		yield [ true, 'foo {{PLURAL|one=one|many}} bar' ];
		yield [ true, '{{PLURAL|one=one|many}} {{PLURAL|one=yksi|monta}}' ];
		yield [ false, 'Pupu syö kalkkunaa' ];
		yield [ false, '{{plural|unicode|}}' ];
		yield [ false, '{{PLURAL}}' ];
		yield [ false, '{{PLURAL:aa|bee' ];
	}

	/**
	 * @dataProvider provideHasPlural
	 * @covers ::hasPlural
	 */
	public function testHasPlural( $expected, $input ) {
		$actual = UnicodePlural::hasPlural( $input );
		$this->assertEquals( $expected, $actual );
	}

	public static function provideFlattenList() {
		yield [
			'{{PLURAL|}}',
			[]
		];

		yield [
			'{{PLURAL|one=a|b}}',
			[ [ 'one', 'a' ], [ 'other', 'b' ] ],
		];

		yield [
			'{{PLURAL|one=a|one=b}}',
			[ [ 'one', 'a' ], [ 'one', 'b' ] ],
		];
	}

	/**
	 * @dataProvider provideFlattenList
	 * @covers ::flattenList
	 * @covers ::formatForm
	 */
	public function testFlattenList( $expected, $input ) {
		$actual = UnicodePlural::flattenList( $input );
		$this->assertEquals( $expected, $actual );
	}

	public static function provideFlattenMap() {
		yield [
			'{{PLURAL|}}',
			[]
		];

		yield [
			'{{PLURAL|one=a|b}}',
			[ 'one' => 'a', 'other' => 'b' ],
		];

		yield [
			'{{PLURAL|one=a|two=b|c}}',
			[ 'one' => 'a', 'two' => 'b', 'other' => 'c' ],
		];
	}

	/**
	 * @dataProvider provideFlattenMap
	 * @covers ::flattenMap
	 */
	public function testFlattenMap( $expected, $input ) {
		$actual = UnicodePlural::flattenMap( $input );
		$this->assertEquals( $expected, $actual );
	}

	public static function provideUnflatten() {
		yield [
			[ 'other' => 'Hei' ],
			'Hei',
			[ 'other' ]
		];

		yield [
			[ 'one' => 'a', 'other' => 'b' ],
			'{{PLURAL|one=a|b}}',
			[ 'one', 'other' ],
		];

		yield [
			[ 'one' => 'a', 'other' => 'e' ],
			'{{PLURAL|one=a|b|c|d|e}}',
			[ 'one', 'other' ],
		];

		yield [
			[ 'one' => 'pre a middle a post', 'other' => 'pre b middle b post' ],
			'pre {{PLURAL|one=a|b}} middle {{PLURAL|one=a|b}} post',
			[ 'one', 'other' ],
		];

		yield [
			[ 'a' => 'A', 'b' => 'B', 'c' => '' ],
			"{{PLURAL| a = A |\nb\n=\nB\n}}",
			[ 'a', 'b', 'c' ]
		];

		yield [
			[ 'a' => 'A' ],
			"{{PLURAL| a = A |\nb\n=\nB\n}}",
			[ 'a' ]
		];
	}

	/**
	 * @dataProvider provideUnflatten
	 * @covers ::unflatten
	 * @covers ::parsePluralForms
	 * @covers ::expandTemplate
	 */
	public function testUnflatten( $expected, $inputText, $keywords ) {
		$actual = UnicodePlural::unflatten( $inputText, $keywords );
		$this->assertEquals( $expected, $actual );
	}

	public static function provideConvertFormListToFormMap() {
		yield [
			[ 'one' => 'B', 'other' => 'C' ],
			[ [ 'one', 'A' ], [ 'one', 'B' ], [ 'other', 'C' ] ],
			[ 'one', 'other' ],
		];

		yield [
			[ 'a' => 'A', 'b' => 'B', 'c' => '' ],
			[ [ 'a', 'A' ], [ 'b', 'B' ] ],
			[ 'a', 'b', 'c' ]
		];

		yield [
			[ 'a' => 'A' ],
			[ [ 'a', 'A' ], [ 'b', 'B' ] ],
			[ 'a' ]
		];
	}

	/**
	 * @dataProvider provideConvertFormListToFormMap
	 * @covers ::convertFormListToFormMap
	 */
	public function testConvertFormListToFormMap( $expected, array $formsList, array $keywords ) {
		$actual = UnicodePlural::convertFormListToFormMap( $formsList, $keywords );
		$this->assertEquals( $expected, $actual );
	}
}
