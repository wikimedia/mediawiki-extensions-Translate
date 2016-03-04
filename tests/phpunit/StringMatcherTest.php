<?php
/**
 * The StringMatcher class is responsible for making sure message keys
 * from external sources are valid titles in MediaWiki.
 * @author Niklas Laxström
 * @file
 */

class StringMatcherTest extends MediaWikiTestCase {
	/**
	 * @dataProvider messageKeyProvider
	 */
	public function testKeyPrefixing( $key, $expected, $prefix, $rules, $comment ) {
		$matcher = new StringMatcher( $prefix, $rules );
		$mangled = $matcher->mangle( $key );
		$title = Title::makeTitleSafe( NS_MEDIAWIKI, $mangled );
		$this->assertInstanceOf( 'Title', $title, "Key '$mangled' did not produce valid title" );
		$unmangled = $matcher->unmangle( $mangled );
		$this->assertEquals( $key, $unmangled, 'Mangling is reversable' );
		$this->assertEquals( $expected, $mangled, 'Message is prefixed correctly' );
	}

	public function messageKeyProvider() {
		// The fourth parameter causes the key to be prefixed or unprefixed
		$keys = array(
			array( 'key', 'p-key', 'p-', array( 'key' ), 'Exact match' ),
			array( 'key', 'key', 'p-', array( 'bar' ), 'Exact not match' ),
			array( 'key', 'p-key', 'p-', array( 'k*' ), 'Prefix match' ),
			array( 'key', 'key', 'p-', array( 'b*' ), 'Prefix not match' ),
			array( 'key', 'p-key', 'p-', array( '*y' ), 'Suffix match' ),
			array( 'key', 'key', 'p-', array( '*r' ), 'Suffix not match' ),
			array( 'key', 'p-key', 'p-', array( 'k*y' ), 'Wildcard match' ),
			array( 'key', 'key', 'p-', array( '*a*' ), 'Wildcard not match' ),
			array( 'key', 'p-key', 'p-', array( 'key', '*ey', 'ke*' ), 'Multiple rules match' ),
			array( 'key', 'key', 'p-', array( '*a*', '*ar', 'ba*' ), 'Multiple rules not match' ),
			array( 'key', 'p-key', 'p-', array( '*' ), 'All match' ),
			array(
				'[k.ssa]', 'p-=5Bk.ssa=5D', 'p-', array( '[k.s*' ),
				'Message key with special chars'
			),
			array(
				'[kissa]', '=5Bkissa=5D', 'p-', array( '[k.s*' ),
				'Message key with special chars'
			),
		);

		return $keys;
	}

	/**
	 * @dataProvider problematicMessageKeyProvider
	 */
	public function testKeyMangling( $key, $comment ) {
		$matcher = StringMatcher::EmptyMatcher();
		$mangled = $matcher->mangle( $key );

		$title = Title::makeTitleSafe( NS_MEDIAWIKI, $mangled );
		$this->assertInstanceOf( 'Title', $title, "Key '$mangled' did not produce a valid title" );
		$unmangled = $matcher->unmangle( $mangled );
		$this->assertEquals( $key, $unmangled, 'Mangling is reversible' );
	}

	/**
	 * @dataProvider problematicMessageKeyProvider
	 */
	public function testKeyManglingWithPrefixing( $key, $comment ) {
		$matcher = new StringMatcher( 'prefix', array( '*' ) );
		$mangled = $matcher->mangle( $key );
		$title = Title::makeTitleSafe( NS_MEDIAWIKI, $mangled );
		$this->assertInstanceOf( 'Title', $title, "Key '$mangled' did not produce a valid title" );

		$unmangled = $matcher->unmangle( $mangled );
		$this->assertEquals( $key, $unmangled, 'Mangling is reversible' );
	}

	public function problematicMessageKeyProvider() {
		$keys = array(
			array( 'key', 'simple string' ),
			array( 'key[]', 'string with brackets' ),
			array( 'key%AB', 'string with invalid url encoding' ),
			array( 'key&amp;', 'string with html entity' ),
			array( 'key=2A', 'string with fake escaping' ),
			array( 'abcdefgh', 'string with fake escaping' ),
			array( 'общегосударственные', 'Unicode string' ),
		);

		// Add tests for ranges of exotic ASCII characters
		foreach ( range( 0, 7 ) as $k ) {
			$key = '';
			foreach ( range( 0, 15 ) as $c ) {
				$key .= chr( $c + 16 * $k );
			}

			$start = $k * 16;
			$end = $start + 16;
			$keys[] = array( $key, "ASCII range $start..$end" );
		}

		return $keys;
	}
}
