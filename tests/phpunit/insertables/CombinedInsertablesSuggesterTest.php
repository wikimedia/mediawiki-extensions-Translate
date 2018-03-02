<?php

/**
 * Tests for class CombinedInsertablesSuggester
 *
 * @file
 * @author Geoffrey Mon
 * @license GPL-2.0-or-later
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
		return [
			// Test basic combination of multiple InsertablesSuggesters
			[
				[
					new TestingInsertablesSuggester(),
					new NumericalParameterInsertablesSuggester(),
				],
				'test $1 foo $2 bar $3spam eggs',
				[
					new Insertable( 'Test', 'Test', '' ),
					new Insertable( '$1', '$1', '' ),
					new Insertable( '$2', '$2', '' ),
					new Insertable( '$3', '$3', '' ),
				]
			],
			// Test removal of duplicate suggestions
			[
				[
					new NumericalParameterInsertablesSuggester(),
					new NumericalParameterInsertablesSuggester(),
				],
				'test $1 duplicates $2 $3',
				[
					new Insertable( '$1', '$1', '' ),
					new Insertable( '$2', '$2', '' ),
					new Insertable( '$3', '$3', '' ),
				]
			],
			// Test removal of duplicate suggestions
			[
				[
					new TestingDuplicateInsertablesSuggester(),
					new NumericalParameterInsertablesSuggester(),
				],
				'test $1 duplicates $2 $3',
				[
					new Insertable( '$1', '$1', '' ),
					new Insertable( '$2', '$2', '' ),
					new Insertable( '$3', '$3', '' ),
					new Insertable( 'Test', 'Test', '' ),
					new Insertable( '', 'Test', 'Test' ),
				]
			],
			// Test no InsertablesSuggesters
			[
				[],
				'test $1 duplicates $2 $3',
				[]
			],
		];
	}
}

class TestingInsertablesSuggester implements InsertablesSuggester {
	public function getInsertables( $text ) {
		return [ new Insertable( 'Test', 'Test', '' ) ];
	}
}

class TestingDuplicateInsertablesSuggester implements InsertablesSuggester {
	public function getInsertables( $text ) {
		return [
			new Insertable( '$1', '$1', '' ),
			new Insertable( '$1', '$1', '' ),
			new Insertable( 'Test', 'Test', '' ),
			new Insertable( 'Test', 'Test', '' ),
			new Insertable( '', 'Test', 'Test' ),
		];
	}
}
