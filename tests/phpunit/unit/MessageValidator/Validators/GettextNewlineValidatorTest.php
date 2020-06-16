<?php
/**
 * @file
 * @license GPL-2.0-or-later
 */

declare( strict_types = 1 );

use MediaWiki\Extensions\Translate\MessageValidator\Validators\GettextNewlineValidator;
use MediaWiki\Extensions\Translate\Validation\ValidationIssue;
use MediaWiki\Extensions\Translate\Validation\ValidationIssues;

/** @covers \MediaWiki\Extensions\Translate\MessageValidator\Validators\GettextNewlineValidator */
class GettextNewlineValidatorTest extends MediaWikiUnitTestCase {
	/** @dataProvider getGettextNewlineValidatorProvider */
	public function testBraceBalanceValidator(
		string $definition, string $translation, array $subTypes, string $msg
	) {
		$validator = new GettextNewlineValidator();

		$message = new FatMessage( 'key', $definition );
		$message->setTranslation( $translation );

		$actual = $validator->getIssues( $message, 'en-gb' );
		$this->assertSameSize( $subTypes, $actual, $msg );
		$this->assertArrayEquals( $subTypes, $this->getSubTypes( $actual ) );
	}

	/** @return string[] */
	private function getSubTypes( ValidationIssues $issues ): array {
		return array_map( function ( ValidationIssue $x ) {
			return $x->subType();
		}, iterator_to_array( $issues ) );
	}

	public function getGettextNewlineValidatorProvider() {
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
