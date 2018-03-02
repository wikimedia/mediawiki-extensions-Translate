<?php
/**
 * Tests for class MediaWikiInsertablesSuggester
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */

class MediaWikiInsertablesSuggesterTest extends PHPUnit\Framework\TestCase {

	/**
	 * @dataProvider getInsertablesProvider
	 */
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
		];
	}
}
