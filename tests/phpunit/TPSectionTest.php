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
class TPSectionTest extends MediaWikiTestCase {

	/**
	 * @dataProvider removeMarkersProvider
	 * @covers TPSection::removeMarkers
	 */
	public function testRemoveMarkers( $input, $expected ) {
		$output = TPSection::removeMarkers( $input );
		$this->assertSame( $expected, $output );
	}

	public function removeMarkersProvider() {
		$testCases = array();

		$testCases[] = array(
			"\n== A == <!--T:42-->\n",
			"\n== A ==\n",
		);

		$testCases[] = array(
			"\n== A ==  <!--T:38-->\n\n<!--T:41-->\n* B",
			"\n== A ==\n\n* B",
		);

		$testCases[] = array(
			"\n<!--T:666-->\nC\n",
			"\nC\n",
		);

		$testCases[] = array(
			"<!--T:1-->\nD",
			"D",
		);

		return $testCases;
	}
}
