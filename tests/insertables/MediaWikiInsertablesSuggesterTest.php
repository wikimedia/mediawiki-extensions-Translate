<?php
/**
 * Tests for class MediaWikiInsertablesSuggester
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0+
 */

class MediaWikiMessageCheckerTest extends MediaWikiTestCase {

	/**
	 * @dataProvider getInsertablesProvider
	 */
	public function testGetInsertables( $input, $expected ) {
		$suggester = new MediaWikiInsertablesSuggester();
		$this->assertEquals( $expected, $suggester->getInsertables( $input ) );
	}

	public function getInsertablesProvider() {
		return array(
			array( 'Hi $1', array(
				new Insertable( '$1', '$1', '' )
			) ),
			array( '{{GENDER:$1|he|she}}', array(
				new Insertable( '$1', '$1', '' ),
				new Insertable( 'GENDER:$1', '{{GENDER:$1|', '}}' ),
			) ),
		);
	}
}
