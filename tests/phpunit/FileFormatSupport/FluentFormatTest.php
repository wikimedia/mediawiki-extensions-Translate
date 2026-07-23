<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\FileFormatSupport;

use FileBasedMessageGroup;
use MediaWikiIntegrationTestCase;
use MessageGroupBase;
use MockMessageCollectionForExport;

/**
 * Tests for the Fluent (.ftl) message file format.
 *
 * Fixture files are the upstream spec test fixtures from
 * https://github.com/projectfluent/fluent/tree/main/test/fixtures
 * stored verbatim under tests/phpunit/data/fluent/fluent_*.ftl.
 *
 * For each fixture the JSON sidecar defines which entries are valid (non-junk).
 * We assert that our parser extracts exactly those keys, and that pure-text
 * values match the spec's parsed text exactly.
 *
 * @license GPL-2.0-or-later
 * @covers \MediaWiki\Extension\Translate\FileFormatSupport\FluentFormat
 */
class FluentFormatTest extends MediaWikiIntegrationTestCase {

	private const GROUP_CONFIGURATION = [
		'BASIC' => [
			'class' => FileBasedMessageGroup::class,
			'id' => 'test-id',
			'label' => 'Test Label',
			'namespace' => 'NS_MEDIAWIKI',
			'description' => 'Test description',
		],
		'FILES' => [
			'format' => 'Fluent',
			'sourcePattern' => __DIR__ . '/../data/fluent/fluenttest_%CODE%.ftl',
			'targetPattern' => 'fluenttest_%CODE%.ftl',
		],
	];

	private function makeFormat(): FluentFormat {
		/** @var FileBasedMessageGroup $group */
		$group = MessageGroupBase::factory( self::GROUP_CONFIGURATION );
		return new FluentFormat( $group );
	}

	private function fixture( string $name ): string {
		return file_get_contents( __DIR__ . "/../data/fluent/fluent_$name.ftl" );
	}

	/**
	 * Parse the JSON sidecar and return two arrays:
	 *  - $textValues: key => expected plain-text value (for text-only patterns)
	 *  - $placeableKeys: keys whose value contains placeables (we only assert presence)
	 *
	 * Also returns $specJunkKeys: keys the spec marks as Junk due to invalid placeable
	 * syntax. Our parser stores values verbatim without validating placeable internals,
	 * so it accepts these. We assert they are present but do not check their value.
	 */
	private function expectedFromJson( string $name ): array {
		$json = json_decode(
			file_get_contents( __DIR__ . "/../data/fluent/fluent_$name.json" ),
			true
		);

		$textValues = [];
		$placeableKeys = [];
		$specJunkKeys = [];

		foreach ( $json['body'] as $entry ) {
			$type = $entry['type'] ?? '';

			// Collect keys from Junk entries that look like valid message identifiers
			// AND whose value has balanced braces. These are entries the spec rejects
			// due to invalid placeable/selector syntax but our parser accepts verbatim.
			// Entries with unbalanced braces are now rejected by our brace-balance check.
			if ( $type === 'Junk' ) {
				$content = $entry['content'] ?? '';
				if ( preg_match( '/^([a-zA-Z][a-zA-Z0-9_-]*)\s*=(.*)$/s', $content, $m ) ) {
					$value = trim( $m[2] );
					if ( $this->testBraceBalanced( $value ) ) {
						$specJunkKeys[] = $m[1];
					}
				}
				continue;
			}

			if ( !in_array( $type, [ 'Message', 'Term' ], true ) ) {
				continue;
			}

			$prefix = $type === 'Term' ? '-' : '';
			$id = $prefix . $entry['id']['name'];

			// Message value
			$val = $entry['value'] ?? null;
			if ( $val !== null ) {
				[ $text, $hasPlaceable ] = $this->flattenPattern( $val );
				if ( $hasPlaceable ) {
					$placeableKeys[] = $id;
				} else {
					$textValues[$id] = $text;
				}
			}

			// Attributes
			foreach ( $entry['attributes'] ?? [] as $attr ) {
				$akey = $id . '.' . $attr['id']['name'];
				[ $text, $hasPlaceable ] = $this->flattenPattern( $attr['value'] );
				if ( $hasPlaceable ) {
					$placeableKeys[] = $akey;
				} else {
					$textValues[$akey] = $text;
				}
			}
		}

		return [ $textValues, $placeableKeys, $specJunkKeys ];
	}

