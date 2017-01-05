<?php

/**
 * Tests for class NumericalParameterInsertablesSuggester
 *
 * @file
 * @author Geoffrey Mon
 * @license GPL-2.0+
 */
class NumericalParameterInsertablesSuggesterTest extends MediaWikiTestCase {

	/**
	 * @dataProvider getInsertablesProvider
	 */
	public function testGetInsertables( $input, $expected ) {
		$suggester = new MediaWikiInsertablesSuggester();
		$this->assertEquals( $expected, $suggester->getInsertables( $input ) );
	}

	public function getInsertablesProvider() {
		return array(
			array( '$1 $2 $3', array(
				new Insertable( '$1', '$1', '' ),
				new Insertable( '$2', '$2', '' ),
				new Insertable( '$3', '$3', '' ),
			) ),
			array( 'test $1 foo $2 bar $3spam eggs', array(
				new Insertable( '$1', '$1', '' ),
				new Insertable( '$2', '$2', '' ),
				new Insertable( '$3', '$3', '' ),
			) ),
			array( '$1 or $2, $15!', array(
				new Insertable( '$1', '$1', '' ),
				new Insertable( '$2', '$2', '' ),
				new Insertable( '$15', '$15', '' ),
			) ),
		);
	}
}
