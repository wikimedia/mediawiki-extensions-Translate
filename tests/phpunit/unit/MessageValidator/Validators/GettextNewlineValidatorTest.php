<?php
/**
 * @file
 * @license GPL-2.0-or-later
 */

declare( strict_types = 1 );

use MediaWiki\Extensions\Translate\MessageValidator\Validators\GettextNewlineValidator;

/** @covers \MediaWiki\Extensions\Translate\MessageValidator\Validators\GettextNewlineValidator */
class GettextNewlineValidatorTest extends BaseValidatorTestCase {
	/** @dataProvider provideTestCases */
	public function test( ...$params ) {
		$this->runValidatorTests( new GettextNewlineValidator(), 'newline', ...$params );
	}

	public function provideTestCases() {
		yield [
			"\n\nHello\n\n\\",
			"\nHello World\n\n\n\\",
			[ 'missing-start', 'extra-end' ],
			'should see a notice due to missing / extra newlines.',
		];

		yield [
			"\nHello\n\\",
			"\nHello World\n\\",
			[],
			'should not see a notice when newlines are matching.',
		];

		yield [
			"\n\nHello",
			"\nHello World",
			[ 'missing-start' ],
			'should see a notice due to missing / extra newlines.',
		];

		yield [
			"Hello",
			"Hello World\n\\",
			[ 'extra-end' ],
			'should see a notice due to missing / extra newlines.',
		];
	}
}