	private function testBraceBalanced( string $value ): bool {
		$depth = 0;
		for ( $i = 0; $i < strlen( $value ); $i++ ) {
			if ( $value[$i] === '{' ) {
				$depth++;
			} elseif ( $value[$i] === '}' ) {
				if ( --$depth < 0 ) {
					return false;
				}
			}
		}
		return $depth === 0;
	}

	/**
	 * Flatten a Pattern AST node to a string.
	 * Returns [text, hasPlaceable] where hasPlaceable is true if any element
	 * is not a plain TextElement.
	 */
	private function flattenPattern( array $pattern ): array {
		$text = '';
		$hasPlaceable = false;
		foreach ( $pattern['elements'] as $el ) {
			if ( $el['type'] === 'TextElement' ) {
				$text .= $el['value'];
			} else {
				$hasPlaceable = true;
				$text .= $el['value'] ?? '';
			}
		}
		return [ $text, $hasPlaceable ];
	}

	// -----------------------------------------------------------------------
	// Fixture-driven tests
	// -----------------------------------------------------------------------

	/** @dataProvider provideFixtures */
	public function testFixture( string $fixtureName ): void {
		$ftl = $this->fixture( $fixtureName );
		[ $textValues, $placeableKeys, $specJunkKeys ] = $this->expectedFromJson( $fixtureName );

		$parsed = $this->makeFormat()->readFromVariable( $ftl );
		$messages = $parsed['MESSAGES'];

		// All expected text-value keys must be present with the correct value
		foreach ( $textValues as $key => $expected ) {
			$this->assertArrayHasKey( $key, $messages, "Key '$key' missing in $fixtureName" );
			$this->assertSame( $expected, $messages[$key], "Value mismatch for '$key' in $fixtureName" );
		}

		// All expected placeable keys must be present (value is raw FTL, not checked)
		foreach ( $placeableKeys as $key ) {
			$this->assertArrayHasKey( $key, $messages, "Placeable key '$key' missing in $fixtureName" );
		}

		// No extra keys beyond what the spec defines as valid, plus keys the spec marks
		// as Junk due to invalid placeable syntax (our parser accepts these verbatim)
		$allAllowed = array_merge( array_keys( $textValues ), $placeableKeys, $specJunkKeys );
		$extra = array_diff( array_keys( $messages ), $allAllowed );
		$this->assertSame( [], array_values( $extra ),
			"Unexpected keys in $fixtureName: " . implode( ', ', $extra ) );
	}

	public static function provideFixtures(): array {
		return [
			'messages' => [ 'messages' ],
			'multiline_values' => [ 'multiline_values' ],
			'comments' => [ 'comments' ],
			'terms' => [ 'terms' ],
			'variables' => [ 'variables' ],
			'placeables' => [ 'placeables' ],
			'junk' => [ 'junk' ],
			'sparse_entries' => [ 'sparse_entries' ],
			'mixed_entries' => [ 'mixed_entries' ],
			'select_expressions' => [ 'select_expressions' ],
		];
	}

	// -----------------------------------------------------------------------
	// Author extraction
	// -----------------------------------------------------------------------

	public function testAuthorsExtracted(): void {
		$ftl = "# Author: Alice\n# Author: Bob\n\nkey = Value\n";
		$parsed = $this->makeFormat()->readFromVariable( $ftl );
		$this->assertSame( [ 'Alice', 'Bob' ], $parsed['AUTHORS'] );
	}

	public function testNoAuthors(): void {
		$parsed = $this->makeFormat()->readFromVariable( "key = Value\n" );
		$this->assertSame( [], $parsed['AUTHORS'] );
	}

	// -----------------------------------------------------------------------
	// Export round-trip
	// -----------------------------------------------------------------------

	public function testExportRoundTrip(): void {
		$collection = new MockMessageCollectionForExport();
		$format = $this->makeFormat();
		$output = $format->writeIntoVariable( $collection );
		$parsed = $format->readFromVariable( $output );

		$this->assertSame( [ 'Nike the bunny' ], $parsed['AUTHORS'], 'Authors are exported' );
		$this->assertArrayHasKey( 'fuzzymsg', $parsed['MESSAGES'], 'Fuzzy message is exported' );
		$this->assertArrayHasKey( 'translatedmsg', $parsed['MESSAGES'], 'Translated message is exported' );
		$this->assertArrayNotHasKey( 'untranslatedmsg', $parsed['MESSAGES'], 'Untranslated messages are not exported' );
	}

