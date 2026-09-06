<?php
declare( strict_types = 1 );

use MediaWiki\Extension\Translate\Validation\Validators\PrintfIndexedParameterValidator;

/**
 * @license GPL-2.0-or-later
 * @covers \MediaWiki\Extension\Translate\Validation\Validators\PrintfIndexedParameterValidator
 */
class PrintfIndexedParameterValidatorTest extends BaseValidatorTestCase {
	/** @dataProvider provideTestCases */
	public function test( ...$params ) {
		$this->runValidatorTests( new PrintfIndexedParameterValidator(), 'percent', ...$params );
	}

	public static function provideTestCases() {
		yield [
			'foo',
			'%1$s',
			[],
			'indexed parameter is valid',
		];

		yield [
			'foo',
			'%%',
			[],
			'%% escape sequence is valid',
		];

		yield [
			'foo',
			'%s',
			[ 'not-indexed' ],
			'non-indexed specifier is an issue',
		];

		yield [
			'foo',
			'%(name)s',
			[ 'not-indexed' ],
			'named parameter is an issue',
		];

		yield [
			'foo',
			'50%',
			[ 'not-indexed' ],
			'bare % at end of string is an issue',
		];

		yield [
			'foo',
			'no percent here',
			[],
			'string without % has no issues',
		];

		yield [
			'foo',
			'%1$s costs 50%',
			[ 'not-indexed' ],
			'valid indexed parameter mixed with bare % is still an issue',
		];

		yield [
			'foo',
			'%%%',
			[ 'not-indexed' ],
			'odd number of % signs leaves a bare % after stripping %%',
		];

		yield [
			'foo',
			'%0$s',
			[ 'not-indexed' ],
			'zero-indexed parameter is an issue',
		];

		yield [
			'foo',
			'%10s',
			[ 'not-indexed' ],
			'field width is not an argument index',
		];

		yield [
			'foo',
			'%2d',
			[ 'not-indexed' ],
			'integer field width is not an argument index',
		];

		yield [
			'foo',
			'%2$s',
			[],
			'explicit second argument is valid',
		];

		yield [
			'foo',
			'%2$10s',
			[],
			'explicit argument index followed by field width is valid',
		];

		yield [
			'foo',
			'%12$s',
			[],
			'multi-digit argument index is valid',
		];

		yield [
			'foo',
			'%1$s %10s',
			[ 'not-indexed' ],
			'indexed parameter does not hide an unindexed parameter with width',
		];
	}
}
