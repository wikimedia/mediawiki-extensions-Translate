<?php
/**
 * @file
 * @license GPL-2.0-or-later
 */

use MediaWiki\Extensions\Translate\MessageValidator\Validators\WikiParameterValidator;

/**
 * @covers \MediaWiki\Extensions\Translate\MessageValidator\Validators\WikiParameterValidator
 */
class WikiParameterValidatorTest extends MediaWikiUnitTestCase {
	public static function provideValidate() {
		$key = 'key';
		$code = 'en';

		$message = new FatMessage( $key, '$1' );
		$message->setTranslation( 'a' );
		yield [
			$message,
			$code,
			[ 'variable', 'missing', $key, $code ]
		];

		$message = new FatMessage( $key, '$1' );
		$message->setTranslation( '$2' );
		yield [
			$message,
			$code,
			[ 'variable', 'missing', $key, $code ]
		];

		$message = new FatMessage( $key, 'a' );
		$message->setTranslation( '$1' );
		yield [
			$message,
			$code,
			[ 'variable', 'unknown', $key, $code ]
		];

		$message = new FatMessage( $key, '$1' );
		$message->setTranslation( '$1' );
		yield [
			$message,
			$code,
			null
		];
	}

	/**
	 * @dataProvider provideValidate
	 */
	public function testValidate( TMessage $message, $code, $expected ) {
		$validator = new WikiParameterValidator();

		$notices = [];
		$validator->validate( $message, $code, $notices );

		if ( $expected === null ) {
			$this->assertSame( [], $notices );
		} else {
			$this->assertSame( $expected, $notices[ $message->key() ][ 0 ][ 0 ] );
		}
	}
}
