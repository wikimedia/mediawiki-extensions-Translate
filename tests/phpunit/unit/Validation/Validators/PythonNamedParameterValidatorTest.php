<?php
declare( strict_types = 1 );

use MediaWiki\Extension\Translate\Validation\Validators\PythonNamedParameterValidator;

/**
 * @license GPL-2.0-or-later
 * @covers \MediaWiki\Extension\Translate\Validation\Validators\PythonNamedParameterValidator
 */
class PythonNamedParameterValidatorTest extends BaseValidatorTestCase {
	/** @dataProvider provideTestCases */
	public function test( ...$params ) {
		$this->runValidatorTests( new PythonNamedParameterValidator(), 'percent', ...$params );
	}

	public static function provideTestCases() {
		yield [
			'foo',
			'%(name)s',
			[],
			'named parameter is valid',
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
			[ 'not-named' ],
			'unnamed printf specifier is an issue',
		];

		yield [
			'foo',
			'50%',
			[ 'not-named' ],
			'bare % at end of string is an issue',
		];

		yield [
			'foo',
			'%1$s',
			[ 'not-named' ],
			'indexed parameter is an issue',
		];

		yield [
			'foo',
			'no percent here',
			[],
			'string without % has no issues',
		];

		yield [
			'foo',
			'%(name)s costs 50%',
			[ 'not-named' ],
			'valid named parameter mixed with bare % is still an issue',
		];

		yield [
			'foo',
			'%%%',
			[ 'not-named' ],
			'odd number of % signs leaves a bare % after stripping %%',
		];
	}
}
