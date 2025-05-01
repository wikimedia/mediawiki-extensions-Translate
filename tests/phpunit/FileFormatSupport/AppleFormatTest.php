<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\FileFormatSupport;

use FileBasedMessageGroup;
use MediaWikiIntegrationTestCase;
use MessageGroupBase;

/**
 * The AppleFormat class is responsible for loading messages from .strings
 * files, which are used in many iOS and macOS projects.
 * These tests check that the message keys are loaded, mangled and unmangled
 * correctly.
 * @author Brion Vibber
 * @author Niklas Laxström
 * @covers \MediaWiki\Extension\Translate\FileFormatSupport\AppleFormat
 */
class AppleFormatTest extends MediaWikiIntegrationTestCase {

	private array $groupConfiguration = [
		'BASIC' => [
			'class' => FileBasedMessageGroup::class,
			'id' => 'test-id',
			'label' => 'Test Label',
			'namespace' => 'NS_MEDIAWIKI',
			'description' => 'Test description',
		],
		'FILES' => [
			'format' => 'Apple',
		],
	];

	public function testParsing(): void {
		$file =
			<<<'STRINGS'
			// aslkfjlkasdfjklfsj
			/* You are reading the ".strings" entry. */
			/* It's all for fun and fun for all.
			On two lines! */
			/* This is a
			  Multiline comment
				test */
			// Author: Testy McTesterson
			"website" = "<nowiki>http://en.wikipedia.org/</nowiki>";
			"language" = "English";
			// Add spaces to the key
			"key with spaces" = "Value that can be looked up with \"key with spaces\".";
			"key-with-{curlies}" = "Value that can be looked up with \"key-with-{curlies}\".";
			STRINGS;

		/** @var FileBasedMessageGroup $group */
		$group = MessageGroupBase::factory( $this->groupConfiguration );
		$appleFormat = new AppleFormat( $group );
		$parsed = $appleFormat->readFromVariable( $file );
		$expected = [
			'website' => '<nowiki>http://en.wikipedia.org/</nowiki>',
			'language' => 'English',
			'key with spaces' => 'Value that can be looked up with "key with spaces".',
			// We expect this one to be mangled for storage
			'key-with-=7Bcurlies=7D' => 'Value that can be looked up with "key-with-{curlies}".',
		];
		$authors = [
			'Testy McTesterson',
		];
		$expected = [ 'MESSAGES' => $expected, 'AUTHORS' => $authors ];
		$this->assertEquals( $expected, $parsed );
	}

	/** @dataProvider rowValuesProvider */
	public function testRowRoundtrip( string $key, string $value, string $comment ): void {
		/** @var FileBasedMessageGroup $group */
		$group = MessageGroupBase::factory( $this->groupConfiguration );
		$appleFormat = new AppleFormat( $group );
		$write = $appleFormat->writeRow( $key, $value );
		// Trim the trailing newline
		$write = rtrim( $write );
		[ $newkey, $newvalue ] = $appleFormat->readRow( $write );

		$this->assertSame( $key, $newkey, "Key survives roundtrip in testdata: $comment" );
		$this->assertSame( $value, $newvalue, "Value survives roundtrip in testdata: $comment" );
	}

	public function testFileRoundtrip(): void {
		$infile = file_get_contents( __DIR__ . '/../data/AppleFormatTest1.strings' );
		/** @var FileBasedMessageGroup $group */
		$group = MessageGroupBase::factory( $this->groupConfiguration );
		$appleFormat = new AppleFormat( $group );
		$parsed = $appleFormat->readFromVariable( $infile );

		$outfile = '';
		foreach ( $parsed['MESSAGES'] as $key => $value ) {
			$outfile .= $appleFormat->writeRow( $key, $value );
		}
		$reparsed = $appleFormat->readFromVariable( $outfile );

		$this->assertSame( $parsed['MESSAGES'], $reparsed['MESSAGES'],
			'Messages survive roundtrip through write and read' );
	}

	public static function rowValuesProvider(): array {
		return [
			[ 'key', 'value', 'simple row' ],
			[ 'key', 'value', 'row with different sep' ],
			[ 'key', 'val=ue', 'row with sep inside value' ],
			[ 'k=ey', 'value', 'row with sep inside key' ],
			[ '!key', 'value', 'row with ! at the beginning of key' ],
			[ 'k!ey', 'value', 'row with ! inside key' ],
			[ '#key', 'value', 'row with # at the beginning of key' ],
			[ 'k#ey', 'value', 'row with # inside key' ],
			[ 'k{ey}', 'value', 'row with { and } inside key' ],
			[ 'k\\tey', 'value\\', 'row with escapes' ],
			[ '01234', '13.34', 'row with numbers' ],
			[ '\\n\\tкая', 'кая', 'row with annoying characteres' ],
			[ '=', '', 'row with empty value' ],
			[ '#k   e\\=y#', '=v!\\=alue\\ \\\\', 'complex row' ],
			[ 'Key with "quotes"', 'Value "with quotes" also', 'row with double-quotes' ],
			[ 'Key with \\"quotes\\"', 'Value \\"with quotes\\" also',
				'row with double-quotes AND backslashes' ],
		];
	}
}
