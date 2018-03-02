<?php
/**
 * Unit tests for class TPSection
 *
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @file
 */

/**
 * Unit tests for class TPSection
 * @ingroup PageTranslation
 */
class TranslatablePageTest extends PHPUnit\Framework\TestCase {
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

	/**
	 * @dataProvider provideTestCleanupTags
	 */
	public function testCleanupTags( $input, $expected, $comment ) {
		$output = TranslatablePage::cleanupTags( $input );
		$this->assertEquals( $expected, $output, $comment );
	}

	public static function provideTestCleanupTags() {
		$cases = [];

		$cases[] = [
			"== Hello ==\n</translate>",
			'== Hello ==',
			'Unbalanced tag in a section preview',
		];

		$cases[] = [
			"</translate><translate>",
			'',
			'Unbalanced tags, no whitespace',
		];

		$cases[] = [
			"1\n2<translate>3\n4</translate>5\n6",
			"1\n23\n45\n6",
			'Unbalanced tags, non-removable whitespace',
		];

		$cases[] = [
			"1<translate>\n\n</translate>2",
			'12',
			'Unbalanced tags, removable whitespace',
		];

		$cases[] = [
			'[[<tvar|wmf>Special:MyLanguage/Wikimedia Foundation</>|Wikimedia Foundation]].',
			'[[Special:MyLanguage/Wikimedia Foundation|Wikimedia Foundation]].',
			'TVAR tag is collapsed',
		];

		$cases[] = [
			'You can use the <nowiki><translate></nowiki> tag.',
			'You can use the <nowiki><translate></nowiki> tag.',
			'Tag inside a nowiki is retained',
		];

		$cases[] = [
			'What if I <translate and </translate>.',
			'What if I <translate and .',
			'Broken tag is retained',
		];

		return $cases;
	}
}
