<?php
/**
 * @file
 * @license GPL-2.0-or-later
 */

declare( strict_types = 1 );

use MediaWiki\Extensions\Translate\MessageValidator\Validators\NewlineValidator;

/** @covers \MediaWiki\Extensions\Translate\MessageValidator\Validators\NewlineValidator */
class NewlineValidatorTest extends MediaWikiUnitTestCase {
	/** @dataProvider getNewlineValidatorProvider */
	public function testBraceBalanceValidator(
		string $definition, string $translation, int $expectedCount, string $msg
	) {
		$validator = new NewlineValidator();

		$message = new FatMessage( 'key', $definition );
		$message->setTranslation( $translation );

		$actual = $validator->getIssues( $message, 'en-gb' );
		$this->assertCount( $expectedCount, $actual, $msg );
	}

	public function getNewlineValidatorProvider() {
		yield [
			'Hello',
			'Hello World',
			0,
			'should not see a notice when newlines are not present.',
		];

		yield [
			"\nHello",
			"\nHello World",
			0,
			'should not see a notice when newlines are matching.',
		];

		yield [
			"\n\nHello",
			"\nHello World",
			1,
			'should see a notice due to missing starting newlines.',
		];

		yield [
			"Hello",
			"\nHello World",
			1,
			'should see a notice due to extra starting newlines.',
		];
	}
}
