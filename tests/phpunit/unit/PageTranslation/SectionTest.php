<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

use MediaWikiUnitTestCase;

/**
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @covers \MediaWiki\Extension\Translate\PageTranslation\Section
 */
class SectionTest extends MediaWikiUnitTestCase {
	public function test() {
		$section = new Section( '<translate>', '2045903458', '</translate>' );
		$this->assertSame( '2045903458', $section->contents() );
		$this->assertSame( '<translate>2045903458</translate>', $section->wrappedContents() );

		$section = new Section( '<translate>', "\n\n<0>\n\n", '</translate>' );
		$this->assertSame( "\n<0>\n", $section->contents() );
		$this->assertSame( "<translate>\n\n<0>\n\n</translate>", $section->wrappedContents() );
	}
}
