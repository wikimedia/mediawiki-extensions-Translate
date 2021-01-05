<?php
declare( strict_types = 1 );

use MediaWiki\Extension\Translate\Validation\Validators\MediaWikiTimeListValidator;

/**
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @covers \MediaWiki\Extension\Translate\Validation\Validators\MediaWikiTimeListValidator
 */
class MediaWikiTimeListValidatorTest extends BaseValidatorTestCase {
	/** @dataProvider provideTestCases */
	public function test( ...$params ) {
		$this->runValidatorTests( new MediaWikiTimeListValidator(), 'miscmw', ...$params );
	}

	public static function provideTestCases() {
		yield [
			'2 hours:2 hours,1 day:1 day',
			'2 tuntia:2 hours,1 päivä:1 day',
			[],
			'Good translation is not an issue'
		];

		yield [
			'2 hours:2 hours,1 day:1 day,1 week:1 week',
			'2 tuntia:2 hours,1 päivä:1 day',
			[ 'timelist-count' ],
			'Missing option is an issue'
		];

		yield [
			'2 hours:2 hours,1 day:1 day',
			'2 tuntia:2 hours,1 päivä:1 day,1 viikko: 1 week',
			[ 'timelist-count' ],
			'Extra option is an issue'
		];

		yield [
			'2 hours:2 hours,1 day:1 day',
			'2 tuntia:2 hours,2 days:2 päivää',
			[ 'timelist-format-value' ],
			'Changed option is an issue'
		];

		yield [
			'2 hours:2 hours,1 day:1 day',
			'2 tuntia:2 hours,1 day:1 päivä: 2 piävää:',
			[ 'timelist-format' ],
			'Wrong format is an issue'
		];

		yield [
			'2 hours:2 hours,1 day:1 day',
			'1 päivä:1 day,2 tuntia:2 hours',
			[ 'timelist-format-value', 'timelist-format-value' ],
			'Changed order is an issue'
		];
	}
}
