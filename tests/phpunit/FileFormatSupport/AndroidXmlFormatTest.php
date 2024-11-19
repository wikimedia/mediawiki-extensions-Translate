<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\FileFormatSupport;

use FileBasedMessageGroup;
use MediaWiki\Extension\Translate\MessageLoading\FatMessage;
use MediaWiki\Extension\Translate\MessageLoading\MessageCollection;
use MediaWikiIntegrationTestCase;
use MessageGroupBase;

/**
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @covers \MediaWiki\Extension\Translate\FileFormatSupport\AndroidXmlFormat
 */
class AndroidXmlFormatTest extends MediaWikiIntegrationTestCase {
	private const DOCLANG = 'qqq';

	protected function setUp(): void {
		$this->overrideConfigValue( 'TranslateDocumentationLanguageCode', self::DOCLANG );
	}

	private array $groupConfiguration = [
		'BASIC' => [
			'class' => FileBasedMessageGroup::class,
			'id' => 'test-id',
			'label' => 'Test Label',
			'namespace' => 'NS_MEDIAWIKI',
			'description' => 'Test description',
		],
		'FILES' => [
			'format' => 'AndroidXml',
			'sourcePattern' => '',
		],
	];

	public function testParsing(): void {
		$file =
			<<<'XML'
			<?xml version="1.0" encoding="utf-8"?>
			<!-- Authors:
			* Imaginary translator
			-->
			<resources>
				<string name="wpt_voicerec">Voice recording</string>
				<string name="wpt_stillimage" fuzzy="true">Picture</string>
				<plurals name="alot">
					<item quantity="one">bunny</item>
					<item quantity="other">bunnies</item>
				</plurals>
				<string name="has_quotes">Go to \"Wikipedia\"</string>
				<string name="starts_with_at">\@Wikipedia</string>
				<string name="has_ampersand">1&amp;nbsp;000</string>
				<string name="has_newline">first\nsecond</string>
				<string name="has_slashes">first \\ second</string>
				<string name="utf8_symbols">Hello World: \\u1234 \u1234 \\\u1234</string>
				<string name="quote_double_slash">Hello World: \' \\\'</string>
			</resources>
			XML;

		/** @var FileBasedMessageGroup $group */
		$group = MessageGroupBase::factory( $this->groupConfiguration );
		$androidFormat = new AndroidXmlFormat( $group );
		$parsed = $androidFormat->readFromVariable( $file );
		$expected = [
			'MESSAGES' => [
				'wpt_voicerec' => 'Voice recording',
				'wpt_stillimage' => '!!FUZZY!!Picture',
				'alot' => '{{PLURAL|one=bunny|bunnies}}',
				'has_quotes' => 'Go to "Wikipedia"',
				'starts_with_at' => '@Wikipedia',
				'has_ampersand' => '1&nbsp;000',
				'has_newline' => "first\nsecond",
				'has_slashes' => 'first \\ second',
				'utf8_symbols' => "Hello World: \u1234 ሴ \ሴ",
				'quote_double_slash' => 'Hello World: \' \\\''
			],
			'AUTHORS' => [
				'Imaginary translator',
			]
		];

		$this->assertEquals( $expected, $parsed );
	}

	public function testWrite(): void {
		/** @var FileBasedMessageGroup $group */
		$group = MessageGroupBase::factory( $this->groupConfiguration );
		$androidFormat = new AndroidXmlFormat( $group );

		$messages = [
			'ko=26ra' => 'wawe',
			'foobar' => '!!FUZZY!!Kissa kala <koira> "a\'b',
			'amuch' => '{{PLURAL|one=bunny|bunnies}}',
			'ampersand' => '&nbsp; &foo',
			'newlines' => "first\nsecond",
			'slashes' => 'has \\ slash'
		];
		$authors = [
			'1 Hyphen-Fan',
			'2 Hyphen--Lover',
			'3 Hyphen---Fanatic-',
		];

		$collection = new MockMessageCollection( $messages );
		$collection->addCollectionAuthors( $authors, 'set' );

		$xml = $androidFormat->writeIntoVariable( $collection );
		$parsed = $androidFormat->readFromVariable( $xml );
		$expected = [
			'MESSAGES' => $messages,
			'AUTHORS' => $authors,
		];
		$this->assertEquals( $expected, $parsed );
	}

	public function testWriteDoc(): void {
		/** @var FileBasedMessageGroup $group */
		$group = MessageGroupBase::factory( $this->groupConfiguration );

		$androidFormat = new AndroidXmlFormat( $group );

		$messages = [
			'a' => 'b',
		];

		$collection = new MockMessageCollection( $messages, self::DOCLANG );

		$actual = $androidFormat->writeIntoVariable( $collection );
		$expected =
			<<<'XML'
			<?xml version="1.0" encoding="utf-8"?>
			<resources xmlns:tools="http://schemas.android.com/tools" tools:ignore="all">
			  <string name="a">b</string>
			</resources>

			XML;
		$this->assertEquals( $expected, $actual );
	}
}

class MockMessageCollection extends MessageCollection {
	public function __construct( array $messages, string $code = 'en' ) {
		$this->code = $code;
		$keys = array_keys( $messages );
		$this->keys = array_combine( $keys, $keys );
		foreach ( $messages as $key => $value ) {
			$m = new FatMessage( $key, $value );
			$m->setTranslation( $value );
			if ( $key === 'foobar' ) {
				$m->addTag( 'fuzzy' );
			}
			$this->messages[$key] = $m;
		}
	}

	public function filter( string $type, bool $condition = true, $value = null ): void {
	}

	public function loadTranslations(): void {
	}
}
