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

	public static function providerTestGetMarkedText() {
		$cases = array();

		// Inline syntaxs
		$cases[] = array(
			'name',
			'Hello',
			true,
			'<!--T:name--> Hello',
		);

		// Normal syntax
		$cases[] = array(
			'name',
			'Hello',
			false,
			"<!--T:name-->\nHello",
		);

		// Inline should not matter for headings, which have special syntax, but test both values
		$cases[] = array(
			'name',
			'== Hello ==',
			true,
			'== Hello == <!--T:name-->',
		);

		$cases[] = array(
			'name',
			'====== Hello ======',
			false,
			'====== Hello ====== <!--T:name-->',
		);

		return $cases;
	}
}
