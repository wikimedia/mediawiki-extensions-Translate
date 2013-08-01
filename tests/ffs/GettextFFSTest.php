<?php
/**
 * Tests for Gettext message file format.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2012-2013, Niklas Laxström
 * @license GPL-2.0+
 */

/**
 * @see GettextFFS
 */
class GettextFFSTest extends MediaWikiTestCase {
	protected $groupConfiguration;

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
				'class' => 'GettextFFS',
				'sourcePattern' => __DIR__ . '/../data/gettext.po',
			),
		);
	}

	/**
	 * @dataProvider provideMangling
	 */
	public function testMangling( $expected, $item, $algo ) {
		$this->assertEquals( $expected, GettextFFS::generateKeyFromItem( $item, $algo ) );
	}

	public static function provideMangling() {
		return array(
			array(
				'3f9999051ce0bc6e98f43224fe6ee1c220e34e49-Hello!_world_loooooooooooooooo',
				array( 'id' => 'Hello! world loooooooooooooooooooooooooooooooooooooooooong', 'ctxt' => 'baa' ),
				'legacy'
			),
			array(
				'3f9999-Hello!_world_loooooooooooooooo',
				array( 'id' => 'Hello! world loooooooooooooooooooooooooooooooooooooooooong', 'ctxt' => 'baa' ),
				'simple'
			),

			array(
				'1437e478b59e220640bf530f7e3bac93950eb8ae-"¤_=FJQ"_¤r_£_ab',
				array( 'id' => '"¤#=FJQ"<>¤r £}[]}%ab', 'ctxt' => false ),
				'legacy'
			),
			array(
				'1437e4-"¤#=FJQ"<>¤r_£}[]}%ab',
				array( 'id' => '"¤#=FJQ"<>¤r £}[]}%ab', 'ctxt' => false ),
				'simple'
			),

		);
	}

	public function testHashing() {
		$item1 = array(
			'id' => 'a',
			'str' => 'b',
			'ctxt' => false,
		);

		$item2 = array(
			'id' => 'a',
			'str' => 'b',
			'ctxt' => '',
		);

		$this->assertNotEquals(
			GettextFFS::generateKeyFromItem( $item1, 'legacy' ),
			GettextFFS::generateKeyFromItem( $item2, 'legacy' ),
			'Empty msgctxt is different from no msgctxt'
		);

		$this->assertNotEquals(
			GettextFFS::generateKeyFromItem( $item1, 'simple' ),
			GettextFFS::generateKeyFromItem( $item2, 'simple' ),
			'Empty msgctxt is different from no msgctxt'
		);

		$this->assertEquals(
			sha1( $item1['id'] ) . '-' . $item1['id'],
			GettextFFS::generateKeyFromItem( $item1, 'legacy' )
		);

		$this->assertEquals(
			substr( sha1( $item1['id'] ), 0, 6 ) . '-' . $item1['id'],
			GettextFFS::generateKeyFromItem( $item1, 'simple' )
		);
	}

	public function testMsgctxtExport() {
		/** @var FileBasedMessageGroup $group */
		$group = MessageGroupBase::factory( $this->groupConfiguration );
		$ffs = new GettextFFS( $group );

		$object = new ReflectionObject( $ffs );
		$method = $object->getMethod( 'formatMessageBlock' );
		$method->setAccessible( true );

		$key = 'key';
		$m = new FatMessage( 'key', 'definition' );
		$m->setTranslation( 'translation' );
		$trans = array();
		$pot = array();
		$pluralCount = 0;

		$results = <<<GETTEXT
#
msgid "definition"
msgstr "translation"

#
msgctxt ""
msgid "definition"
msgstr "translation"

#
msgctxt "context"
msgid "definition"
msgstr "translation"
GETTEXT;

		$results = preg_split( '/\n\n/', $results );

		// Case 1: no context
		$this->assertEquals(
			$results[0],
			trim( $method->invoke( $ffs, $key, $m, $trans, $pot, $pluralCount ) )
		);

		// Case 2: empty context
		$pot['ctxt'] = '';
		$this->assertEquals(
			$results[1],
			trim( $method->invoke( $ffs, $key, $m, $trans, $pot, $pluralCount ) )
		);

		// Case 3: context
		$pot['ctxt'] = 'context';
		$this->assertEquals(
			$results[2],
			trim( $method->invoke( $ffs, $key, $m, $trans, $pot, $pluralCount ) )
		);
	}
}