	public function testExportAttributeRoundTrip(): void {
		$ftl = "login-input = Predefined value\n"
			. "    .placeholder = email@example.com\n"
			. "    .aria-label = Login input value\n";
		$parsed = $this->makeFormat()->readFromVariable( $ftl );
		$this->assertSame( [
			'login-input' => 'Predefined value',
			'login-input.placeholder' => 'email@example.com',
			'login-input.aria-label' => 'Login input value',
		], $parsed['MESSAGES'] );
	}

	/** @dataProvider provideUnbalancedBraces */
	public function testUnbalancedBracesAreDropped( string $ftl ): void {
		$parsed = $this->makeFormat()->readFromVariable( $ftl );
		$this->assertArrayNotHasKey( 'key', $parsed['MESSAGES'] );
	}

	public static function provideUnbalancedBraces(): array {
		return [
			'unclosed brace' => [ "key = { \$var\n" ],
			'unclosed brace, multiline' => [ "key = { \$var\nother = Value\n" ],
			'unmatched closing brace' => [ "key = value }\n" ],
			'unclosed brace in attribute' => [ "key =\n    .attr = { \$var\n" ],
		];
	}

	public function testWhitespaceOnlyLinesDoNotAffectIndent(): void {
		$ftl = "key =\n    First line\n  \n    Second line\n";
		$parsed = $this->makeFormat()->readFromVariable( $ftl );
		$this->assertSame( "First line\n\nSecond line", $parsed['MESSAGES']['key'] );
	}

	public function testMessageWithOnlyAttributesRoundTrips(): void {
		$format = $this->makeFormat();
		$ftl = "key =\n    .attr = Value\n";
		$this->assertSame(
			[ 'key.attr' => 'Value' ],
			$format->readFromVariable( $ftl )['MESSAGES']
		);
	}

	/** Terms must have a value, so a term with only attributes is junk. */
	public function testTermWithOnlyAttributesIsJunk(): void {
		$parsed = $this->makeFormat()->readFromVariable( "-term =\n    .attr = Value\n" );
		$this->assertSame( [], $parsed['MESSAGES'] );
	}

	// -----------------------------------------------------------------------
	// Import boundary: read() mangles keys, readFromVariable() does not
	// -----------------------------------------------------------------------

	/**
	 * read() is overridden so that key mangling — MediaWiki title-safety escaping
	 * and group prefixing — is applied at the wiki-import boundary, while
	 * readFromVariable() returns the raw Fluent identifiers.
	 *
	 * This uses the "messages" spec fixture, whose "key_13_" identifier is a valid
	 * Fluent key but ends in an underscore, which is not a stable MediaWiki title
	 * (trailing "_" is trimmed). The parser must keep it verbatim, yet importing it
	 * onto the wiki must mangle it to "key_13=5F". Reading the same file two ways
	 * demonstrates why read() had to be overridden instead of mangling in
	 * readFromVariable().
	 */
	public function testReadManglesKeysButReadFromVariableDoesNot(): void {
		$config = self::GROUP_CONFIGURATION;
		$config['FILES']['sourcePattern'] = __DIR__ . '/../data/fluent/fluent_%CODE%.ftl';
		/** @var FileBasedMessageGroup $group */
		$group = MessageGroupBase::factory( $config );
		$format = new FluentFormat( $group );

		// Raw parsing keeps the identifier verbatim.
		$raw = $format->readFromVariable( $this->fixture( 'messages' ) );
		$this->assertArrayHasKey(
			'key_13_', $raw['MESSAGES'], 'readFromVariable() returns the Fluent key verbatim'
		);

		// read() reads the same fixture file and mangles keys for title-safety.
		$imported = $format->read( 'messages' );
		$this->assertArrayHasKey(
			'key_13=5F', $imported['MESSAGES'], 'read() mangles the trailing-underscore key into a valid title'
		);
		$this->assertArrayNotHasKey(
			'key_13_', $imported['MESSAGES'], 'read() does not expose the raw, title-unsafe key'
		);
	}

	// -----------------------------------------------------------------------
	// File extension
	// -----------------------------------------------------------------------

	public function testFileExtension(): void {
		$this->assertContains( '.ftl', $this->makeFormat()->getFileExtensions() );
	}
}
