<?php
/**
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */

namespace MediaWiki\Extension\Translate\TranslatorInterface\Insertable;

use MediaWikiUnitTestCase;

/** @covers \MediaWiki\Extension\Translate\TranslatorInterface\Insertable\MediaWikiInsertablesSuggester */
class MediaWikiInsertablesSuggesterTest extends MediaWikiUnitTestCase {
	/** @dataProvider getInsertablesProvider */
	public function testGetInsertables( $input, $expected ) {
		$suggester = new MediaWikiInsertablesSuggester();
		$this->assertEquals( $expected, $suggester->getInsertables( $input ) );
	}

	public function getInsertablesProvider() {
		return [
			[ 'Hi $1', [
				new Insertable( '$1', '$1', '' )
			] ],
			[ 'Hello $1user', [
				new Insertable( '$1user', '$1user', '' ),
			] ],
			[ '{{GENDER:$1|he|she}}', [
				new Insertable( '$1', '$1', '' ),
				new Insertable( 'GENDER:$1', '{{GENDER:$1|', '}}' ),
			] ],
			// Parameterless gender
			[ '{{GENDER:|he|she}}', [
				new Insertable( 'GENDER:', '{{GENDER:|', '}}' ),
			] ],
			[
				'Hello <a href="https://en.wikipedia.org">World!</a>',
				[
					new Insertable(
						'<a href="https://en.wikipedia.org"></a>',
						'<a href="https://en.wikipedia.org">',
						'</a>'
					)
				]
			]
		];
	}
}
