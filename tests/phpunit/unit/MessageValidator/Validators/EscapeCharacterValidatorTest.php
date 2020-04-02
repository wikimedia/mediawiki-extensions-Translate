<?php
/**
 * @file
 * @license GPL-2.0-or-later
 */

use MediaWiki\Extensions\Translate\MessageValidator\Validators\EscapeCharacterValidator;

/**
 * @covers \MediaWiki\Extensions\Translate\MessageValidator\Validators\EscapeCharacterValidator
 */
class EscapeCharacterValidatorTest extends MediaWikiUnitTestCase {
	public static function provideValidate() {
		$key = 'key';
		$code = 'en-gb';
		$message = new FatMessage( $key, 'Hello' );

		yield [
			'Hello\n',
			[
				'values' => [ '\n', '\\\\' ],
			],
			$message,
			$code,
			null
		];

		yield [
			'Hello\n',
			[
				'values' => [ '\b' ],
			],
			$message,
			$code,
			[ 'escape', 'invalid', $key, $code ]
		];

		yield [
			'Hello \b',
			[
				'values' => [ '\\\\', '\n' ]
			],
			$message,
			$code,
			[ 'escape', 'invalid', $key, $code ]
		];

		yield [
			'Hello\\\\',
			[
				'values' => [ '\\\\', '\n' ]
			],
			$message,
			$code,
			null
		];
	}

	/**
	 * @dataProvider provideValidate
	 */
	public function testValidate(
		string $translation, array $config, FatMessage $message, $code, $expected
	) {
		$message->setTranslation( $translation );
		$validator = new EscapeCharacterValidator( $config );

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
		$this->expectExceptionMessageMatches( '/no values provided/i' );
		new EscapeCharacterValidator( [
			'values' => [],
		] );
	}

	public function testInvalidEscape() {
		$this->expectException( \InvalidArgumentException::class );
		$this->expectExceptionMessageMatches( '/invalid escape character/i' );
		new EscapeCharacterValidator( [
			'values' => [ '\c' ],
		] );
	}
}
