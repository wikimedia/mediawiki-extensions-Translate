<?php
/**
 * @file
 * @license GPL-2.0-or-later
 */

use MediaWiki\Extensions\Translate\MessageValidator\Validators\MatchSetValidator;

/**
 * @covers \MediaWiki\Extensions\Translate\MessageValidator\Validators\MatchSetValidator
 */
class MatchSetValidatorTest extends MediaWikiUnitTestCase {
	public static function provideValidate() {
		$key = 'key';
		$code = 'en-gb';
		$message = new FatMessage( $key, 'rtl' );
		$message->setTranslation( 'ltr' );

		yield [
			[
				'values' => [ 'rtl', 'Ltr' ],
			],
			$message,
			$code,
			[ 'value-not-present', 'invalid', $key, $code ]
		];

		yield [
			[
				'values' => [ 'rtl', 'Ltr' ],
				'caseSensitive' => false
			],
			$message,
			$code,
			null
		];

		yield [
			[
				'values' => [ 'rtl', 'etc' ]
			],
			$message,
			$code,
			[ 'value-not-present', 'invalid', $key, $code ]
		];
	}

	/**
	 * @dataProvider provideValidate
	 */
	public function testValidate( array $config, TMessage $message, $code, $expected ) {
		$validator = new MatchSetValidator( $config );

		$notice = [];
		$validator->validate( $message, $code, $notice );

		if ( $expected === null ) {
			$this->assertEmpty( $notice );
		} else {
			$this->assertSame( $expected, $notice[ $message->key() ][ 0 ][ 0 ] );
		}
	}

	public function testEmptyValues() {
		$this->expectException( \InvalidArgumentException::class );
		new MatchSetValidator( [
			'values' => [],
		] );
	}
}
