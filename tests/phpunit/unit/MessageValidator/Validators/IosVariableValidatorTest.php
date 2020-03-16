<?php
/**
 * @file
 * @license GPL-2.0-or-later
 */

use MediaWiki\Extensions\Translate\MessageValidator\Validators\IosVariableValidator;

/**
 * @covers \MediaWiki\Extensions\Translate\MessageValidator\Validators\IosVariableValidator
 */
class IosVariableValidatorTest extends MediaWikiUnitTestCase {
	public static function provideValidate() {
		$key = 'key';
		$code = 'en';

		$message = new FatMessage( $key, 'My name is %@' );
		$message->setTranslation( 'This is invalid' );
		yield [
			$message,
			$code,
			[ 'variable', 'missing', $key, $code ]
		];

		$message = new FatMessage( $key, 'My name is %5d' );
		$message->setTranslation( 'This is invalid.' );
		yield [
			$message,
			$code,
			[ 'variable', 'missing', $key, $code ]
		];

		$message = new FatMessage( $key, 'My name is %ld.' );
		$message->setTranslation( 'This is invalid: %ld %d.' );
		yield [
			$message,
			$code,
			[ 'variable', 'unknown', $key, $code ]
		];
	}

	/**
	 * @dataProvider provideValidate
	 */
	public function testValidate( TMessage $message, $code, $expected ) {
		$validator = new IosVariableValidator();

		$notices = [];
		$validator->validate( $message, $code, $notices );

		if ( $expected === null ) {
			$this->assertSame( [], $notices );
		} else {
			$this->assertSame( $expected, $notices[ $message->key() ][ 0 ][ 0 ] );
		}
	}
}
