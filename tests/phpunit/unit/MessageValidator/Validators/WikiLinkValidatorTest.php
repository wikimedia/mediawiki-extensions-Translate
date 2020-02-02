<?php
/**
 * @file
 * @license GPL-2.0-or-later
 */

use MediaWiki\Extensions\Translate\MessageValidator\Validators\WikiLinkValidator;

/**
 * @covers \MediaWiki\Extensions\Translate\MessageValidator\Validators\WikiLinkValidator
 */
class WikiLinkValidatorTest extends MediaWikiUnitTestCase {
	public static function provideValidate() {
		$key = 'test';
		$code = 'en-gb';

		yield [
			'[[$3|Hello]] [[#Hello|Hello World]]',
			'Hello world',
			$key,
			$code,
			[ 'links', 'missing', $key, $code ],
			[ 'PARAMS', [ '[[$3|Hello]]', '[[#Hello|Hello World]]' ] ],
			[ 'COUNT', 2 ]
		];

		yield [
			'[[$3|Hello]] [[#Hello|Hello World]]',
			'[[$3|Hola]] [[#Hello|Hey]]',
			$key,
			$code,
			null
		];

		yield [
			'[[$3|Hello]] [[#Hello|Hello World]]',
			'[[$3|Food]] [[#Hey|Hey]]',
			$key,
			$code,
			[ 'links', 'extra', $key, $code ],
			[ 'PARAMS', [ '[[#Hey|Hey]]' ] ],
			[ 'COUNT', 1 ]
		];

		yield [
			'[[$3|Hello]] [[#Hello|Hello World]]',
			'[[$3|Food]] [[#Hello|Hey]]',
			$key,
			$code,
			null
		];
	}

	/**
	 * @dataProvider provideValidate
	 */
	public function testValidate(
		$messageText, $translation, $key, $code, $expected, $params = null, $count = null
	) {
		$message = new FatMessage( $key, $messageText );
		$message->setTranslation( $translation );

		$validator = new WikiLinkValidator();

		$notice = [];
		$validator->validate( $message, $code, $notice );

		if ( $expected === null ) {
			$this->assertEmpty( $notice );
		} else {
			$this->assertSame( $expected, $notice[ $message->key() ][ 0 ][ 0 ] );
			$this->assertSame( $params, $notice[ $message->key() ][ 0 ][ 2 ] );
			$this->assertSame( $count, $notice[ $message->key() ][ 0 ][ 3 ] );
		}
	}
}
