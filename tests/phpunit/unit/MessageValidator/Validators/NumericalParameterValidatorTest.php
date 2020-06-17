<?php
/**
 * @file
 * @license GPL-2.0-or-later
 */

declare( strict_types = 1 );

use MediaWiki\Extensions\Translate\MessageValidator\Validators\NumericalParameterValidator;

/** @covers \MediaWiki\Extensions\Translate\MessageValidator\Validators\NumericalParameterValidator */
class NumericalParameterValidatorTest extends BaseValidatorTestCase {
	/** @dataProvider provideTestCases */
	public function test( ...$params ) {
		$this->runValidatorTests( new NumericalParameterValidator(), 'variable', ...$params );
	}

	public static function provideTestCases() {
		yield [
			'$12',
			'a',
			[ 'missing' ],
			'missing variable is an issue'
		];

		yield [
			'$1',
			'$2',
			[ 'missing', 'unknown' ],
			'typoed variable is two issues'
		];

		yield [
			'a',
			'$11',
			[ 'unknown' ],
			'unknown variable is an issue'
		];

		yield [
			'$32',
			'$32',
			[],
			'all variables used, no issues',
		];
	}
}
