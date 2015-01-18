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
			array(
				'Hi $1, I am $myname $myLastName.',
				array(
					new Insertable( '$1', '$1', '' ),
					new Insertable( '$myname', '$myname', '' ),
					new Insertable( '$myLastName', '$myLastName', '' ),
				)
			),
			array(
				'Insertables can $have-hyphens, $number9 and $under_scores',
				array(
					new Insertable( '$have-hyphens', '$have-hyphens', '' ),
					new Insertable( '$number9', '$number9', '' ),
					new Insertable( '$under_scores', '$under_scores', '' ),
				)
			),
		);
	}
}
