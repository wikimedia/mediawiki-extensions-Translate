<?php

namespace MediaWiki\Extensions\Translate\Synchronization;

use HashBagOStuff;
use MediaWikiUnitTestCase;

/**
 * @covers \MediaWiki\Extensions\Translate\Synchronization\GroupSynchronizationCache
 * @covers \MediaWiki\Extensions\Translate\Synchronization\GroupSynchronizationResponse
 */
class GroupSynchronizationCacheTest extends MediaWikiUnitTestCase {
	/** @var GroupSynchronizationCache */
	private $groupSyncCache;

	protected function setUp(): void {
		$this->groupSyncCache = $this->getGroupSynchronizationCache();
	}

	public function testIsGroupBeingProcessed() {
		$groupId = 'hello';
		$this->groupSyncCache->startSync( $groupId );

		$this->assertTrue( $this->groupSyncCache->isGroupBeingProcessed( $groupId ) );

		$this->groupSyncCache->endSync( $groupId );

		$this->assertFalse( $this->groupSyncCache->isGroupBeingProcessed( $groupId ) );
	}

	public function testGetGroupMessageKeys() {
		$groupId = 'hello';
		$title = 'Title';

		$this->groupSyncCache->startSync( $groupId );
		$this->groupSyncCache->addMessages(
			$groupId,
			$this->getMessageParam( $groupId, $title )
		);

		$this->assertEquals(
			[ $title ], $this->groupSyncCache->getGroupMessageKeys( $groupId )
		);

		$this->groupSyncCache->removeMessages( $title );

		$this->assertEquals(
			[ $title ],
			$this->groupSyncCache->getGroupMessageKeys( $groupId ),
			'Removing a message does not update the group message list.'
		);
	}

	public function testGetGroupsInSync() {
		$groupId = 'hello';

		$this->groupSyncCache->startSync( $groupId );
		$this->assertEquals( [ $groupId ], $this->groupSyncCache->getGroupsInSync() );

		$this->groupSyncCache->endSync( $groupId );
		$this->assertEquals( [], $this->groupSyncCache->getGroupsInSync() );
	}

	public function testEndSync() {
		$groupId = 'group-id';
		$title = 'hello';

		$this->groupSyncCache->startSync( $groupId );
		$this->groupSyncCache->addMessages(
			$groupId, $this->getMessageParam( $groupId, $title )
		);
		$this->assertNotEmpty( $this->groupSyncCache->getMessages( $title )[$title] );
		$this->assertTrue( $this->groupSyncCache->isGroupBeingProcessed( $groupId ) );

		$this->groupSyncCache->endSync( $groupId );

		$message = $this->groupSyncCache->getMessages( $title )[$title];
		$this->assertEmpty( $message );
	}

	/**
	 * @dataProvider provideGetSynchronizationStatus
	 */
	public function testGetSynchronizationStatus(
		GroupSynchronizationCache $syncCache,
		string $groupId,
		array $titlesToAdd,
		array $titlesToRemove,
		bool $hasTimedOut = false
	) {
		$syncCache->startSync( $groupId );

		$this->assertTrue( $syncCache->isGroupBeingProcessed( $groupId ) );

		foreach ( $titlesToAdd as $title ) {
			$syncCache->addMessages(
				$groupId, $this->getMessageParam( $groupId, $title )
			);
		}

		$this->assertEquals( $titlesToAdd, $syncCache->getGroupMessageKeys( $groupId ) );

		$syncCache->removeMessages( ...$titlesToRemove );

		$groupSyncResponse = $syncCache->getSynchronizationStatus( $groupId );

		$diffArray = array_values( array_diff( $titlesToAdd, $titlesToRemove ) );
		$this->assertEquals( $diffArray,  $syncCache->getGroupMessageKeys( $groupId ) );

		if ( $diffArray === [] ) {
			$this->assertEmpty( $this->groupSyncCache->getGroupsInSync() );
		}

		$this->assertEquals( $diffArray === [], $groupSyncResponse->isDone() );
		$this->assertEquals( $hasTimedOut, $groupSyncResponse->hasTimedOut() );
		$this->assertEquals(
			$diffArray,
			$groupSyncResponse->getRemainingMessages()
		);
		$this->assertEquals( $groupId, $groupSyncResponse->getGroupId() );
	}

	public function testGetMulti() {
		$groupId = 'hello';

		$this->groupSyncCache->addMessages( $groupId,
			$this->getMessageParam( $groupId, 'Title' ),
			$this->getMessageParam( $groupId, 'Title_ABC' )
		);

		$messages = $this->groupSyncCache->getMessages( 'Title', 'Title_ABC', 'Title_ABCD' );
		$this->assertEquals( [ 'Title', 'Title_ABC', 'Title_ABCD' ], array_keys( $messages ) );

		$messages = $this->groupSyncCache->getMessages( 'Title_ABCD' );
		$this->assertNull( $messages['Title_ABCD'] );
	}

	public function provideGetSynchronizationStatus() {
		$groupId = 'hello';
		$syncCache = $this->getGroupSynchronizationCache();
		yield [
			$syncCache,
			$groupId,
			[ 'Title', 'Title1' ],
			[ 'Title' ],
			false
		];

		$syncCache = $this->getGroupSynchronizationCache();
		yield [
			$syncCache,
			$groupId,
			[ 'Hello' ],
			[ 'Hello' ],
			false
		];

		$syncCache = $this->getGroupSynchronizationCache( -1 );
		yield [
			$syncCache,
			$groupId,
			[ 'Hello' ],
			[ 'Hello' ],
			false
		];

		$syncCache = $this->getGroupSynchronizationCache( -1 );
		yield [
			$syncCache,
			$groupId,
			[ 'Hello', 'Title' ],
			[ 'Hello' ],
			true
		];
	}

	private function getMessageParam( string $groupId, string $title ): MessageUpdateParameter {
		return new MessageUpdateParameter( [
			'fuzzy' => true,
			'content' => 'Hello',
			'title' => $title,
			'groupId' => $groupId
		] );
	}

	private function getGroupSynchronizationCache( int $timeout = null ): GroupSynchronizationCache {
		if ( $timeout ) {
			return new GroupSynchronizationCache( new HashBagOStuff(), $timeout );
		}

		return new GroupSynchronizationCache( new HashBagOStuff() );
	}
}
