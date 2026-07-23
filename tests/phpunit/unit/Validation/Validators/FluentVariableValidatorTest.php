<?php
declare( strict_types = 1 );

use MediaWiki\Extension\Translate\Validation\Validators\FluentVariableValidator;

/**
 * @license GPL-2.0-or-later
 * @covers \MediaWiki\Extension\Translate\Validation\Validators\FluentVariableValidator
 */
class FluentVariableValidatorTest extends BaseValidatorTestCase {

	/** @dataProvider provideTestCases */
	public function test( ...$params ): void {
		$this->runValidatorTests( new FluentVariableValidator(), 'variable', ...$params );
	}

	public static function provideTestCases(): array {
		return [
			'missing variable is an issue' => [
				'Hello, { $name }!',
				'Hello!',
				[ 'missing' ],
				'Missing Fluent variable should be reported',
			],
			'unknown variable is an issue' => [
				'Hello!',
				'Hello, { $name }!',
				[ 'unknown' ],
				'Unknown Fluent variable should be reported',
			],
			'typoed variable is two issues' => [
				'Hello, { $name }!',
				'Hello, { $naam }!',
				[ 'missing', 'unknown' ],
				'Typoed Fluent variable should be two issues',
			],
			'all variables present, no issues' => [
				'Hello, { $name }! You have { $count } messages.',
				'Hola, { $name }! Tienes { $count } mensajes.',
				[],
				'All variables present should produce no issues',
			],
			'no variables, no issues' => [
				'Hello!',
				'Hola!',
				[],
				'No variables should produce no issues',
			],
		];
	}
}
