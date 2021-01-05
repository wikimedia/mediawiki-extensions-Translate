<?php
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
		yield [
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
	}
}
