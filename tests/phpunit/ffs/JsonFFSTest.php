<?php
/**
 * Tests for JSON message file format.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2012-2013, Niklas Laxström
 * @license GPL-2.0-or-later
 */

/**
 * @see JsonFFS
 */
class JsonFFSTest extends MediaWikiTestCase {

	public function setUp() {
		parent::setUp();
		$this->groupConfiguration = [
			'BASIC' => [
				'class' => 'FileBasedMessageGroup',
				'id' => 'test-id',
				'label' => 'Test Label',
				'namespace' => 'NS_MEDIAWIKI',
				'description' => 'Test description',
			],
			'FILES' => [
				'class' => 'JsonFFS',
				'sourcePattern' => __DIR__ . '/../data/jsontest_%CODE%.json',
				'targetPattern' => 'jsontest_%CODE%.json',
			],
		];
	}

	protected $groupConfiguration;

	/**
	 * @dataProvider jsonProvider
	 */
	public function testParsing( $messages, $authors, $file ) {
		/**
		 * @var FileBasedMessageGroup $group
		 */
		$group = MessageGroupBase::factory( $this->groupConfiguration );
		$ffs = new JsonFFS( $group );
		$parsed = $ffs->readFromVariable( $file );
		$expected = [
			'MESSAGES' => $messages,
			'AUTHORS' => $authors,
			'METADATA' => [],
		];
		$this->assertEquals( $expected, $parsed );

		if ( $messages === [] ) {
			$this->assertFalse( JsonFFS::isValid( $file ) );
		} else {
			$this->assertTrue( JsonFFS::isValid( $file ) );
		}
	}

	public function jsonProvider() {
		$values = [];

		$file1 =
			<<<JSON
			{
	"one": "jeden",
	"two": "dwa",
	"three": "trzy"
}
JSON;

		$values[] = [
			[
				'one' => 'jeden',
				'two' => 'dwa',
				'three' => 'trzy',
			],
			[],
			$file1,
		];

		$file2 =
			<<<JSON
			{
	"@metadata": {
		"authors": ["Niklas", "Amir"]
	},
	"word": "слово"
}
JSON;

		$values[] = [
			[ 'word' => 'слово' ],
			[ 'Niklas', 'Amir' ],
			$file2,
		];

		$file3 =
			<<<JSON
			<This is not
Json!>@£0 file
JSON;

		$values[] = [
			[],
			[],
			$file3,
		];

		return $values;
	}

	public function testExport() {
		$collection = new MockMessageCollectionForExport();
		/**
		 * @var FileBasedMessageGroup $group
		 */
		$group = MessageGroupBase::factory( $this->groupConfiguration );
		$ffs = new JsonFFS( $group );
		$data = $ffs->writeIntoVariable( $collection );
		$parsed = $ffs->readFromVariable( $data );

		$this->assertEquals(
			[ 'Nike the bunny' ],
			$parsed['AUTHORS'],
			'Authors are exported'
		);
		$this->assertArrayHasKey( 'fuzzymsg', $parsed['MESSAGES'], 'fuzzy message is exported' );
		$this->assertArrayHasKey(
			'translatedmsg',
			$parsed['MESSAGES'],
			'translated message is exported'
		);
		if ( array_key_exists( 'untranslatedmsg', $parsed['MESSAGES'] ) ) {
			$this->fail( 'Untranslated messages should not be exported' );
		}

		$this->assertEquals(
			'metavalue',
			$parsed['METADATA']['metakey'],
			'metadata is preserved'
		);
	}
}
