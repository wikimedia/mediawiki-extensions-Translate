<?php

namespace MediaWiki\Extension\Translate\MessageProcessing;

use MediaWikiIntegrationTestCase;
use Title;

/**
 * The StringMatcher class is responsible for making sure message keys
 * from external sources are valid titles in MediaWiki.
 * @author Niklas Laxström
 * @file
 */

class StringMatcherTest extends MediaWikiIntegrationTestCase {
	/** @dataProvider messageKeyProvider */
	public function testKeyPrefixing(
		string $key, string $expected, string $prefix, array $rules
	): void {
		$matcher = new StringMatcher( $prefix, $rules );
		$mangled = $matcher->mangle( $key );
		$title = Title::makeTitleSafe( NS_MEDIAWIKI, $mangled );
		$this->assertInstanceOf( 'Title', $title, "Key '$mangled' did not produce valid title" );
		$unmangled = $matcher->unmangle( $mangled );
		$this->assertEquals( $key, $unmangled, 'Mangling is reversible' );
		$this->assertEquals( $expected, $mangled, 'Message is prefixed correctly' );
	}

	public function messageKeyProvider(): array {
		// The fourth parameter causes the key to be prefixed or unprefixed
		$keys = [
			[ 'key', 'p-key', 'p-', [ 'key' ], 'Exact match' ],
			[ 'key', 'key', 'p-', [ 'bar' ], 'Exact not match' ],
			[ 'key', 'p-key', 'p-', [ 'k*' ], 'Prefix match' ],
			[ 'key', 'key', 'p-', [ 'b*' ], 'Prefix not match' ],
			[ 'key', 'p-key', 'p-', [ '*y' ], 'Suffix match' ],
			[ 'key', 'key', 'p-', [ '*r' ], 'Suffix not match' ],
			[ 'key', 'p-key', 'p-', [ 'k*y' ], 'Wildcard match' ],
			[ 'key', 'key', 'p-', [ '*a*' ], 'Wildcard not match' ],
			[ 'key', 'p-key', 'p-', [ 'key', '*ey', 'ke*' ], 'Multiple rules match' ],
			[ 'key', 'key', 'p-', [ '*a*', '*ar', 'ba*' ], 'Multiple rules not match' ],
			[ 'key', 'p-key', 'p-', [ '*' ], 'All match' ],
			[
				'[k.ssa]', 'p-=5Bk.ssa=5D', 'p-', [ '[k.s*' ],
				'Message key with special chars'
			],
			[
				'[kissa]', '=5Bkissa=5D', 'p-', [ '[k.s*' ],
				'Message key with special chars'
			],
			[ 'keyblah/i', 'p-keyblah/i', 'p-', [ 'key*/i' ], 'Slash in pattern does not trigger modifier' ],
			[
				'p-key', 'p-key', 'p-', [ 'b-*' ],
				'Unmangle does not remove prefix if pattern doesn\'t match'
			]
		];

		return $keys;
	}

	/** @dataProvider problematicMessageKeyProvider */
	public function testKeyMangling( string $key ): void {
		$matcher = new StringMatcher();
		$mangled = $matcher->mangle( $key );
		$title = Title::makeTitleSafe( NS_MEDIAWIKI, $mangled );
		$this->assertInstanceOf( 'Title', $title, "Key '$mangled' did not produce a valid title" );

		$unmangled = $matcher->unmangle( $mangled );
		$this->assertEquals( $key, $unmangled, 'Mangling is reversible' );
	}

	/** @dataProvider problematicMessageKeyProvider */
	public function testKeyManglingWithPrefixing( string $key ): void {
		$matcher = new StringMatcher( 'prefix', [ '*' ] );
		$mangled = $matcher->mangle( $key );
		$title = Title::makeTitleSafe( NS_MEDIAWIKI, $mangled );
		$this->assertInstanceOf( 'Title', $title, "Key '$mangled' did not produce a valid title" );

		$unmangled = $matcher->unmangle( $mangled );
		$this->assertEquals( $key, $unmangled, 'Mangling is reversible' );
	}

	public function problematicMessageKeyProvider(): array {
		$keys = [
			[ 'key', 'simple string' ],
			[ 'key[]', 'string with brackets' ],
			[ 'key%AB', 'string with invalid url encoding' ],
			[ 'key&amp;', 'string with html entity' ],
			[ 'key=2A', 'string with fake escaping' ],
			[ 'общегосударственные', 'Unicode string' ],
			[ ' la la land_', 'string starting or ending with spaces or underscores' ],
			[ 'one  two__three _four', 'multiple spaces consisting of spaces or underscores' ],
			[ 'Signed by ~~~', 'Magic tilde expansion' ],
			[ ':iam', 'string starting with a colon' ],
		];

		// Add tests for ranges of exotic ASCII characters
		foreach ( range( 0, 7 ) as $k ) {
			$key = '';
			foreach ( range( 0, 15 ) as $c ) {
				$key .= chr( $c + 16 * $k );
			}

			$start = $k * 16;
			$end = $start + 16;
			$keys[] = [ $key, "ASCII range $start..$end" ];
		}

		return $keys;
	}
}
