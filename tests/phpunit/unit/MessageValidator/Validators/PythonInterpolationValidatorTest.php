<?php
/**
 * @file
 * @license GPL-2.0-or-later
 */

use MediaWiki\Extensions\Translate\MessageValidator\Validators\PythonInterpolationValidator;

/**
 * @covers \MediaWiki\Extensions\Translate\MessageValidator\Validators\PythonInterpolationValidator
 */
class PythonInterpolationValidatorTest extends MediaWikiUnitTestCase {
	public static function provideValidate() {
		$key = 'key';
		$code = 'en';

		$message = new FatMessage( $key, 'My name is %s' );
		$message->setTranslation( 'This is invalid' );
		yield [
			$message,
			$code,
			[ 'variable', 'missing', $key, $code ]
		];

		$message = new FatMessage( $key, 'My name is %(name)s' );
		$message->setTranslation( 'This is invalid.' );
		yield [
			$message,
			$code,
			[ 'variable', 'missing', $key, $code ]
		];

		$message = new FatMessage( $key, 'My name is %(name)s' );
		$message->setTranslation( 'This is an invalid %(aaaa)d %(name)s variable.' );
		yield [
			$message,
			$code,
			[ 'variable', 'unknown', $key, $code ]
		];

		$message = new FatMessage( $key, 'My name is %s' );
		$message->setTranslation( 'This is a value: %s' );
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
		$validator = new PythonInterpolationValidator();

		$notices = [];
		$validator->validate( $message, $code, $notices );

		if ( $expected === null ) {
			$this->assertSame( [], $notices );
		} else {
			$this->assertSame( $expected, $notices[ $message->key() ][ 0 ][ 0 ] );
		}
	}
}
