<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TranslatorInterface\Insertable;

use MediaWiki\Extension\Translate\Validation\Validators\FluentVariableValidator;
use MediaWikiUnitTestCase;

/**
 * @license GPL-2.0-or-later
 * @covers \MediaWiki\Extension\Translate\Validation\Validators\FluentVariableValidator
 */
class FluentVariableValidatorInsertableTest extends MediaWikiUnitTestCase {
	/** @dataProvider provideGetInsertables */
	public function testGetInsertables( string $input, array $expected ): void {
		$validator = new FluentVariableValidator();
		$this->assertEquals( $expected, $validator->getInsertables( $input ) );
	}

	public static function provideGetInsertables(): array {
		return [
			'single variable' => [
				'Hello, { $name }!',
				[ new Insertable( '{ $name }', '{ $name }', '' ) ],
			],
			'multiple variables' => [
				'{ $user } has { $count } messages.',
				[
					new Insertable( '{ $user }', '{ $user }', '' ),
					new Insertable( '{ $count }', '{ $count }', '' ),
				],
			],
			'compact spacing' => [
				'Hello {$name}!',
				[ new Insertable( '{$name}', '{$name}', '' ) ],
			],
			'no variables' => [
				'No variables here.',
				[],
			],
			'hyphenated variable name' => [
				'{ $user-name }',
				[ new Insertable( '{ $user-name }', '{ $user-name }', '' ) ],
			],
		];
	}
}
