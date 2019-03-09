<?php
/**
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 * @file
 */

 /**
  * @group medium
  */
class MessageGroupCacheTest extends MediaWikiTestCase {
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
		$list['pageid'] = [
			'group' => $pageMessageGroup,
			'row' 	=> (object)[
				'page_id' => 3
			]
		];

		$anotherPageMessageGroup = new WikiPageMessageGroup( 'anotherpageid', 'mypage' );
		$anotherPageMessageGroup->setLabel( 'thelabel' ); // Example
		$anotherPageMessageGroup->setNamespace( 5 ); // Example
		$list['anotherpageid'] = $anotherPageMessageGroup;

		return false;
	}

	public function testCacheDataProcessing() {
		$groups = MessageGroups::getAllGroups();

		$this->assertCount( 2, $groups, 'Only the two groups specified are in the cache.' );

		$this->assertTrue( $groups['pageid'] instanceof WikiPageMessageGroup,
			'both the objects in the array are of type WikiPageMessageGroup.' );
		$this->assertTrue( $groups['anotherpageid'] instanceof WikiPageMessageGroup,
			'both the objects in the array are of type WikiPageMessageGroup.' );

		$this->assertTrue( $groups['pageid']->getTitle() instanceof Title,
			'title property is an instance of the Title class.' );
		$this->assertTrue( $groups['anotherpageid']->getTitle() instanceof Title,
			'title property is an instance of the Title class.' );

		$this->assertEquals( $groups['pageid']->getTitle()->mArticleID, 3,
			'mArticleID property value matches page_id.' );
	}

	public function testDataSerialization() {
		$groups = MessageGroups::getAllGroups();
		$serialized = serialize( $groups['pageid'] );
		$pageMessageGroup = unserialize( $serialized );

		$this->assertEquals( $pageMessageGroup->getId(), 'pageid',
			'id is set' );
		$this->assertTrue( $pageMessageGroup->getTitle() instanceof Title,
			'title property is an instance of the Title class.' );
	}

}
