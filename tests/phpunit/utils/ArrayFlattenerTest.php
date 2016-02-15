<?php
/**
 * Unit tests.
 *
 * @author Niklas Laxström
 * @file
 * @license GPL-2.0+
 */

class ArrayFlattenerTest extends MediaWikiTestCase {
	/**
	 * @dataProvider provideTestFlatten
	 */
	public function testFlatten( $sep, $input, $expected ) {
		$flattener = new ArrayFlattener( $sep );
		$output = $flattener->flatten( $input );
		$this->assertEquals( $expected, $output );
	}

	/**
	 * @dataProvider provideTestFlatten
	 */
	public function testUnflatten( $sep, $expected, $input ) {
		$flattener = new ArrayFlattener( $sep );
		$output = $flattener->unflatten( $input );
		$this->assertEquals( $expected, $output );
	}

	public static function provideTestFlatten() {
		$cases = array();
		$cases[] = array(
			'.',
			array( 'a' => 1 ),
			array( 'a' => 1 ),
		);

		$cases[] = array(
			'.',
			array( 'a' => array( 'b' => array( 'c' => 1, 'd' => 2 ) ) ),
			array( 'a.b.c' => 1, 'a.b.d' => 2 ),
		);

		return $cases;
	}
}
