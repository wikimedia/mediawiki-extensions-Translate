<?php
declare( strict_types = 1 );

use MediaWiki\Extension\Translate\Validation\Validators\PythonInterpolationValidator;

/**
 * @license GPL-2.0-or-later
 * @covers \MediaWiki\Extension\Translate\Validation\Validators\PythonInterpolationValidator
 */
class PythonInterpolationValidatorTest extends BaseValidatorTestCase {
	/** @dataProvider provideTestCases */
	public function test( ...$params ) {
		$this->runValidatorTests( new PythonInterpolationValidator(), 'variable', ...$params );
	}

	public static function provideTestCases() {
		yield [
			'My name is %s',
			'This is invalid',
			[ 'missing' ],
			'missing unnamed variable is an issue'
		];

		yield [
			'My name is %(name)s',
			'This is invalid.',
			[ 'missing' ],
			'missing named variable is an issue'
		];

		yield [
			'My name is %(name)s',
			'This is an invalid %(aaaa)d %(name)s variable.',
			[ 'unknown' ],
			'unknown named variable is an issue'
		];

		yield [
			'My name is %s',
			'This is a value: %s',
			[],
			'all variables are used, not an issue'
		];
	}
}
