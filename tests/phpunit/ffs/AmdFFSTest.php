<?php
/**
 * Tests for the AMD i18n message file format (used by require.js and Dojo).
 *
 * @file
 * @author Matthias Palmer
 * @copyright Copyright © 2011-2015, MetaSolutions AB
 * @license GPL-2.0+
 */

/**
 * @see AmdFFS
 */
class AmdFFSTest extends MediaWikiTestCase {

	public function setUp() {
		parent::setUp();
		$this->groupConfiguration = array(
			'BASIC' => array(
				'class' => 'FileBasedMessageGroup',
				'id' => 'test-id',
				'label' => 'Test Label',
				'namespace' => 'NS_MEDIAWIKI',
				'description' => 'Test description',
			),
			'FILES' => array(
				'class' => 'AmdFFS',
				'sourcePattern' => 'fake_reference_not_used_in_practise',
				'targetPattern' => 'fake_reference_not_used_in_practise',
			),
		);
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
		$expected = array(
			'MESSAGES' => $messages,
			'AUTHORS' =>  $authors,
			'METADATA' => array(),
		);
		$this->assertEquals( $parsed, $expected );
	}

	public function amdProvider() {
		$values = array();

		$file1 =
			<<<JS
define({
	"one": "jeden",
	"two": "dwa",
	"three": "trzy"
});
JS;

		$values[] = array(
			array(
				'one' => 'jeden',
				'two' => 'dwa',
				'three' => 'trzy',
			),
			array(),
			$file1,
		);

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

		$values[] = array(
			array( 'word' => 'слово' ),
			array( 'Matthias', 'Hannes' ),
			$file2,
		);

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
			array( 'Nike the bunny' ),
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
