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
	public function testGetInsertables( $suggesters, $input, $expected ) {
		$suggester = new CombinedInsertablesSuggester( $suggesters );
		$this->assertArrayEquals( $expected, $suggester->getInsertables( $input ) );
	}

	public function getInsertablesProvider() {
		return array(
			// Test basic combination of multiple InsertablesSuggesters
			array(
				array(
					new TestingInsertablesSuggester(),
					new NumericalParameterInsertablesSuggester(),
				),
				'test $1 foo $2 bar $3spam eggs',
				array(
					new Insertable( 'Test', 'Test', '' ),
					new Insertable( '$1', '$1', '' ),
					new Insertable( '$2', '$2', '' ),
					new Insertable( '$3', '$3', '' ),
				)
			),
			// Test removal of duplicate suggestions
			array(
				array(
					new NumericalParameterInsertablesSuggester(),
					new NumericalParameterInsertablesSuggester(),
				),
				'test $1 duplicates $2 $3',
				array(
					new Insertable( '$1', '$1', '' ),
					new Insertable( '$2', '$2', '' ),
					new Insertable( '$3', '$3', '' ),
				)
			),
			// Test removal of duplicate suggestions
			array(
				array(
					new TestingDuplicateInsertablesSuggester(),
					new NumericalParameterInsertablesSuggester(),
				),
				'test $1 duplicates $2 $3',
				array(
					new Insertable( '$1', '$1', '' ),
					new Insertable( '$2', '$2', '' ),
					new Insertable( '$3', '$3', '' ),
					new Insertable( 'Test', 'Test', '' ),
					new Insertable( '', 'Test', 'Test' ),
				)
			),
			// Test no InsertablesSuggesters
			array(
				array(),
				'test $1 duplicates $2 $3',
				array()
			),
		);
	}
}

class TestingInsertablesSuggester implements InsertablesSuggester {
	public function getInsertables( $text ) {
		return array( new Insertable( 'Test', 'Test', '' ) );
	}
}

class TestingDuplicateInsertablesSuggester implements InsertablesSuggester {
	public function getInsertables( $text ) {
		return array(
			new Insertable( '$1', '$1', '' ),
			new Insertable( '$1', '$1', '' ),
			new Insertable( 'Test', 'Test', '' ),
			new Insertable( 'Test', 'Test', '' ),
			new Insertable( '', 'Test', 'Test' ),
		);
	}
}
