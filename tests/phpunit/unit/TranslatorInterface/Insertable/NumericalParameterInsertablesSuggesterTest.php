<?php
/**
 * @file
 * @author Geoffrey Mon
 * @license GPL-2.0-or-later
 */

namespace MediaWiki\Extension\Translate\TranslatorInterface\Insertable;

use MediaWikiUnitTestCase;

/** @covers \MediaWiki\Extension\Translate\TranslatorInterface\Insertable\NumericalParameterInsertablesSuggester */
class NumericalParameterInsertablesSuggesterTest extends MediaWikiUnitTestCase {
	/** @dataProvider getInsertablesProvider */
	public function testGetInsertables( $input, $expected ) {
		$suggester = new NumericalParameterInsertablesSuggester();
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
