<?php
declare( strict_types = 1 );

use MediaWiki\Extension\Translate\Validation\Validators\MediaWikiPageNameValidator;

/**
 * @license GPL-2.0-or-later
 * @covers \MediaWiki\Extension\Translate\Validation\Validators\MediaWikiPageNameValidator
 */
class MediaWikiPageNameValidatorTest extends BaseValidatorTestCase {
	/** @dataProvider provideTestCases */
	public function test( ...$params ) {
		$this->runValidatorTests( new MediaWikiPageNameValidator(), 'pagename', ...$params );
	}

	public function provideTestCases() {
		yield [
			'{{ns:project}}:hello',
			'{{ns:hello}}:hello',
			[ 'namespace' ],
			'Changed namespace is an issue'
		];

		yield [
			'help:me',
			'help:me',
			[],
			'Unchanged namespace is not an issue'
		];

		yield [
			'{{ns:project}}:hello',
			'{{ns:project}}:Hey!',
			[],
			'Unchanged namespace is not an issue'
		];
	}
}
