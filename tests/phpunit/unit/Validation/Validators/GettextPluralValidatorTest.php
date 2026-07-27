<?php
declare( strict_types = 1 );

use MediaWiki\Extension\Translate\Validation\Validators\GettextPluralValidator;

/**
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @covers \MediaWiki\Extension\Translate\Validation\Validators\GettextPluralValidator
 */
class GettextPluralValidatorTest extends BaseValidatorTestCase {
	private const TEST_RULE_FILE = __DIR__ . '/data/plural-gettext.txt';

	/** @dataProvider provideTestCases */
	public function test( ...$params ) {
		$this->runValidatorTests( new GettextPluralValidator(), 'plural', ...$params );
	}

	public function testMalformedPluralRuleResultsInNoIssues() {
		$this->runValidatorTests(
			new GettextPluralValidator( self::TEST_RULE_FILE ),
			'plural',
			'{{PLURAL:GETTEXT|meter|meters}}',
			'metres',
			[],
			'Malformed plural rule results in no issues',
			'x-test-malformed'
		);
	}

	public static function provideTestCases() {
		// Note that BaseValidatorTestCase uses 'fr' as target language
		yield [
			'{{PLURAL:GETTEXT|meter|meters}}',
			'{{PLURAL:GETTEXT|metre|metres}}',
			[],
			'Correct number of plural forms is not an issue'
		];

		yield [
			'{{PLURAL:GETTEXT|meter|meters}}',
			'{{PLURAL:GETTEXT|metre|metres|so many metres}}',
			[ 'forms' ],
			'Extra plural form is an issue'
		];

		yield [
			'{{PLURAL:GETTEXT|meter|meters}}',
			'{{PLURAL:GETTEXT|metres}}',
			[ 'forms' ],
			'Missing plural form is an issue'
		];

		yield [
			'{{PLURAL:GETTEXT|meter|meters}}',
			'metres',
			[ 'missing' ],
			'Missing plural is an issue'
		];

		yield [
			'meters',
			'{{PLURAL:GETTEXT|meter|meters}}',
			[ 'unsupported' ],
			'Plural in translation when lacking in the source an issue'
		];

		yield [
			'{{PLURAL:GETTEXT|meter|meters}}',
			'metres',
			[],
			'Unknown language results in no issues',
			'x-test-unknown'
		];
	}
}
