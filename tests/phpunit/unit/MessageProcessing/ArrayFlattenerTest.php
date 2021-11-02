<?php

namespace MediaWiki\Extension\Translate\MessageProcessing;

use MediaWikiUnitTestCase;
use MWException;

/** @coversDefaultClass \MediaWiki\Extension\Translate\MessageProcessing\ArrayFlattener */
class ArrayFlattenerTest extends MediaWikiUnitTestCase {
	/**
	 * @dataProvider provideTestFlatten
	 * @covers ::flatten
	 */
	public function testFlatten( $sep, $input, $expected ) {
		$flattener = new ArrayFlattener( $sep );
		$output = $flattener->flatten( $input );
		$this->assertEquals( $expected, $output );
	}

	/**
	 * @dataProvider provideTestFlatten
	 * @covers ::unflatten
	 */
	public function testUnflatten( $sep, $expected, $input ) {
		$flattener = new ArrayFlattener( $sep );
		$output = $flattener->unflatten( $input );
		$this->assertEquals( $expected, $output );
	}

	/**
	 * @dataProvider provideTestCLDRPlurals
	 * @covers ::flattenCLDRPlurals
	 */
	public function testFlattenCLDRPlurals( $sep, $input, $expected ) {
		$flattener = new ArrayFlattener( $sep, true );
		$output = $flattener->flatten( $input );
		$this->assertEquals( $expected, $output );
	}

	/**
	 * @dataProvider provideTestCLDRPlurals
	 * @dataProvider provideUnflattenCLDRPlurals
	 * @covers ::unflattenCLDRPlurals
	 */
	public function testUnflattenCLDRPlurals( $sep, $expected, $input ) {
		$flattener = new ArrayFlattener( $sep, true );
		$output = $flattener->unflatten( $input );
		$this->assertEquals( $expected, $output );
	}

	/**
	 * @dataProvider provideTestMixedCLDRPlurals
	 * @covers ::flattenCLDRPlurals
	 */
	public function testFlattenMixedCLDRPlurals( $input ) {
		$flattener = new ArrayFlattener( '.', true );
		$this->expectException( MWException::class );
		$flattener->flatten( $input );
	}

	public static function provideTestFlatten() {
		yield [
			'.',
			[ 'a' => 1 ],
			[ 'a' => 1 ],
		];

		yield [
			'.',
			[ 'a' => [ 'b' => [ 'c' => 1, 'd' => 2 ] ] ],
			[ 'a.b.c' => 1, 'a.b.d' => 2 ],
		];

		// By default, CLDR plural keywords should be treated like any other key
		yield [
			'/',
			[ 'number' => [ 'one' => '1', 'other' => '999' ] ],
			[ 'number/one' => '1', 'number/other' => '999' ]
		];
	}

	public static function provideTestCLDRPlurals() {
		// We include some non-plural data to ensure it is processed correctly
		yield [
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
				],
				'hello' => [
					'world' => 'hey',
					'other' => 'hello world'
				]
			],
			[
				'cat' => 'An amount of cats',
				'mice/0' => 'Frankie',
				'mice/1' => 'Benjy',
				'dog or dogs' => '{{PLURAL|one=One dog|two=Two doggies|Some dogs}}',
				'hello/world' => 'hey',
				'hello/other' => 'hello world'
			],
		];

		yield [
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

		yield [
			'/',
			[
				'math is hard' => [
					'one' => 'a=400',
					'other' => 'a=999'
				]
			],
			[ 'math is hard' => '{{PLURAL|one=a=400|a=999}}' ],
		];
	}

	/**
	 * Separate input due to bug Phab:T233402.
	 * TODO: Remove and add to provideTestCLDRPlurals itself once the
	 * above bug is fixed.
	 */
	public static function provideUnflattenCLDRPlurals() {
		yield [
			'/',
			[
				'collect' => [
					'one' => '%{count} collection',
					'other' => '%{count} collection'
				]
			],
			[ 'collect' => '%{count} collection{{PLURAL|one=|}}' ]
		];

		yield [
			'/',
			[
				'collect many' => [
					'one' => '%{count} collection',
					'other' => '%{count} collections'
				]
			],
			[ 'collect many' => '%{count} collection{{PLURAL|one=|s}}' ]
		];
	}

	// Separate provider because the input throws an exception
	public static function provideTestMixedCLDRPlurals() {
		yield [
			[
				'dog or dogs' => [
					'one' => 'One dog',
					'two' => 'Two doggies',
					'other' => 'Some dogs',
					'Pluto' => 'A specific dog'
				]
			]
		];

		yield [
			[
				'dog or dogs' => [
					'Pluto' => 'A specific dog',
					'one' => 'One dog',
					'two' => 'Two doggies',
					'other' => 'Some dogs',
				]
			]
		];
	}

	/**
	 * @dataProvider provideMatchingValues
	 * @covers ::compareContent
	 */
	public function testCompareTrue( $input1, $input2 ) {
		$flattener = new ArrayFlattener( '.', true );

		$this->assertTrue(
			$flattener->compareContent( $input1, $input2 )
		);
	}

	/**
	 * @dataProvider provideNonMatchingValues
	 * @covers ::compareContent
	 */
	public function testCompareFalse( $input1, $input2 ) {
		$flattener = new ArrayFlattener( '.', true );

		$this->assertfalse(
			$flattener->compareContent( $input1, $input2 )
		);
	}

	public static function provideMatchingValues() {
		// We include some non-plural data to ensure it is processed correctly
		yield [
			'a',
			'a'
		];

		yield [
			'{{PLURAL|one=cat|cats}}',
			'{{PLURAL|one=cat|cats}}',
		];

		yield [
			'Give me {{PLURAL|one=a cat|cats}}',
			'{{PLURAL|one=Give me a cat|Give me cats}}',
		];

		// Order should not matter
		yield [
			'{{PLURAL|one=Give me a cat|Give me cats}}',
			'Give me {{PLURAL|one=a cat|cats}}',
		];

		// Multiple inlines
		yield [
			'Test {{PLURAL|one=one|other}} and {{PLURAL|one=one|other}} and {{PLURAL|one=one|other}}!',
			'{{PLURAL|one=Test one and one and one|Test other and other and other}}!',
		];

		// Lots of keys
		yield [
			'Is {{PLURAL|zero=zero|one=one|two=two|few=few|many=many|other}}',
			'{{PLURAL|zero=Is zero|one=Is one|two=Is two|few=Is few|many=Is many|Is other}}',
		];
	}

	public static function provideNonMatchingValues() {
		yield [
			'a',
			'b'
		];

		yield [
			'{{PLURAL|one=cat|cats}}',
			'{{PLURAL|one=dog|dogs}}',
		];

		// Different set of keys
		yield [
			'Is {{PLURAL|zero=zero|one=one|two=two|few=few|other}}',
			'{{PLURAL|zero=Is zero|two=Is two|few=Is few|many=Is many|Is other}}',
		];
	}
}
