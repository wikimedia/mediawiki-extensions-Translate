<?php
declare( strict_types = 1 );

use MediaWiki\Extension\Translate\Utilities\MediaWikiPlural;

/**
 * @license GPL-2.0-or-later
 * @covers \MediaWiki\Extension\Translate\Utilities\MediaWikiPlural
 */
class MediaWikiPluralTest extends MediaWikiUnitTestCase {
	public static function provideHasPlural() {
		yield 'no plural' => [ false, 'Hello $1' ];
		yield 'one form' => [ true, '{{PLURAL:$1|item}}' ];
		yield 'multiple forms' => [ true, '$1 {{PLURAL:$1|item|items}}' ];
		yield 'explicit number forms' => [ true, '{{PLURAL:$1|0=none|one|many}}' ];
		yield 'lowercase' => [ true, '{{plural:$1|item|items}}' ];
		yield 'fuzzy marker' => [ true, '!!FUZZY!!{{PLURAL:$1|item|items}}' ];
		yield 'gettext plural' => [ false, '{{PLURAL:GETTEXT|item|items}}' ];
		yield 'unicode plural' => [ false, '{{PLURAL|one=item|items}}' ];
		yield 'gettext and mediawiki plural' => [ true, '{{PLURAL:GETTEXT|a|b}} {{PLURAL:$1|c|d}}' ];
	}

	/** @dataProvider provideHasPlural */
	public function testHasPlural( bool $expected, string $text ) {
		$this->assertSame( $expected, MediaWikiPlural::hasPlural( $text ) );
	}
}
