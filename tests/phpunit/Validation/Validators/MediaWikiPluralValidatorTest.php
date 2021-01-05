<?php
declare( strict_types = 1 );

use MediaWiki\Extension\Translate\Validation\Validators\MediaWikiPluralValidator;

/**
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 * @covers \MediaWiki\Extension\Translate\Validation\Validators\MediaWikiPluralValidator
 */
class MediaWikiPluralValidatorTest extends PHPUnit\Framework\TestCase {
	/** @dataProvider getPluralFormCountProvider */
	public function testGetPluralFormCount( $expected, $code, $comment ) {
		$provided = MediaWikiPluralValidator::getPluralFormCount( $code );
		$this->assertEquals( $expected, $provided, $comment );
	}

	public function getPluralFormCountProvider() {
		yield [ 2, 'en', 'English has two plural forms' ];
		yield [ 3, 'ro', 'Romanian has three plural forms' ];
		yield [ 5, 'br', 'Breton has five plural forms' ];
	}

	/** @dataProvider getPluralFormsProvider */
	public function testGetPluralForms( $expected, $string, $comment ) {
		$provided = MediaWikiPluralValidator::getPluralForms( $string );
		$this->assertSame( $expected, $provided, $comment );
	}

	public function getPluralFormsProvider() {
		yield [
			[ [ '1', '2' ] ],
			'a{{PLURAL:#|1|2}}b',
			'one plural magic word is parsed correctly'
		];

		yield [
			[ [ '1', '2' ], [ '3', '4' ] ],
			'{{PLURAL:#|1|2}}{{PLURAL:#|3|4}}',
			'two plural magic words are parsed correctly'
		];

		yield [
			[ [ '1', '2{{}}3' ] ],
			'a{{PLURAL:#|1|2{{}}3}}',
			'one plural magic word with curlies inside is parsed correctly'
		];

		yield [
			[ [ '0=0', '1=one', '1', '2' ] ],
			'a{{PLURAL:#|0=0|1=one|1|2}}',
			'one plural magic word with explicit forms is parsed correctly'
		];

		yield [
			[],
			'a{{PLURAL:#|0=0|1=one|1|2}',
			'unclosed plural tag is ignored'
		];

		yield [
			[ [ '1=foo', '{{GENDER:#|he}}' ] ],
			'a{{PLURAL:#|1=foo|{{GENDER:#|he}}}}',
			'pipes in subtemplates are ignored'
		];

		yield [
			[ [ '[[Special:A|письмо]]', '[[Special:A|писем]]', '[[Special:A|письма]]' ] ],
			'{{PLURAL:#|[[Special:A|письмо]]|[[Special:A|писем]]|[[Special:A|письма]]}}',
			'pipes in links are ignored'
		];

		yield [
			[
				[ 'a', 'b' ],
				[ 'c', 'd' ],
				[ '{{PLURAL:#|a|b}}', '{{PLURAL:#|c|d}}' ],
			],
			'{{PLURAL:#|{{PLURAL:#|a|b}}|{{PLURAL:#|c|d}}}}',
			'nested plurals are handled correctly'
		];
	}

	/** @dataProvider removeExplicitPluralFormsProvider */
	public function testRemoveExplicitPluralForms( $expected, $forms, $comment ) {
		$provided = MediaWikiPluralValidator::removeExplicitPluralForms( $forms );
		$this->assertEquals( $expected, $provided, $comment );
	}

	public function removeExplicitPluralFormsProvider() {
		yield [
			[ '1', '2' ],
			[ '1', '2' ],
			'default forms are not removed',
		];

		yield [
			[ '1', '2' ],
			[ '0=0', '1', '0=0', '2', '1=one' ],
			'explicit forms are removed regardless of position',
		];

		yield [
			[ '1', '2' ],
			[ '1', '2', '500=lots' ],
			'works for any number',
		];
	}
}
