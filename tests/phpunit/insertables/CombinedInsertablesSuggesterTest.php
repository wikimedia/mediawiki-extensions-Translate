<?php

/**
 * Tests for class CombinedInsertablesSuggester
 *
 * @file
 * @author Geoffrey Mon
 * @license GPL-2.0+
 */
class CombinedInsertablesSuggesterTest extends MediaWikiTestCase {

	/**
	 * @dataProvider getInsertablesProvider
	 */
	public function testGetInsertables( $input, $expected ) {
		$suggester = new CombinedInsertablesSuggester( array(
			new FakeInsertablesSuggester(),
			new NumericalParameterInsertablesSuggester(),
		) );
		$this->assertEquals( $expected, $suggester->getInsertables( $input ) );
	}

	public function getInsertablesProvider() {
		return array(
			array( 'test $1 foo $2 bar $3spam eggs', array(
				new Insertable( 'Test', 'Test', '' ),
				new Insertable( '$1', '$1', '' ),
				new Insertable( '$2', '$2', '' ),
				new Insertable( '$3', '$3', '' ),
			) ),
		);
	}
}

class FakeInsertablesSuggester implements InsertablesSuggester {
	public function getInsertables( $text ) {
		return new Insertable( 'Test', 'Test', '' );
	}
}
