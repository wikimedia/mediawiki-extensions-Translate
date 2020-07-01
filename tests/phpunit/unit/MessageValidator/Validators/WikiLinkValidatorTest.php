<?php
declare( strict_types = 1 );

use MediaWiki\Extensions\Translate\MessageValidator\Validators\WikiLinkValidator;

/**
 * @license GPL-2.0-or-later
 * @covers \MediaWiki\Extensions\Translate\MessageValidator\Validators\WikiLinkValidator
 */
class WikiLinkValidatorTest extends BaseValidatorTestCase {
	/** @dataProvider provideTestCases */
	public function test( ...$params ) {
		$this->runValidatorTests( new WikiLinkValidator(), 'links', ...$params );
	}

	public function provideTestCases() {
		yield [
			'[[$3|Hello]] [[#Hello|Hello World]]',
			'Hello world',
			[ 'missing' ],
			'Two links missing is an issue'

		];

		yield [
			'[[$3|Hello]] [[#Hello|Hello World]]',
			'[[$3|Hola]] [[#Hello|Hey]]',
			[],
			'All links present, no issue'

		];

		yield [
			'[[$3|Hello]] [[#Hello|Hello World]]',
			'[[$3|Food]] [[#Hey|Hey]]',
			[ 'missing', 'extra' ],
			'One link changed is two issues'
		];
	}
}
