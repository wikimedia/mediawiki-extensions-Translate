<?php
declare( strict_types = 1 );

/**
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @covers \TPSection
 */
class TPSectionTest extends \MediaWikiUnitTestCase {
	/** @dataProvider providerTestGetMarkedText */
	public function testGetMarkedText(
		string $name, string $text, bool $inline, string $expected
	) {
		$section = new TPSection();
		$section->name = $name;
		$section->text = $text;
		$section->setIsInline( $inline );

		$output = $section->getMarkedText();

		$this->assertEquals( $expected, $output );
	}

	/** @dataProvider providerTestGetTextWithVariables */
	public function testGetTextWithVariables( string $text, string $expected ) {
		$section = new TPSection();
		$section->text = $text;

		$output = $section->getTextWithVariables();

		$this->assertEquals( $expected, $output );
	}

	/** @dataProvider providerTestGetTextForTrans */
	public function testGetTextForTrans( string $text, string $expected ) {
		$section = new TPSection();
		$section->text = $text;

		$output = $section->getTextForTrans();

		$this->assertEquals( $expected, $output );
	}

	public static function providerTestGetMarkedText() {
		$cases = [];

		// Inline syntax
		$cases[] = [
			'name',
			'Hello',
			true,
			'<!--T:name--> Hello',
		];

		// Normal syntax
		$cases[] = [
			'name',
			'Hello',
			false,
			"<!--T:name-->\nHello",
		];

		// Inline should not matter for headings, which have special syntax, but test both values
		$cases[] = [
			'name',
			'== Hello ==',
			true,
			'== Hello == <!--T:name-->',
		];

		$cases[] = [
			'name',
			'====== Hello ======',
			false,
			'====== Hello ====== <!--T:name-->',
		];

		return $cases;
	}

	public static function providerTestGetTextWithVariables() {
		$cases = [];

		// syntax
		$cases[] = [
			"<tvar|abc>Peter\n cat!</>",
			'$abc',
		];

		$cases[] = [
			"<tvar|1>Hello</>\n<tvar|2>Hello</>",
			"$1\n$2",
		];

		return $cases;
	}

	public static function providerTestGetTextForTrans() {
		$cases = [];

		// syntax
		$cases[] = [
			"<tvar|abc>Peter\n cat!</>",
			"Peter\n cat!",
		];

		$cases[] = [
			"<tvar|1>Hello</>\n<tvar|2>Hello</>",
			"Hello\nHello",
		];

		return $cases;
	}
}
