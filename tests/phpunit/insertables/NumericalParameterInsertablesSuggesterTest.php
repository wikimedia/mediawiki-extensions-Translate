<?php

/**
 * Tests for class NumericalParameterInsertablesSuggester
 *
 * @file
 * @author Geoffrey Mon
 * @license GPL-2.0+
 */
class NumericalParameterInsertablesSuggesterTest extends PHPUnit_Framework_TestCase {

	/**
	 * @dataProvider getInsertablesProvider
	 */
	public function testGetInsertables( $input, $expected ) {
		$suggester = new MediaWikiInsertablesSuggester();
		$this->assertEquals( $expected, $suggester->getInsertables( $input ) );
	}

	public function getInsertablesProvider() {
		return [
			[ '$1 $2 $3', [
				new Insertable( '$1', '$1', '' ),
				new Insertable( '$2', '$2', '' ),
				new Insertable( '$3', '$3', '' ),
			] ],
			[ 'test $1 foo $2 bar $3spam eggs', [
				new Insertable( '$1', '$1', '' ),
				new Insertable( '$2', '$2', '' ),
				new Insertable( '$3', '$3', '' ),
			] ],
			[ '$1 or $2, $15!', [
				new Insertable( '$1', '$1', '' ),
				new Insertable( '$2', '$2', '' ),
				new Insertable( '$15', '$15', '' ),
			] ],
		];
	}
}
