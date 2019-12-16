<?php
/**
 * @file
 * @license GPL-2.0-or-later
 */

use MediaWiki\Extensions\Translate\MessageValidator\Validators\PrintfValidator;

/**
 * @covers \MediaWiki\Extensions\Translate\MessageValidator\Validators\PrintfValidator
 */
class PrintfValidatorTest extends MediaWikiUnitTestCase {
	public static function provideValidate() {
		$key = 'key';
		$code = 'en';

		$message = new FatMessage( $key, '%2$f' );
		$message->setTranslation( 'a' );
		yield [
			$message,
			$code,
			[ 'variable', 'missing', $key, $code ]
		];

		$message = new FatMessage( $key, '%2$f' );
		$message->setTranslation( '%3$d' );
		yield [
			$message,
			$code,
			[ 'variable', 'missing', $key, $code ]
		];

		$message = new FatMessage( $key, 'abc' );
		$message->setTranslation( '%4$d' );
		yield [
			$message,
			$code,
			[ 'variable', 'unknown', $key, $code ]
		];

		$message = new FatMessage( $key, '%2$f' );
		$message->setTranslation( '%2$f' );
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
		$validator = new PrintfValidator();

		$notices = [];
		$validator->validate( $message, $code, $notices );

		if ( $expected === null ) {
			$this->assertSame( [], $notices );
		} else {
			$this->assertSame( $expected, $notices[ $message->key() ][ 0 ][ 0 ] );
		}
	}
}
