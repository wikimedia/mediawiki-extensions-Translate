<?php
declare( strict_types = 1 );

use MediaWiki\Extension\Translate\Validation\Validators\EscapeCharacterValidator;

/**
 * @license GPL-2.0-or-later
 * @covers \MediaWiki\Extension\Translate\Validation\Validators\EscapeCharacterValidator
 */
class EscapeCharacterValidatorTest extends BaseValidatorTestCase {
	/** @dataProvider provideTestCases */
	public function test( $constructorParams, ...$params ) {
		$validator = new EscapeCharacterValidator( $constructorParams );
		$this->runValidatorTests( $validator, 'escape', ...$params );
	}

	public function provideTestCases() {
		$message = new FatMessage( 'key', 'Hello' );

		yield [
			[ 'values' => [ '\n', '\\\\' ] ],
			'Hello',
			'Hello\n',
			[],
			'Correct escape is not an issue'
		];

		yield [
			[ 'values' => [ '\b' ] ],
			'Hello',
			'Hello\n',
			[ 'invalid' ],
			'Unsupported escape is an issue'
		];

		yield [
			[ 'values' => [ '\\\\', '\n' ] ],
			'Hello',
			'Hello\b',
			[ 'invalid' ],
			'Unsupported escape is an issue'
		];

		yield [
			[ 'values' => [ '\\\\', '\n' ] ],
			'Hello',
			'Hello\\\\',
			[],
			'Correct escape is not an issue'
		];
	}

	public function testEmptyValues() {
		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessageMatches( '/no values provided/i' );
		new EscapeCharacterValidator( [
			'values' => [],
		] );
	}

	public function testInvalidEscape() {
		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessageMatches( '/invalid escape character/i' );
		new EscapeCharacterValidator( [
			'values' => [ '\c' ],
		] );
	}
}
