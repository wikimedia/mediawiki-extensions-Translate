<?php
declare( strict_types = 1 );

/**
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @covers \AndroidXmlFFS
 */
class AndroidXmlFFSTest extends MediaWikiIntegrationTestCase {
	private const DOCLANG = 'qqq';

	protected function setUp(): void {
		$this->setMwGlobals( 'wgTranslateDocumentationLanguageCode', self::DOCLANG );
	}

	protected $groupConfiguration = [
		'BASIC' => [
			'class' => FileBasedMessageGroup::class,
			'id' => 'test-id',
			'label' => 'Test Label',
			'namespace' => 'NS_MEDIAWIKI',
			'description' => 'Test description',
		],
		'FILES' => [
			'class' => AndroidXmlFFS::class,
			'sourcePattern' => '',
		],
	];

	public function testParsing() {
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
		$ffs = new AndroidXmlFFS( $group );
		$parsed = $ffs->readFromVariable( $file );
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

	public function testWrite() {
		/** @var FileBasedMessageGroup $group */
		$group = MessageGroupBase::factory( $this->groupConfiguration );
		$ffs = new AndroidXmlFFS( $group );

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

		$xml = $ffs->writeIntoVariable( $collection );
		$parsed = $ffs->readFromVariable( $xml );
		$expected = [
			'MESSAGES' => $messages,
			'AUTHORS' => $authors,
		];
		$this->assertEquals( $expected, $parsed );
	}

	public function testWriteDoc() {
		/** @var FileBasedMessageGroup $group */
		$group = MessageGroupBase::factory( $this->groupConfiguration );
		$ffs = new AndroidXmlFFS( $group );

		$messages = [
			'a' => 'b',
		];

		$collection = new MockMessageCollection( $messages, self::DOCLANG );

		$actual = $ffs->writeIntoVariable( $collection );
		$expected = <<<'XML'
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

	public function filter( $type, $condition = true, $value = null ) {
	}

	public function loadTranslations() {
	}
}
