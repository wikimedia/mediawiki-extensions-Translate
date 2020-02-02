<?php
/**
 * @file
 * @license GPL-2.0-or-later
 */

use MediaWiki\Extensions\Translate\MessageValidator\Validators\MediaWikiPageNameValidator;

/**
 * @covers \MediaWiki\Extensions\Translate\MessageValidator\Validators\MediaWikiPageNameValidator
 */
class MediaWikiPageNameValidatorTest extends MediaWikiUnitTestCase {
	public static function provideValidate() {
		$key = 'test';
		$code = 'en-gb';

		yield [
			'{{ns:project}}:hello',
			'{{ns:hello}}:hello',
			$key,
			$code,
			[ 'pagename', 'namespace', $key, $code ]
		];

		yield [
			'help:me',
			'help:me',
			$key,
			$code,
			null
		];

		yield [
			'{{ns:project}}:hello',
			'{{ns:project}}:Hey!',
			$key,
			$code,
			null
		];
	}

	/**
	 * @dataProvider provideValidate
	 */
	public function testValidate( $messageText, $translation, $key, $code, $expected ) {
		$message = new FatMessage( $key, $messageText );
		$message->setTranslation( $translation );

		$validator = new MediaWikiPageNameValidator();

		$notice = [];
		$validator->validate( $message, $code, $notice );

		if ( $expected === null ) {
			$this->assertEmpty( $notice );
		} else {
			$this->assertSame( $expected, $notice[ $message->key() ][ 0 ][ 0 ] );
		}
	}
}
