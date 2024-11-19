<?php
declare( strict_types = 1 );

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * @license GPL-2.0-or-later
 * @covers \SubsetMessageGroup
 */
class SubsetMessageGroupTest extends MediaWikiUnitTestCase {
	public function testGetKeys() {
		$parentKeys = [ 'key1', 'key2', 'key3' ];
		$subsetKeys = [ 'key2' ];

		$parentGroup = $this->createMock( MessageGroup::class );
		$parentGroup->method( 'getKeys' )->willReturn( $parentKeys );

		$subsetGroup = $this->getSubsetGroup( $parentGroup, $subsetKeys );

		$this->assertEquals( $subsetKeys, $subsetGroup->getKeys() );
	}

	public function testGetKeysInvalid() {
		$parentKeys = [ 'key1', 'key2', 'key3' ];
		$subsetKeys = [ 'key2', 'key-unknown' ];
		$commonKeys = [ 'key2' ];
		$invalidKeys = [ 'key-unknown' ];

		$parentGroup = $this->createMock( MessageGroup::class );
		$parentGroup->method( 'getKeys' )->willReturn( $parentKeys );

		$logger = new TestLogger(
			true,
			static fn ( string $msg ): ?string => strpos( $msg, 'Invalid top messages' ) === 0 ? $msg : null,
			true
		);
		$subsetGroup = $this->getSubsetGroup( $parentGroup, $subsetKeys, $logger );

		$this->assertEquals( $commonKeys, $subsetGroup->getKeys() );
		$this->assertArrayEquals(
			[
				[ LogLevel::WARNING, 'Invalid top messages: {invalidMessages}', [ 'invalidMessages' => $invalidKeys ] ]
			],
			$logger->getBuffer()
		);
	}

	public function testGetTags() {
		$parentGroup = $this->createMock( MessageGroup::class );
		$parentGroup->method( 'getTags' )->willReturnCallback( static function ( $type = null ) {
			$tags = [];
			$tags['optional'] = [ 'optional-key' ];
			$tags['ignored'] = [ 'ignored-key' ];
			return $type ? $tags[$type] ?? [] : $tags;
		} );

		$subsetGroup = $this->getSubsetGroup( $parentGroup );

		$this->assertEquals( $parentGroup->getTags(), $subsetGroup->getTags() );
		$this->assertEquals( $parentGroup->getTags( 'optional' ), $subsetGroup->getTags( 'optional' ) );
	}

	public function testAggregateParentGroup() {
		$parentGroup = $this->getMockBuilder( AggregateMessageGroup::class )
			->onlyMethods( [ 'getGroups' ] )
			->disableOriginalConstructor()
			->getMock();

		$subsetGroup = $this->getSubsetGroup( $parentGroup );

		$parentGroup->method( 'getGroups' )->willReturn( [ $subsetGroup ] );

		// Just check we don't get into infinite loop
		$this->assertEquals( [], $subsetGroup->getKeys() );
		$this->assertEquals( [], $subsetGroup->getTags() );
	}

	/**
	 * @param MessageGroup $parentGroup A mock parent group that should be returned
	 *  by SubsetMessageGroup::getParentGroup
	 * @param string[] $subsetKeys Subset keys to be passed to the constructor
	 * @param LoggerInterface|null $logger Logger if log messages are expected.
	 *  If `null`, a default logger will be set, which causes test cases to fail
	 *  if anything is logged.
	 * @return SubsetMessageGroup
	 */
	private function getSubsetGroup(
		MessageGroup $parentGroup,
		array $subsetKeys = [],
		?LoggerInterface $logger = null
	): SubsetMessageGroup {
		$subsetGroup = $this->getMockBuilder( SubsetMessageGroup::class )
			->onlyMethods( [ 'getParentGroup', 'getLogger' ] )
			->setConstructorArgs( [ 'testGroupId', 'Test group', 'testParentGroupId', $subsetKeys ] )
			->getMock();

		$subsetGroup->method( 'getParentGroup' )->willReturn( $parentGroup );
		$subsetGroup->method( 'getLogger' )->willReturn( $logger ?? new TestLogger() );
		return $subsetGroup;
	}
}
