<?php
declare( strict_types = 1 );

use MediaWiki\Extension\Translate\Validation\Validators\MatchSetValidator;

/**
 * @license GPL-2.0-or-later
 * @covers \MediaWiki\Extension\Translate\Validation\Validators\MatchSetValidator
 */
class MatchSetValidatorTest extends BaseValidatorTestCase {
	/** @dataProvider provideTestCases */
	public function test( $constructorParams, ...$params ) {
		$validator = new MatchSetValidator( $constructorParams );
		$this->runValidatorTests( $validator, 'value-not-present', ...$params );
	}

	public function provideTestCases() {
		yield [
			[ 'values' => [ 'rtl', 'Ltr' ] ],
			'rtl',
			'ltr',
			[ 'invalid' ],
			'Wrong case (case-sensitive) is an issue'
		];

		yield [
			[ 'values' => [ 'rtl', 'Ltr' ], 'caseSensitive' => false ],
			'rtl',
			'ltr',
			[],
			'Matching value (case-insensitive) is not an issue'
		];

		yield [
			[ 'values' => [ 'rtl', 'etc' ] ],
			'rtl',
			'ltr',
			[ 'invalid' ],
			'Wrong value (case-sensitive) is an issue'
		];
	}

	public function testEmptyValues() {
		$this->expectException( InvalidArgumentException::class );
		new MatchSetValidator( [
			'values' => [],
		] );
	}
}
