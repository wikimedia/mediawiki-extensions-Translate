<?php
declare( strict_types = 1 );

/**
 * @license GPL-2.0-or-later
 * @covers SubsetMessageGroup
 */
class SubsetMessageGroupTest extends MediaWikiUnitTestCase {
	public function testGetKeys() {
		$parentKeys = [ 'key1', 'key2', 'key3' ];
		$subsetKeys = [ 'key2' ];

		$parentGroup = $this->createMock( MessageGroup::class );
		$parentGroup->method( 'getKeys' )->willReturn( $parentKeys );

		$subsetGroup = $this->getMockBuilder( SubsetMessageGroup::class )
			->onlyMethods( [ 'getParentGroup' ] )
			->setConstructorArgs( [ 'testGroupId', 'Test group', 'testParentGroupId', $subsetKeys ] )
			->getMock();
		$subsetGroup->method( 'getParentGroup' )->willReturn( $parentGroup );

		$this->assertEquals( $subsetKeys, $subsetGroup->getKeys() );
	}

	public function testGetKeysInvalid() {
		$parentKeys = [ 'key1', 'key2', 'key3' ];
		$subsetKeys = [ 'key2', 'key-unknown' ];
		$commonKeys = [ 'key2' ];

		$parentGroup = $this->createMock( MessageGroup::class );
		$parentGroup->method( 'getKeys' )->willReturn( $parentKeys );

		$subsetGroup = $this->getMockBuilder( SubsetMessageGroup::class )
			->onlyMethods( [ 'getParentGroup' ] )
			->setConstructorArgs( [ 'testGroupId', 'Test group', 'testParentGroupId', $subsetKeys ] )
			->getMock();
		$subsetGroup->method( 'getParentGroup' )->willReturn( $parentGroup );

		// Side effect of printing to error log, not possible to test?
		$this->assertEquals( $commonKeys, $subsetGroup->getKeys() );
	}

	public function testGetTags() {
		$parentGroup = $this->createMock( MessageGroup::class );
		$parentGroup->method( 'getTags' )->willReturnCallback( static function ( $type = null ) {
			$tags = [];
			$tags['optional'] = [ 'optional-key' ];
			$tags['ignored'] = [ 'ignored-key' ];
			return $type ? $tags[$type] ?? [] : $tags;
		} );

		$subsetGroup = $this->getMockBuilder( SubsetMessageGroup::class )
			->onlyMethods( [ 'getParentGroup' ] )
			->setConstructorArgs( [ 'testGroupId', 'Test group', 'testParentGroupId', [] ] )
			->getMock();
		$subsetGroup->method( 'getParentGroup' )->willReturn( $parentGroup );

		$this->assertEquals( $parentGroup->getTags(), $subsetGroup->getTags() );
		$this->assertEquals( $parentGroup->getTags( 'optional' ), $subsetGroup->getTags( 'optional' ) );
	}

	public function testAggregateParentGroup() {
		$subsetGroup = $this->getMockBuilder( SubsetMessageGroup::class )
			->onlyMethods( [ 'getParentGroup' ] )
			->setConstructorArgs( [ 'testGroupId', 'Test group', 'testParentGroupId', [] ] )
			->getMock();

		$parentGroup = $this->getMockBuilder( AggregateMessageGroup::class )
			->onlyMethods( [ 'getGroups' ] )
			->disableOriginalConstructor()
			->getMock();

		$subsetGroup->method( 'getParentGroup' )->willReturn( $parentGroup );
		$parentGroup->method( 'getGroups' )->willReturn( [ $subsetGroup ] );

		// Just check we don't get into infinite loop
		$this->assertEquals( [], $subsetGroup->getKeys() );
		$this->assertEquals( [], $subsetGroup->getTags() );
	}
}
