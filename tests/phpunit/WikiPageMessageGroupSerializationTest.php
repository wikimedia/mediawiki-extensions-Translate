<?php
/**
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 * @file
 */

 /**
  * @group medium
  */
class WikiPageMessageGroupSerializationTest extends MediaWikiTestCase {
	protected function setUp() {
		parent::setUp();

		global $wgHooks;
		$this->setMwGlobals( [
			'wgHooks' => $wgHooks
		] );
		$wgHooks['TranslatePostInitGroups'] = [ [ $this, 'getTestGroups' ] ];

		$mg = MessageGroups::singleton();
		$mg->setCache( new WANObjectCache( [ 'cache' => wfGetCache( 'hash' ) ] ) );
		$mg->recache();
	}

	public function getTestGroups( &$list ) {
		$pageMessageGroup = new WikiPageMessageGroup( 'pageid', 'mypage' );
		$pageMessageGroup->setLabel( 'thelabel' ); // Example
		$pageMessageGroup->setNamespace( 5 ); // Example
		$list['pageid'] = $pageMessageGroup;
		$pageMessageGroup->setIgnored( [ 'hello', 'world' ] );

		$anotherPageMessageGroup = new WikiPageMessageGroup( 'anotherpageid', 'mypage' );
		$anotherPageMessageGroup->setLabel( 'thelabel' ); // Example
		$anotherPageMessageGroup->setNamespace( 5 ); // Example

		$list['anotherpageid'] = $anotherPageMessageGroup;

		return false;
	}

	public function testDataSerialization() {
		$groups = MessageGroups::getAllGroups();
		$this->assertCount( 2, $groups['pageid']->getIgnored(),
			'before serialization ignored has 2 values.' );

		$serialized = serialize( $groups );
		$unserializedGroups = unserialize( $serialized );
		$this->assertCount( 2, $unserializedGroups,
			'before serialization ignored has 2 values.' );

		$pageMessageGroup = $unserializedGroups['pageid'];

		$this->assertEquals( $pageMessageGroup->getId(), 'pageid',
			'after serialization id is set' );
		$this->assertTrue( $pageMessageGroup->getTitle() instanceof Title,
			'after serialization title property is an instance of the Title class.' );
		$this->assertEquals( 5, $pageMessageGroup->getNamespace(),
			'after serialization namespace is not empty' );
		$this->assertEquals( 'thelabel', $pageMessageGroup->getLabel(),
			'after serialization label is not empty' );
		$this->assertCount( 0, $pageMessageGroup->getIgnored(),
			'after serialization ignored has 0 values.' );
	}

}
