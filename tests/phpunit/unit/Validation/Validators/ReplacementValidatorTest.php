<?php
declare( strict_types = 1 );

use MediaWiki\Extension\Translate\Validation\Validators\ReplacementValidator;

/**
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @covers \MediaWiki\Extension\Translate\Validation\Validators\ReplacementValidator
 */
class ReplacementValidatorTest extends BaseValidatorTestCase {
	/** @dataProvider provideTestCases */
	public function test( $constructorParams, ...$params ) {
		$validator = new ReplacementValidator( $constructorParams );
		$this->runValidatorTests( $validator, 'replacement', ...$params );
	}

	public static function provideTestCases() {
		yield [
			[ 'search' => 'rude', 'replace' => 'nice' ],
			'Grumpy bunny is kind',
			'Grumpy bunny is rude',
			[ 'replacement' ],
			'Search match is an issue'
		];

		yield [
			[ 'search' => 'rude', 'replace' => 'nice' ],
			'Grumpy bunny is kind',
			'Grumpy bunny is nice',
			[],
			'No match is not an issue'
		];
	}

	/** @dataProvider provideBadTestCases */
	public function testBadValues( $params ) {
		$this->expectException( InvalidArgumentException::class );
		new ReplacementValidator( $params );
	}

	public static function provideBadTestCases() {
		yield [ [ 'oma maa' ] ];
		yield [ [ 'source' => 'mansikka' ] ];
		yield [ [ 'replacement' => 'mustikka' ] ];
	}
}
