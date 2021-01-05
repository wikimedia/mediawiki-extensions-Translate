<?php
/**
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */

namespace MediaWiki\Extension\Translate\PageTranslation;

use MediaWiki\Extension\Translate\TranslatorInterface\Insertable\Insertable;
use MediaWikiUnitTestCase;

/** @covers \MediaWiki\Extension\Translate\PageTranslation\TranslatablePageInsertablesSuggester */
class TranslatablePageInsertablesSuggesterTest extends MediaWikiUnitTestCase {
	/** @dataProvider getInsertablesProvider */
	public function testGetInsertables( $input, $expected ) {
		$suggester = new TranslatablePageInsertablesSuggester();
		$this->assertEquals( $expected, $suggester->getInsertables( $input ) );
	}

	public function getInsertablesProvider() {
		return [
			[
				'Hi $1, I am $myname $myLastName.',
				[
					new Insertable( '$1', '$1', '' ),
					new Insertable( '$myname', '$myname', '' ),
					new Insertable( '$myLastName', '$myLastName', '' ),
				]
			],
			[
				'Insertables can $have-hyphens, $number9 and $under_scores',
				[
					new Insertable( '$have-hyphens', '$have-hyphens', '' ),
					new Insertable( '$number9', '$number9', '' ),
					new Insertable( '$under_scores', '$under_scores', '' ),
				]
			],
		];
	}
}
