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

	/**
	 * @dataProvider provideTestCLDRPlurals
	 */
	public function testFlattenCLDRPlurals( $sep, $input, $expected ) {
		$flattener = new ArrayFlattener( $sep, true );
		$output = $flattener->flatten( $input );
		$this->assertEquals( $expected, $output );
	}

	/**
	 * @dataProvider provideTestCLDRPlurals
	 */
	public function testUnflattenCLDRPlurals( $sep, $expected, $input ) {
		$flattener = new ArrayFlattener( $sep, true );
		$output = $flattener->unflatten( $input );
		$this->assertEquals( $expected, $output );
	}

	/**
	 * @expectedException MWException
	 * @expectedExceptionMessage Reserved plural keywords mixed with other keys
	 * @dataProvider provideTestMixedCLDRPlurals
	 */
	public function testFlattenMixedCLDRPlurals( $input ) {
		$flattener = new ArrayFlattener( '.', true );
		$flattener->flatten( $input );
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

		// By default, CLDR plural keywords should be treated like any other key
		$cases[] = array(
			'/',
			array( 'number' => array( 'one' => '1', 'other' => '999' ) ),
			array( 'number/one' => '1', 'number/other' => '999' )
		);

		return $cases;
	}

	public static function provideTestCLDRPlurals() {
		$cases = array();

		// We include some non-plural data to ensure it is processed correctly
		$cases[] = array(
			'/',
			array(
				'cat' => 'An amount of cats',
				'mice' => array(
					'Frankie',
					'Benjy'
				),
				'dog or dogs' => array(
					'one' => 'One dog',
					'two' => 'Two doggies',
					'other' => 'Some dogs'
				)
			),
			array(
				'cat' => 'An amount of cats',
				'mice/0' => 'Frankie',
				'mice/1' => 'Benjy',
				'dog or dogs' => '{{PLURAL|one=One dog|two=Two doggies|Some dogs}}'
			),
		);

		$cases[] = array(
			'/',
			array(
				'dog or dogs' => array(
					'zero' => 'No dogs',
					'one' => 'One dog',
					'two' => 'A couple doggies',
					'few' => 'A few dogs',
					'many' => '%1 dogs',
					'other' => 'Some dogs'
				)
			),
			array(
				'dog or dogs' => '{{PLURAL|zero=No dogs|one=One dog|two=A couple doggies|' .
					'few=A few dogs|many=%1 dogs|Some dogs}}'
			),
		);

		$cases[] = array(
			'/',
			array(
				'math is hard' => array(
					'one' => 'a=400',
					'other' => 'a=999'
				)
			),
			array( 'math is hard' => '{{PLURAL|one=a=400|a=999}}' ),
		);

		return $cases;
	}

	// Separate provider because the input throws an exception
	public static function provideTestMixedCLDRPlurals() {
		$cases = array();
		$cases[] = array(
			array(
				'dog or dogs' => array(
					'one' => 'One dog',
					'two' => 'Two doggies',
					'other' => 'Some dogs',
					'Pluto' => 'A specific dog'
				)
			)
		);

		$cases[] = array(
			array(
				'dog or dogs' => array(
					'Pluto' => 'A specific dog',
					'one' => 'One dog',
					'two' => 'Two doggies',
					'other' => 'Some dogs',
				)
			)
		);
		return $cases;
	}

}
