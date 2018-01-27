<?php
/**
 * Unit tests for class TPSection
 *
 * @author Niklas Laxström
 * @license GPL-2.0+
 * @file
 */

/**
 * Unit tests for class TPSection
 * @ingroup PageTranslation
 */
class TPSectionTest extends PHPUnit_Framework_TestCase {
	/**
	 * @dataProvider providerTestGetMarkedText
	 */
	public function testGetMarkedText( $name, $text, $inline, $expected ) {
		$section = new TPSection();
		$section->name = $name;
		$section->text = $text;
		$section->setIsInline( $inline );

		$output = $section->getMarkedText();

		$this->assertEquals( $expected, $output );
	}

	/**
	 * @dataProvider providerTestTextWithVariables
	 */
	public function testGetTextWithVariables( $text, $expected ) {
		$section = new TPSection();
		$section->text = $text;

		$output = $section->getTextWithVariables();

		$this->assertEquals( $expected, $output );
	}

	/**
	 * @dataProvider providerTestTextWithVariables
	 */
	public function testGetTextForTrans( $text, $expected ) {
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

	public static function providerTestTextWithVariables() {
		$cases = [];

		// syntax
		$cases[] = [
			"<tvar|name>Hello.\n Hello.\n Hello.</>",
			"<tvar|name>Hello.\n Hello.\n Hello.</>",
		];

		return $cases;
	}
}
