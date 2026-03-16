<?php
declare( strict_types = 1 );

use MediaWiki\Extension\Translate\MessageLoading\FatMessage;
use MediaWiki\Extension\Translate\Validation\Validators\NotSupportedValidator;

/**
 * @license GPL-2.0-or-later
 * @covers \MediaWiki\Extension\Translate\Validation\Validators\NotSupportedValidator
 */
class NotSupportedValidatorTest extends BaseValidatorTestCase {
	/** @dataProvider provideTestCases */
	public function test( $constructorParams, ...$params ) {
		$validator = new NotSupportedValidator( $constructorParams );
		$this->runValidatorTests( $validator, 'notsupported', ...$params );
	}

	public static function provideTestCases() {
		yield [
			[ 'regex' => '/\{\{\s*gender\s*:/i' ],
			'Use neutral phrasing',
			'Use {{gender:$1|he|she}}.',
			[ 'notsupported' ],
			'Regex match is case-insensitive'
		];

		yield [
			[ 'regex' => '/\{\{GENDER:[^}]*\}\}/', 'display' => '{{GENDER:...}}' ],
			'Use neutral phrasing',
			'Use {{GENDER:male|female|unknown}}.',
			[ 'notsupported' ],
			'Display override can hide the full matched text'
		];
	}

	/** @dataProvider provideBadTestCases */
	public function testBadValues( string $exceptionClass, $params ) {
		$this->expectException( $exceptionClass );
		new NotSupportedValidator( $params );
	}

	public static function provideBadTestCases() {
		yield [ InvalidArgumentException::class, [] ];
		yield [ TypeError::class, [ 'regex' => [ '/\{\{GENDER:/i' ] ] ];
		yield [ InvalidArgumentException::class, [ 'regex' => '/([a-z+/' ] ];
	}

	public function testDisplayOverrideIsUsedInMessageParams() {
		$validator = new NotSupportedValidator( [
			'regex' => '/\{\{GENDER:[^}]*\}\}/',
			'display' => '{{GENDER:...}}',
		] );

		$message = new FatMessage( 'key', 'Definition' );
		$message->setTranslation( 'Use {{GENDER:male|female|unknown}}.' );

		$issues = iterator_to_array( $validator->getIssues( $message, 'fr' ) );
		$this->assertCount( 1, $issues );
		$this->assertSame( [ [ 'PLAIN', '{{GENDER:...}}' ] ], $issues[0]->messageParams() );
	}
}
