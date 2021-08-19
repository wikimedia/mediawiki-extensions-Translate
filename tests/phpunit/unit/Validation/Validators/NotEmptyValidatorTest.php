<?php
declare( strict_types = 1 );

use MediaWiki\Extension\Translate\Validation\Validators\NotEmptyValidator;

/**
 * @license GPL-2.0-or-later
 * @covers \MediaWiki\Extension\Translate\Validation\Validators\NotEmptyValidator
 */
class NotEmptyValidatorTest extends BaseValidatorTestCase {
	/** @dataProvider provideTestCases */
	public function test( ...$params ) {
		$this->runValidatorTests( new NotEmptyValidator(), 'empty', ...$params );
	}

	public function provideTestCases() {
		yield [
			'Hello',
			'',
			[ 'empty' ],
			'should see a notice when an empty translation is provided.',
		];

		yield [
			'Hello',
			" \n ",
			[ 'empty' ],
			'should see a notice when a translation with newlines is provided.',
		];

		yield [
			'Hello',
			'Hello World',
			[],
			'should not see a notice when translation is not an empty string.',
		];

		yield [
			'Hello',
			null,
			[],
			'should not see a notice when translation is null.'
		];
	}
}
