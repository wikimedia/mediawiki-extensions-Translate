<?php
/**
 * Tests for AndroidXmlFFS
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */

class AndroidXmlFFSTest extends MediaWikiTestCase {

	protected $groupConfiguration = [
		'BASIC' => [
			'class' => 'FileBasedMessageGroup',
			'id' => 'test-id',
			'label' => 'Test Label',
			'namespace' => 'NS_MEDIAWIKI',
			'description' => 'Test description',
		],
		'FILES' => [
			'class' => 'AndroidXmlFFS',
			'sourcePattern' => '',
		],
	];

	public function testParsing() {
		$file =
<<<XML
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
</resources>
XML;

		/**
		 * @var FileBasedMessageGroup $group
		 */
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
			],
			'AUTHORS' => [
				'Imaginary translator',
			]
		];

		$this->assertEquals( $expected, $parsed );
	}

	public function testWrite() {
		/**
		 * @var FileBasedMessageGroup $group
		 */
		$group = MessageGroupBase::factory( $this->groupConfiguration );
		$ffs = new AndroidXmlFFS( $group );

		$messages = [
			'ko=26ra' => 'wawe',
			'foobar' => '!!FUZZY!!Kissa kala <koira> "a\'b',
			'amuch' => '{{PLURAL|one=bunny|bunnies}}',
			'ampersand' => '&nbsp; &foo',
			'newlines' => "first\nsecond",
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
}

class MockMessageCollection extends MessageCollection {
	public function __construct( $messages ) {
		$keys = array_keys( $messages );
		$this->keys = array_combine( $keys, $keys );
		foreach ( $messages as $key => $value ) {
			$m = new FatMessage( $key, $value );
			$m->setTranslation( $value );
			$this->messages[$key] = $m;
		}

		$this->messages['foobar']->addTag( 'fuzzy' );
	}

	public function filter( $type, $condition = true, $value = null ) {
	}

	public function loadTranslations() {
	}
}
