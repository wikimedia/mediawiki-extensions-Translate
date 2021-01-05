<?php
declare( strict_types = 1 );

use MediaWiki\Extension\Translate\Validation\Validators\PrintfValidator;

/**
 * @license GPL-2.0-or-later
 * @covers \MediaWiki\Extension\Translate\Validation\Validators\PrintfValidator
 */
class PrintfValidatorTest extends BaseValidatorTestCase {
	/** @dataProvider provideTestCases */
	public function test( ...$params ) {
		$this->runValidatorTests( new PrintfValidator(), 'variable', ...$params );
	}

	public static function provideTestCases() {
		$key = 'key';
		$code = 'en';

		yield [
			'%2$f',
			'a',
			[ 'missing' ],
			'missing positional variable is an issue'
		];

		yield [
			'%2$f',
			'%3$d',
			[ 'missing', 'unknown' ],
			'typoed variable is two issues'
		];

		yield [
			'abc',
			'%4$d',
			[ 'unknown' ],
			'unknown positional variable is an issue'
		];

		yield [
			'%2$f',
			'%2$f',
			[],
			'all variables are used, no issues'
		];

		yield [
			'%2$.2f',
			'%2$f',
			[ 'missing', 'unknown' ],
			'changing precision is not supported'
		];

		yield [
			'%.2f',
			'%2$f',
			[ 'missing', 'unknown' ],
			'changing precision and position is not supported'
		];
	}
}
