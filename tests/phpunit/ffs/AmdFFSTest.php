<?php
/**
 * Tests for the AMD i18n message file format (used by require.js and Dojo).
 *
 * @file
 * @author Matthias Palmer
 * @copyright Copyright © 2011-2015, MetaSolutions AB
 * @license GPL-2.0-or-later
 */

/**
 * @see AmdFFS
 */
class AmdFFSTest extends MediaWikiTestCase {

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
				'class' => 'AmdFFS',
				'sourcePattern' => 'fake_reference_not_used_in_practise',
				'targetPattern' => 'fake_reference_not_used_in_practise',
			],
		];
	}

	protected $groupConfiguration;

	/**
	 * @dataProvider amdProvider
	 */
	public function testParsing( $messages, $authors, $file ) {
		/**
		 * @var FileBasedMessageGroup $group
		 */
		$group = MessageGroupBase::factory( $this->groupConfiguration );
		$ffs = new AmdFFS( $group );
		$parsed = $ffs->readFromVariable( $file );
		$expected = [
			'MESSAGES' => $messages,
			'AUTHORS' => $authors,
			'METADATA' => [],
		];
		$this->assertEquals( $parsed, $expected );
	}

	public function amdProvider() {
		$values = [];

		$file1 =
			<<<JS
define({
	"one": "jeden",
	"two": "dwa",
	"three": "trzy"
});
JS;

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
			<<<JS
/**
 * Translators:
 *  - Matthias
 *  - Hannes
 */
define({
   "root": {
      "word": "слово"
   }
});
JS;

		$values[] = [
			[ 'word' => 'слово' ],
			[ 'Matthias', 'Hannes' ],
			$file2,
		];

		return $values;
	}

	public function testExport() {
		$collection = new MockMessageCollectionForExport();
		/**
		 * @var FileBasedMessageGroup $group
		 */
		$group = MessageGroupBase::factory( $this->groupConfiguration );
		$ffs = new AmdFFS( $group );
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
	}
}
