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
class TranslatablePageTest extends PHPUnit_Framework_TestCase {
	/**
	 * @dataProvider provideTestSectionise
	 */
	public function testSectionise( $input, $pattern, $comment ) {
		$result = TranslatablePage::sectionise( $input );
		$pattern = addcslashes( $pattern, '~' );
		$this->assertRegExp( "~^$pattern$~", $result['template'], $comment );
	}

	public static function provideTestSectionise() {
		// Ugly implicit assumption
		$ph = "\x7fUNIQ[a-z0-9]{8,16}-\d+";

		$cases = [];

		$cases[] = [
			'Hello',
			"$ph",
			'No surrounding whitespace',
		];

		$cases[] = [
			"\nHello",
			"\n$ph",
			'With surrounding whitespace',
		];

		$cases[] = [
			"\nHello world\n\nBunny\n",
			"\n$ph\n\n$ph\n",
			'Splitting at one empty line',
		];

		$cases[] = [
			"First\n\n\n\n\nSecond\n\nThird",
			"$ph\n\n\n\n\n$ph\n\n$ph",
			'Splitting with multiple empty lines',
		];

		return $cases;
	}
}
