<?php
/**
 * @file
 * @license GPL-2.0-or-later
 */

declare( strict_types = 1 );

use MediaWiki\Extensions\Translate\MessageValidator\Validators\NewlineValidator;

/** @covers \MediaWiki\Extensions\Translate\MessageValidator\Validators\NewlineValidator */
class NewlineValidatorTest extends BaseValidatorTestCase {

	/** @dataProvider provideTestCases */
	public function test( ...$params ) {
		$this->runValidatorTests( new NewlineValidator(), 'newline', ...$params );
	}

	public function provideTestCases() {
		yield [
			'Hello',
			'Hello World',
			[],
			'should not see a notice when newlines are not present.',
		];

		yield [
			"\nHello",
			"\nHello World",
			[],
			'should not see a notice when newlines are matching.',
		];

		yield [
			"\n\nHello",
			"\nHello World",
			[ 'missing-start' ],
			'should see a notice due to missing starting newlines.',
		];

		yield [
			"Hello",
			"\nHello World",
			[ 'extra-start' ],
			'should see a notice due to extra starting newlines.',
		];
	}
}
