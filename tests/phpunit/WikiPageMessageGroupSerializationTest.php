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

		$this->setTemporaryHook(
			'TranslatePostInitGroups',
			function ( &$list ) {
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
		);

		$mg = MessageGroups::singleton();
		$mg->setCache( new WANObjectCache( [ 'cache' => wfGetCache( 'hash' ) ] ) );
		$mg->recache();
	}

	public function testDataSerialization() {
		$groups = MessageGroups::getAllGroups();
		$groupCount = count( $groups );

		$serialized = serialize( $groups );
		$unserializedGroups = unserialize( $serialized );

		$this->assertCount( $groupCount, $unserializedGroups,
			'after serialization the number of groups matches the count before serialization.' );

		$pageMessageGroup = $unserializedGroups['pageid'];
		$this->assertEquals( $pageMessageGroup->getId(), 'pageid',
			'after serialization id is set' );
		$this->assertInstanceOf( Title::class, $pageMessageGroup->getTitle(),
			'after serialization title property is an instance of the Title class.' );
		$this->assertEquals( 5, $pageMessageGroup->getNamespace(),
			'after serialization namespace is not empty' );
		$this->assertEquals( $pageMessageGroup->getTitle()->getPrefixedText(),
			$pageMessageGroup->getLabel(), 'after serialization label is not empty' );
		$this->assertCount( 2, $pageMessageGroup->getIgnored(),
			'after serialization ignored has 2 values.' );
	}

}
