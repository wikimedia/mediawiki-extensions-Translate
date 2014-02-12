<?php
/**
 * Tests for class TranslatablePageInsertablesSuggester
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0+
 */

class TranslatablePageInsertablesSuggesterTest extends MediaWikiInsertablesSuggesterTest {
	/**
	 * @dataProvider getInsertablesProvider
	 */
	public function testGetInsertables( $input, $expected ) {
		$suggester = new TranslatablePageInsertablesSuggester();
		$this->assertEquals( $expected, $suggester->getInsertables( $input ) );
	}

	public function getInsertablesProvider() {
		return array(
			array( 'Hi $1, I am $myname $my-middle-name $myLastName.', array(
				new Insertable( '$1', '$1', '' ),
				new Insertable( '$myname', '$myname', '' ),
				new Insertable( '$my-middle-name', '$my-middle-name', '' ),
				new Insertable( '$myLastName', '$myLastName', '' ),
			) ),
		);
	}
}
