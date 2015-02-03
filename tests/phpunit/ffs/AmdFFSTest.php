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
	public function testParsing( $messages, $file ) {
		/**
		 * @var FileBasedMessageGroup $group
		 */
		$group = MessageGroupBase::factory( $this->groupConfiguration );
		$ffs = new AmdFFS( $group );
		$parsed = $ffs->readFromVariable( $file );
		$expected = array(
			'MESSAGES' => $messages,
			'AUTHORS' => array(),
			'METADATA' => array(),
		);
		$this->assertEquals( $expected, $parsed );
	}

	public function amdProvider() {
		$values = array();

		$file1 =
			<<<AMD
			define({
	"one": "jeden",
	"two": "dwa",
	"three": "trzy"
});
AMD;

		$values[] = array(
			array(
				'one' => 'jeden',
				'two' => 'dwa',
				'three' => 'trzy',
			),
			$file1,
		);

		$file2 =
			<<<AMD
			define({
	"root": {
		"word": "слово"
		}
});
AMD;

		$values[] = array(
			array( 'word' => 'слово' ),
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
