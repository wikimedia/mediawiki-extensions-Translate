<?php
declare( strict_types = 1 );

use MediaWiki\Extension\Translate\Utilities\ParsingPlaceholderFactory;

/**
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @covers \MediaWiki\Extension\Translate\Utilities\ParsingPlaceholderFactory
 */
class ParsingPlaceholderFactoryTest extends MediaWikiUnitTestCase {
	public function testMake() {
		$obj = new ParsingPlaceholderFactory();

		if ( method_exists( $this, 'assertMatchesRegularExpression' ) ) {
			// PHPUnit 9.x+
			$this->assertMatchesRegularExpression( '/[a-zA-Z0-9\x7f]/', $obj->make() );

		} else {
			// PHPUnit 8.x, to be dropped when dropping master compatibility with REL1_39
			$this->assertRegExp( '/[a-zA-Z0-9\x7f]/', $obj->make() );
		}
	}
}
