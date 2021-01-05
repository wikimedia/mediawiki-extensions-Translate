<?php
declare( strict_types = 1 );

use MediaWiki\Extension\Translate\Validation\Validators\UnicodePluralValidator;

/**
 * @license GPL-2.0-or-later
 * @covers \MediaWiki\Extension\Translate\Validation\Validators\UnicodePluralValidator
 */
class UnicodePluralValidatorTest extends BaseValidatorTestCase {
	/** @dataProvider provideTestCases */
	public function test( ...$params ) {
		$this->runValidatorTests( new UnicodePluralValidator(), 'plural', ...$params );
	}

	public function provideTestCases() {
		yield [
			'{{PLURAL|one=a|b}}',
			'a',
			[ 'missing' ],
			'Missing plural is an issue'
		];

		yield [
			'a',
			'{{PLURAL|one=a|b}}',
			[ 'unsupported' ],
			'Plural in an unsupported message is an issue'
		];

		yield [
			'{{PLURAL|one=a|b}}',
			'{{PLURAL|one=aa|bb}}',
			[],
			'Correct plural syntax is not an issue'
		];

		yield [
			'{{PLURAL|one=a|b}}',
			'{{PLURAL|one=a|two=b|c}}',
			[ 'forms' ],
			'Extra plural forms are an issue'
		];
	}
}
