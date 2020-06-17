<?php
/**
 * @file
 * @license GPL-2.0-or-later
 */

declare( strict_types = 1 );

use MediaWiki\Extensions\Translate\MessageValidator\Validators\IosVariableValidator;

/** @covers \MediaWiki\Extensions\Translate\MessageValidator\Validators\IosVariableValidator */
class IosVariableValidatorTest extends BaseValidatorTestCase {

	/** @dataProvider provideTestCases */
	public function test( ...$params ) {
		$this->runValidatorTests( new IosVariableValidator(), 'variable', ...$params );
	}

	public static function provideTestCases() {
		yield [
			'My name is %@',
			'This is invalid',
			[ 'missing' ],
			'missing %@ is an issue'
		];

		yield [
			'My name is %5d',
			'This is invalid',
			[ 'missing' ],
			'missing %5d is an issue'
		];

		yield [
			'My name is %ld.',
			'This is invalid: %ld %d.',
			[ 'unknown' ],
			'unknown %d is an issue'
		];
	}
}
