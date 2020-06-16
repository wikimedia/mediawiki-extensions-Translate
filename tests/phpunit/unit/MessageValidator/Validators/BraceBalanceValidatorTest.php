<?php
/**
 * @file
 * @license GPL-2.0-or-later
 */

declare( strict_types = 1 );

use MediaWiki\Extensions\Translate\MessageValidator\Validators\BraceBalanceValidator;

/** @covers \MediaWiki\Extensions\Translate\MessageValidator\Validators\BraceBalanceValidator */
class BraceBalanceValidatorTest extends MediaWikiUnitTestCase {
	/** @dataProvider getBraceBalanceValidatorProvider */
	public function testBraceBalanceValidator(
		string $definition, string $translation, int $expectedCount, string $msg
	) {
		$validator = new BraceBalanceValidator();

		$message = new FatMessage( 'key', $definition );
		$message->setTranslation( $translation );

		$actual = $validator->getIssues( $message, 'en-gb' );
		$this->assertCount( $expectedCount, $actual, $msg );
	}

	public function getBraceBalanceValidatorProvider() {
		yield [
			'{{ Hello }}',
			'{{ Hello }}}',
			1,
			'should return an issue for a message containing non-matching braces.'
		];

		yield [
			'[[ Hello ]]',
			'[[ Hello ]]',
			0,
			'should not set any issue for a balanced translation.'
		];

		yield [
			'Hello :]',
			'Hello :]',
			0,
			'should not set any issue if definition is unbalanced.'
		];

		yield [
			'Hello :]',
			'Hello :)',
			1,
			'balancedness only applies to one brace type, for other types still raise an issue.'
		];
	}
}
