<?php
/**
 * @file
 * @license GPL-2.0-or-later
 */

declare( strict_types = 1 );

use MediaWiki\Extensions\Translate\MessageValidator\Validators\MediaWikiParameterValidator;

/** @covers \MediaWiki\Extensions\Translate\MessageValidator\Validators\MediaWikiParameterValidator */
class MediaWikiParameterValidatorTest extends BaseValidatorTestCase {
	/** @dataProvider provideTestCases */
	public function test( ...$params ) {
		$this->runValidatorTests( new MediaWikiParameterValidator(), 'variable', ...$params );
	}

	public static function provideTestCases() {
		yield [
			'$1',
			'a',
			[ 'missing' ],
			'missing variable is an issue'
		];

		yield [
			'$1',
			'$2',
			[ 'missing', 'unknown' ],
			'typoed variable is two issues'
		];

		yield [
			'a',
			'$1',
			[ 'unknown' ],
			'unknown variable is an issue'
		];

		yield [
			'$1$2 $3',
			'$3$2 $1',
			[],
			'all variables used is not an issue'
		];

		// This fails, deprecate this class in favor of NumericalParameterValidator?
		/*yield [
			'$13',
			'$12',
			[ 'missing' ],
			'missing large variable is an issue'
		];*/
	}
}
