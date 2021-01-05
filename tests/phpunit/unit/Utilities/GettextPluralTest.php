<?php
/**
 * @file
 * @license GPL-2.0-or-later
 */

use MediaWiki\Extension\Translate\Utilities\GettextPlural;

/** @coversDefaultClass \MediaWiki\Extension\Translate\Utilities\GettextPlural */
class GettextPluralTest extends MediaWikiUnitTestCase {
	/** @covers ::getPluralRule */
	public function testGetPluralRule() {
		$expected = 'nplurals=2; plural=(n != 1);';
		$actual = GettextPlural::getPluralRule( 'en' );
		$this->assertEquals( $expected, $actual, 'valid language code' );

		$expected = '';
		$actual = GettextPlural::getPluralRule( 'EN' );
		$this->assertEquals( $expected, $actual, 'invalid language code' );
	}

	public static function provideGetPluralCount() {
		yield [ 2, 'nplurals=2; plural=(n != 1);' ];
		yield [ 1, 'nplurals=1; plural=0;' ];
		yield [ 5, 'nplurals=5; plural=(n == 1) ? 0 : ( (n == 2) ? 1 : ( (n < 7) ? 2 :' .
			' ( (n < 11) ? 3 : 4 ) ) );' ];
		yield [ 2222, 'nplurals=2222; plural=(n != 1);' ];
	}

	/**
	 * @dataProvider provideGetPluralCount
	 * @covers ::getPluralCount
	 */
	public function testGetPluralCount( $expected, $input ) {
		$actual = GettextPlural::getPluralCount( $input );
		$this->assertEquals( $expected, $actual );
	}

	public static function provideGetPluralCountThrows() {
		yield [ 'nplurals=; plural=(n != 1);' ];
		yield [ 'hello' ];
	}

	/**
	 * @dataProvider provideGetPluralCountThrows
	 * @covers ::getPluralCount
	 */
	public function testGetPluralCountThrows( $input ) {
		$this->expectException( InvalidArgumentException::class );
		GettextPlural::getPluralCount( $input );
	}

	public static function provideHasPlural() {
		yield [ true, 'foo {{PLURAL:GETTEXT|one|many}} bar' ];
		yield [ true, '{{PLURAL:GETTEXT|one|many}} {{PLURAL:GETTEXT|yksi|monta}}' ];
		yield [ false, 'Pupu syö kalkkunaa' ];
		yield [ false, '{{plural:gettext|}}' ];
		yield [ false, '{{PLURAL:GETTEXT}}' ];
		yield [ false, '{{PLURAL:GETEXT|aa|bee' ];
	}

	/**
	 * @dataProvider provideHasPlural
	 * @covers ::hasPlural
	 */
	public function testHasPlural( $expected, $input ) {
		$actual = GettextPlural::hasPlural( $input );
		$this->assertEquals( $expected, $actual );
	}

	public static function provideFlatten() {
		yield [
			'{{PLURAL:GETTEXT|}}',
			[]
		];

		yield [
			'{{PLURAL:GETTEXT|a|b}}',
			[ 'a', 'b' ]
		];
	}

	/**
	 * @dataProvider provideFlatten
	 * @covers ::flatten
	 */
	public function testFlatten( $expected, $input ) {
		$actual = GettextPlural::flatten( $input );
		$this->assertEquals( $expected, $actual );
	}

	public static function provideUnflatten() {
		yield [
			[ 'Hei' ],
			'Hei',
			1
		];

		yield [
			[ 'a', 'b' ],
			'{{PLURAL:GETTEXT|a|b}}',
			2,
		];

		yield [
			[ 'a', 'b' ],
			'{{PLURAL:GETTEXT|a|b|c|d|e}}',
			2,
		];

		yield [
			[ 'pre a middle a post', 'pre b middle b post' ],
			'pre {{PLURAL:GETTEXT|a|b}} middle {{PLURAL:GETTEXT|a|b}} post',
			2,
		];

		yield [
			[ 'pre a|/|daa', 'pre b|/|dau' ],
			'pre {{PLURAL:GETTEXT|a|/|daa|b|/|dau}}',
			2,
		];

		yield [
			[ '{1} item waiting at {0}', '{1} items waiting at {0}' ],
			'{{PLURAL:GETTEXT|{1} item waiting at {0}|{1} items waiting at {0}}}',
			2,
		];
	}

	/**
	 * @dataProvider provideUnflatten
	 * @covers ::unflatten
	 * @covers ::parsePluralForms
	 * @covers ::expandTemplate
	 * @covers ::armour
	 * @covers ::unarmour
	 */
	public function testUnflatten( $expected, $inputText, $inputFormCount ) {
		$actual = GettextPlural::unflatten( $inputText, $inputFormCount );
		$this->assertEquals( $expected, $actual );
	}
}
