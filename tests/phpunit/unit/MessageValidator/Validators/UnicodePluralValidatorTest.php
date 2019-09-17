<?php
/**
 * @file
 * @license GPL-2.0-or-later
 */

use MediaWiki\Extensions\Translate\MessageValidator\Validators\UnicodePluralValidator;

/**
 * @covers \MediaWiki\Extensions\Translate\MessageValidator\Validators\UnicodePluralValidator
 */
class UnicodePluralValidatorTest extends MediaWikiUnitTestCase {
	public static function provideValidate() {
		$key = 'k';
		$code = 'en';
		$message = new FatMessage( $key, '{{PLURAL|one=a|b}}' );
		$message->setTranslation( 'a' );
		yield [
			$message,
			$code,
			[ 'plural', 'missing', $key, $code ]
		];

		$code = 'en';
		$message = new FatMessage( $key, 'a' );
		$message->setTranslation( '{{PLURAL|one=a|b}}' );
		yield [
			$message,
			'en',
			[ 'plural', 'unsupported', $key, $code ]
		];

		$code = 'en';
		$message = new FatMessage( $key, '{{PLURAL|one=a|b}}' );
		$message->setTranslation( '{{PLURAL|one=a|b}}' );
		yield [
			$message,
			'en',
			null
		];

		$code = 'en';
		$message = new FatMessage( $key, '{{PLURAL|one=a|b}}' );
		$message->setTranslation( '{{PLURAL|one=a|two=b|c}}' );
		yield [
			$message,
			'en',
			[ 'plural', 'forms', $key, $code ]
		];
	}

	/**
	 * @dataProvider provideValidate
	 */
	public function testValidate( TMessage $message, $code, $expected ) {
		$validator = new UnicodePluralValidator();

		$notices = [];
		$validator->validate( $message, $code, $notices );

		if ( $expected === null ) {
			$this->assertSame( [], $notices );
		} else {
			$this->assertSame( $expected, $notices[ $message->key() ][ 0 ][ 0 ] );
		}
	}
}
