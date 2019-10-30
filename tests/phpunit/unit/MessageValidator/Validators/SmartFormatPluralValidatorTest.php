<?php
/**
 * @file
 * @license GPL-2.0-or-later
 */

use MediaWiki\Extensions\Translate\MessageValidator\Validators\SmartFormatPluralValidator;

/**
 * @covers \MediaWiki\Extensions\Translate\MessageValidator\Validators\SmartFormatPluralValidator
 */
class SmartFormatPluralValidatorTest extends MediaWikiUnitTestCase {
	public static function provideValidate() {
		$key = 'k';
		$code = 'en';
		$message = new FatMessage( $key, '{0:message|messages}' );
		$message->setTranslation( '{1:test|tests}{0:message|messages}' );
		yield [
			$message,
			$code,
			[ 'plural', 'unsupported', $key, $code ]
		];

		$code = 'en';
		$message = new FatMessage( $key, '{0:message|messages}' );
		$message->setTranslation( 'translation' );
		yield [
			$message,
			$code,
			[ 'plural', 'missing', $key, $code ]
		];

		$code = 'en';
		$message = new FatMessage( $key, '{0:message|messages}' );
		$message->setTranslation( '{0:message|messages|messages}' );
		yield [
			$message,
			$code,
			[ 'plural', 'forms', $key, $code ]
		];

		$code = 'en';
		$message = new FatMessage( $key, '{0:message|messages}' );
		$message->setTranslation( '{0:message|messages}' );
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
		$validator = new SmartFormatPluralValidator();

		$notices = [];
		$validator->validate( $message, $code, $notices );

		if ( $expected === null ) {
			$this->assertSame( [], $notices );
		} else {
			$this->assertSame( $expected, $notices[ $message->key() ][ 0 ][ 0 ] );
		}
	}

	public static function provideInsertable() {
		yield [
			'{0:message|messages}',
			'{0:|}',
			'{0:',
			'|}'
		];

		yield [
			'contains no pluralization',
			null
		];
	}

	/**
	 * @dataProvider provideInsertable
	 */
	public function testInsertable( $text, $displayText, $preText = '', $postText = '' ) {
		$validator = new SmartFormatPluralValidator();

		$insertables = $validator->getInsertables( $text );

		if ( $displayText === null ) {
			$this->assertSame( [], $insertables );
		} else {
			$this->assertCount( 1, $insertables );
			$this->assertSame( $insertables[0]->getPreText(), $preText );
			$this->assertSame( $insertables[0]->getPostText(), $postText );
			$this->assertSame( $insertables[0]->getDisplayText(), $displayText );
		}
	}
}
