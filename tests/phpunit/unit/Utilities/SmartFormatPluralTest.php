<?php
declare( strict_types = 1 );

/**
 * @file
 * @license GPL-2.0-or-later
 */

use MediaWiki\Extension\Translate\Utilities\SmartFormatPlural;

/** @covers \MediaWiki\Extension\Translate\Utilities\SmartFormatPlural */
class SmartFormatPluralTest extends MediaWikiUnitTestCase {
	/** @dataProvider provideTestGetPluralInstances */
	public function testGetPluralInstances( $input, $expected ) {
		$actual = SmartFormatPlural::getPluralInstances( $input );
		$this->assertEquals( $expected, $actual );
	}

	public static function provideTestGetPluralInstances() {
		yield 'Multiple plural instances with variables' => [
			'{0} {0:message|messages} older than {1} {1:week|weeks} {0:has|have} been deleted.',
			[
				'0' => [
					[
						'forms' => [ 'message', 'messages' ],
						'original' => '{0:message|messages}',
					],
					[
						'forms' => [ 'has', 'have' ],
						'original' => '{0:has|have}',
					],
				],
				'1' => [
					[
						'forms' => [ 'week', 'weeks' ],
						'original' => '{1:week|weeks}',
					],
				],
			]
		];

		yield 'Parameter used inside plural form' => [
			'{0:דקה אחת|{0} דקות}',
			[
				'0' => [
					[
						'forms' => [ 'דקה אחת', '{0} דקות' ],
						'original' => '{0:דקה אחת|{0} דקות}',
					],
				],
			]
		];

		yield 'Parameter at the end of plural form' => [
			'{0:zero|{0}}',
			[
				'0' => [
					[
						'forms' => [ 'zero', '{0}' ],
						'original' => '{0:zero|{0}}',
					],
				],
			]
		];

		yield 'Multiple plural forms with parameter references' => [
			'{1:שנייה אחת|{1} שניות|{1} שניות|{1} שניות}',
			[
				'1' => [
					[
						'forms' => [ 'שנייה אחת', '{1} שניות', '{1} שניות', '{1} שניות' ],
						'original' => '{1:שנייה אחת|{1} שניות|{1} שניות|{1} שניות}',
					],
				],
			]
		];

		yield 'Empty plural form' => [
			'{0:}',
			[
				'0' => [
					[
						'forms' => [ '' ],
						'original' => '{0:}',
					],
				],
			]
		];

		yield 'Escaped characters inside plural form' => [
			'{0:\{one\}|\|many\|}',
			[
				'0' => [
					[
						'forms' => [ '\{one\}', '\|many\|' ],
						'original' => '{0:\{one\}|\|many\|}',
					],
				],
			]
		];
	}
}
