<?php
declare( strict_types = 1 );

use MediaWiki\Extension\Translate\Validation\Validators\PercentEscapeValidator;

/**
 * @license GPL-2.0-or-later
 * @covers \MediaWiki\Extension\Translate\Validation\Validators\PercentEscapeValidator
 */
class PercentEscapeValidatorTest extends BaseValidatorTestCase {
	/** @dataProvider provideTestCases */
	public function test( ...$params ) {
		$this->runValidatorTests( new PercentEscapeValidator(), 'percent', ...$params );
	}

	public static function provideTestCases() {
		yield [
			'foo',
			'50%',
			[ 'invalid' ],
			'bare % at end of string is an issue',
		];

		yield [
			'foo',
			"50%\ndone",
			[ 'invalid' ],
			'% followed by newline is an issue',
		];

		yield [
			'foo',
			'50% done',
			[ 'invalid' ],
			'% followed by space is an issue (space flag like % d is a known undetected limitation)',
		];

		yield [
			'foo',
			'50%ä',
			[ 'invalid' ],
			'% followed by non-ASCII character is an issue',
		];

		yield [
			'foo',
			'50%%',
			[],
			'%% escape sequence is valid',
		];

		yield [
			'foo',
			'%(name)s',
			[],
			'named parameter is valid',
		];

		yield [
			'foo',
			'%s',
			[],
			'printf specifier is valid',
		];

		yield [
			'foo',
			'%1$s',
			[],
			'indexed parameter is valid',
		];

		yield [
			'foo',
			'%0$s',
			[],
			'% followed by zero digit is valid (printable ASCII); stricter validators reject it',
		];

		yield [
			'foo',
			'no percent here',
			[],
			'string without % has no issues',
		];

		yield [
			'foo',
			'%%%',
			[ 'invalid' ],
			'odd number of % signs leaves a bare % after stripping %%',
		];
	}
}
