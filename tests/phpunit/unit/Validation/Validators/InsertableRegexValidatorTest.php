<?php
declare( strict_types = 1 );

use MediaWiki\Extension\Translate\Validation\Validators\InsertableRegexValidator;

/**
 * @license GPL-2.0-or-later
 * @covers \MediaWiki\Extension\Translate\Validation\Validators\InsertableRegexValidator
 */
class InsertableRegexValidatorTest extends BaseValidatorTestCase {
	/** @dataProvider provideTestCases */
	public function test( $constructorParams, ...$params ) {
		$validator = new InsertableRegexValidator( $constructorParams );
		$this->runValidatorTests( $validator, 'variable', ...$params );
	}

	public function provideTestCases() {
		yield [
			'/\$[a-z0-9]+/',
			'$contacts is $diff less than that.',
			'$contacts2 as $diff2 less.',
			[ 'missing', 'unknown' ],
			'should correctly identifiy the missing and unknown parameters.'
		];

		yield [
			[ 'regex' => '/\$[a-z0-9]+/' ],
			'$contacts is $diff less than that.',
			'$contacts less.',
			[ 'missing' ],
			'should correctly identifiy the missing parameters.'
		];

		yield [
			'/<[a-z]+>/',
			'<hello> <world>',
			'<hello> <world> <msg>',
			[ 'unknown' ],
			'should correctly identify the unknown parameters.'
		];
	}
}
