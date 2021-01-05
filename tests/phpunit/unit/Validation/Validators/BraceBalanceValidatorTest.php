<?php
declare( strict_types = 1 );

use MediaWiki\Extension\Translate\Validation\Validators\BraceBalanceValidator;

/**
 * @license GPL-2.0-or-later
 * @covers \MediaWiki\Extension\Translate\Validation\Validators\BraceBalanceValidator
 */
class BraceBalanceValidatorTest extends BaseValidatorTestCase {
	/** @dataProvider provideTestCases */
	public function test( ...$params ) {
		$this->runValidatorTests( new BraceBalanceValidator(), 'balance', ...$params );
	}

	public function provideTestCases() {
		yield [
			'{{ Hello }}',
			'{{ Hello }}}',
			[ 'brace' ],
			'should return an issue for a message containing non-matching braces.'
		];

		yield [
			'[[ Hello ]]',
			'[[ Hello ]]',
			[],
			'should not set any issue for a balanced translation.'
		];

		yield [
			'Hello :]',
			'Hello :]',
			[],
			'should not set any issue if definition is unbalanced.'
		];

		yield [
			'Hello :]',
			'Hello :)',
			[ 'brace' ],
			'balancedness only applies to one brace type, for other types still raise an issue.'
		];
	}
}
