<?php
declare( strict_types = 1 );

use MediaWiki\Extension\Translate\Validation\Validators\SmartFormatPluralValidator;

/**
 * @license GPL-2.0-or-later
 * @covers \MediaWiki\Extension\Translate\Validation\Validators\SmartFormatPluralValidator
 */
class SmartFormatPluralValidatorTest extends BaseValidatorTestCase {
	/** @dataProvider provideTestCases */
	public function test( ...$params ) {
		$this->runValidatorTests( new SmartFormatPluralValidator(), 'plural', ...$params );
	}

	public function provideTestCases() {
		yield [
			'{0:message|messages}',
			'{1:test|tests}{0:message|messages}',
			[ 'unsupported' ],
			'Using plural on an unsupported parameter is an issue'
		];

		yield [
			'{0:message|messages}',
			'translation',
			[ 'missing' ],
			'Missing plural on an unsupported parameter is an issue'
		];

		yield [
			'{0:message|messages}',
			'{0:message|messages|messages}',
			[ 'forms' ],
			'Extra plural form is an issue'
		];

		yield [
			'{0:message|messages}',
			'{0:message|messages}',
			[],
			'Correct plural forms are not an issue'
		];
	}

	/** @dataProvider provideInsertable */
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
}
