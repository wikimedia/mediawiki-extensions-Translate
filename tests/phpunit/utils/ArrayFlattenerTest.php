<?php
/**
 * @author Niklas Laxström
 * @file
 * @license GPL-2.0-or-later
 */

class ArrayFlattenerTest extends PHPUnit\Framework\TestCase {
	use PHPUnit4And6Compat;

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
	 * @dataProvider provideTestMixedCLDRPlurals
	 */
	public function testFlattenMixedCLDRPlurals( $input ) {
		$flattener = new ArrayFlattener( '.', true );
		$this->setExpectedException(
			MWException::class,
			'Reserved plural keywords mixed with other keys'
		);
		$flattener->flatten( $input );
	}

	public static function provideTestFlatten() {
		$cases = [];
		$cases[] = [
			'.',
			[ 'a' => 1 ],
			[ 'a' => 1 ],
		];

		$cases[] = [
			'.',
			[ 'a' => [ 'b' => [ 'c' => 1, 'd' => 2 ] ] ],
			[ 'a.b.c' => 1, 'a.b.d' => 2 ],
		];

		// By default, CLDR plural keywords should be treated like any other key
		$cases[] = [
			'/',
			[ 'number' => [ 'one' => '1', 'other' => '999' ] ],
			[ 'number/one' => '1', 'number/other' => '999' ]
		];

		return $cases;
	}

	public static function provideTestCLDRPlurals() {
		$cases = [];

		// We include some non-plural data to ensure it is processed correctly
		$cases[] = [
			'/',
			[
				'cat' => 'An amount of cats',
				'mice' => [
					'Frankie',
					'Benjy'
				],
				'dog or dogs' => [
					'one' => 'One dog',
					'two' => 'Two doggies',
					'other' => 'Some dogs'
				]
			],
			[
				'cat' => 'An amount of cats',
				'mice/0' => 'Frankie',
				'mice/1' => 'Benjy',
				'dog or dogs' => '{{PLURAL|one=One dog|two=Two doggies|Some dogs}}'
			],
		];

		$cases[] = [
			'/',
			[
				'dog or dogs' => [
					'zero' => 'No dogs',
					'one' => 'One dog',
					'two' => 'A couple doggies',
					'few' => 'A few dogs',
					'many' => '%1 dogs',
					'other' => 'Some dogs'
				]
			],
			[
				'dog or dogs' => '{{PLURAL|zero=No dogs|one=One dog|two=A couple doggies|' .
					'few=A few dogs|many=%1 dogs|Some dogs}}'
			],
		];

		$cases[] = [
			'/',
			[
				'math is hard' => [
					'one' => 'a=400',
					'other' => 'a=999'
				]
			],
			[ 'math is hard' => '{{PLURAL|one=a=400|a=999}}' ],
		];

		return $cases;
	}

	// Separate provider because the input throws an exception
	public static function provideTestMixedCLDRPlurals() {
		$cases = [];
		$cases[] = [
			[
				'dog or dogs' => [
					'one' => 'One dog',
					'two' => 'Two doggies',
					'other' => 'Some dogs',
					'Pluto' => 'A specific dog'
				]
			]
		];

		$cases[] = [
			[
				'dog or dogs' => [
					'Pluto' => 'A specific dog',
					'one' => 'One dog',
					'two' => 'Two doggies',
					'other' => 'Some dogs',
				]
			]
		];
		return $cases;
	}

	/**
	 * @dataProvider provideMatchingValues
	 */
	public function testCompareTrue( $input1, $input2 ) {
		$flattener = new ArrayFlattener( '.', true );

		$this->assertTrue(
			$flattener->compareContent( $input1, $input2, $flattener )
		);
	}

	/**
	 * @dataProvider provideNonMatchingValues
	 */
	public function testCompareFalse( $input1, $input2 ) {
		$flattener = new ArrayFlattener( '.', true );

		$this->assertfalse(
			$flattener->compareContent( $input1, $input2, $flattener )
		);
	}

	public static function provideMatchingValues() {
		$cases = [];

		// We include some non-plural data to ensure it is processed correctly
		$cases[] = [
			'a',
			'a'
		];

		$cases[] = [
			'{{PLURAL|one=cat|cats}}',
			'{{PLURAL|one=cat|cats}}',
		];

		$cases[] = [
			'Give me {{PLURAL|one=a cat|cats}}',
			'{{PLURAL|one=Give me a cat|Give me cats}}',
		];

		// Order should not matter
		$cases[] = [
			'{{PLURAL|one=Give me a cat|Give me cats}}',
			'Give me {{PLURAL|one=a cat|cats}}',
		];

		// Multiple inlines
		$cases[] = [
			'Test {{PLURAL|one=one|other}} and {{PLURAL|one=one|other}} and {{PLURAL|one=one|other}}!',
			'{{PLURAL|one=Test one and one and one|Test other and other and other}}!',
		];

		// Lots of keys
		$cases[] = [
			'Is {{PLURAL|zero=zero|one=one|two=two|few=few|many=many|other}}',
			'{{PLURAL|zero=Is zero|one=Is one|two=Is two|few=Is few|many=Is many|Is other}}',
		];

		return $cases;
	}

	public static function provideNonMatchingValues() {
		$cases = [];

		$cases[] = [
			'a',
			'b'
		];

		$cases[] = [
			'{{PLURAL|one=cat|cats}}',
			'{{PLURAL|one=dog|dogs}}',
		];

		// Different set of keys
		$cases[] = [
			'Is {{PLURAL|zero=zero|one=one|two=two|few=few|other}}',
			'{{PLURAL|zero=Is zero|two=Is two|few=Is few|many=Is many|Is other}}',
		];

		return $cases;
	}
}
