<?php
/**
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2012-2013, Niklas Laxström
 * @license GPL-2.0-or-later
 */

class MediaWikiMessageCheckerTest extends MediaWikiTestCase {

	/**
	 * @dataProvider getPluralFormCountProvider
	 */
	public function testGetPluralFormCount( $expected, $code, $comment ) {
		$provided = MediaWikiMessageChecker::getPluralFormCount( $code );
		$this->assertEquals( $expected, $provided, $comment );
	}

	public function getPluralFormCountProvider() {
		return [
			[ 2, 'en', 'English has two plural forms' ],
			[ 3, 'ro', 'Romanian has three plural forms' ],
			[ 5, 'br', 'Breton has five plural forms' ],
		];
	}

	/**
	 * @dataProvider getPluralFormsProvider
	 */
	public function testGetPluralForms( $expected, $string, $comment ) {
		$provided = MediaWikiMessageChecker::getPluralForms( $string );
		$this->assertSame( $expected, $provided, $comment );
	}

	public function getPluralFormsProvider() {
		return [
			[
				[ [ '1', '2' ] ],
				'a{{PLURAL:#|1|2}}b',
				'one plural magic word is parsed correctly'
			],

			[
				[ [ '1', '2' ], [ '3', '4' ] ],
				'{{PLURAL:#|1|2}}{{PLURAL:#|3|4}}',
				'two plural magic words are parsed correctly'
			],

			[
				[ [ '1', '2{{}}3' ] ],
				'a{{PLURAL:#|1|2{{}}3}}',
				'one plural magic word with curlies inside is parsed correctly'
			],

			[
				[ [ '0=0', '1=one', '1', '2' ] ],
				'a{{PLURAL:#|0=0|1=one|1|2}}',
				'one plural magic word with explicit forms is parsed correctly'
			],
			[
				[],
				'a{{PLURAL:#|0=0|1=one|1|2}',
				'unclosed plural tag is ignored'
			],
			[
				[ [ '1=foo', '{{GENDER:#|he}}' ] ],
				'a{{PLURAL:#|1=foo|{{GENDER:#|he}}}}',
				'pipes in subtemplates are ignored'
			],
			[
				[ [ '[[Special:A|письмо]]', '[[Special:A|писем]]', '[[Special:A|письма]]' ] ],
				'{{PLURAL:#|[[Special:A|письмо]]|[[Special:A|писем]]|[[Special:A|письма]]}}',
				'pipes in links are ignored'
			],
			[
				[
					[ 'a', 'b' ],
					[ 'c', 'd' ],
					[ '{{PLURAL:#|a|b}}', '{{PLURAL:#|c|d}}' ],
					],
				'{{PLURAL:#|{{PLURAL:#|a|b}}|{{PLURAL:#|c|d}}}}',
				'nested plurals are handled correctly'
			]
		];
	}

	/**
	 * @dataProvider removeExplicitPluralFormsProvider
	 */
	public function testRemoveExplicitPluralForms( $expected, $forms, $comment ) {
		$provided = MediaWikiMessageChecker::removeExplicitPluralForms( $forms );
		$this->assertEquals( $expected, $provided, $comment );
	}

	public function removeExplicitPluralFormsProvider() {
		return [
			[
				[ '1', '2' ],
				[ '1', '2' ],
				'default forms are not removed',
			],

			[
				[ '1', '2' ],
				[ '0=0', '1', '0=0', '2', '1=one' ],
				'explicit forms are removed regardless of position',
			],

			[
				[ '1', '2' ],
				[ '1', '2', '500=lots' ],
				'works for any number',
			],
		];
	}
}
