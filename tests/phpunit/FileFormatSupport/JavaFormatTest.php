<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\FileFormatSupport;

use FileBasedMessageGroup;
use MediaWikiIntegrationTestCase;
use MessageGroupBase;

/**
 * The JavaFormat class is responsible for loading messages from .properties
 * files, which are used in many JavaScript and Java projects.
 * These tests check that the message keys are loaded, mangled and unmangled
 * correctly.
 *
 * @author Niklas Laxström
 * @covers \MediaWiki\Extension\Translate\FileFormatSupport\JavaFormat
 */
class JavaFormatTest extends MediaWikiIntegrationTestCase {

	private const GROUP_CONFIGURATION = [
		'BASIC' => [
			'class' => FileBasedMessageGroup::class,
			'id' => 'test-id',
			'label' => 'Test Label',
			'namespace' => 'NS_MEDIAWIKI',
			'description' => 'Test description',
		],
		'FILES' => [
			'format' => 'Java',
		],
	];

	public function testParsing(): void {
		$file =
			<<<'PROPERTIES'
			# You are reading the ".properties" entry.
			! The exclamation mark can also mark text as comments.
			website = <nowiki>http://en.wikipedia.org/</nowiki>
			language = English
			# The backslash below tells the application to continue reading
			# the value onto the next line.
			message = Welcome to \
			          Wikipedia!
			# Add spaces to the key
			key\ with\ spaces = Value that can be looked up with "key with spaces".
			key-with-{curlies} = Value that can be looked up with "key-with-{curlies}".
			PROPERTIES;

		/** @var FileBasedMessageGroup $group */
		$group = MessageGroupBase::factory( self::GROUP_CONFIGURATION );
		$javaFormat = new JavaFormat( $group );
		$parsed = $javaFormat->readFromVariable( $file );
		$expected = [
			'website' => '<nowiki>http://en.wikipedia.org/</nowiki>',
			'language' => 'English',
			'message' => 'Welcome to Wikipedia!',
			'key with spaces' => 'Value that can be looked up with "key with spaces".',
			// We expect this one to be mangled for storage
			'key-with-=7Bcurlies=7D' => 'Value that can be looked up with "key-with-{curlies}".',
		];
		$expected = [ 'MESSAGES' => $expected, 'AUTHORS' => [] ];
		$this->assertEquals( $expected, $parsed );
	}

	/** @dataProvider rowValuesProvider */
	public function testRowRoundtrip( string $key, string $sep, string $value, string $comment ): void {
		$groupConfiguration = self::GROUP_CONFIGURATION;
		$groupConfiguration['FILES']['keySeparator'] = $sep;
		$group = MessageGroupBase::factory( $groupConfiguration );

		$javaFormat = new JavaFormat( $group );
		$write = $javaFormat->writeRow( $key, $value );
		// Trim the trailing newline
		$write = rtrim( $write );
		[ $newkey, $newvalue ] = $javaFormat->readRow( $write, $sep );

		$this->assertSame( $key, $newkey, "Key survives roundtrip in testdata: $comment" );
		$this->assertSame( $value, $newvalue, "Value survives roundtrip in testdata: $comment" );
	}

	public static function rowValuesProvider(): array {
		return [
			[ 'key', '=', 'value', 'simple row' ],
			[ 'key', ':', 'value', 'row with different sep' ],
			[ 'key', '=', 'val=ue', 'row with sep inside value' ],
			[ 'k=ey', '=', 'value', 'row with sep inside key' ],
			[ '!key', '=', 'value', 'row with ! at the beginning of key' ],
			[ 'k!ey', '=', 'value', 'row with ! inside key' ],
			[ '#key', '=', 'value', 'row with # at the beginning of key' ],
			[ 'k#ey', '=', 'value', 'row with # inside key' ],
			[ 'k{ey}', '=', 'value', 'row with { and } inside key' ],
			[ 'k\\tey', '=', 'value\\', 'row with escapes' ],
			[ '01234', '=', '13.34', 'row with numbers' ],
			[ '\\n\\tкая', '=', 'кая', 'row with annoying characteres' ],
			[ '=', '=', '', 'row with empty value' ],
			[ '#k   e\\=y#', '=', '=v!\\=alue\\ \\\\', 'complex row' ],
		];
	}
}
