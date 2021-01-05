<?php

namespace MediaWiki\Extension\Translate\Synchronization;

use InvalidArgumentException;
use MediaWiki\Extension\Translate\Cache\PersistentDatabaseCache;
use MediaWiki\Extension\Translate\Services;
use MediaWiki\MediaWikiServices;
use MediaWikiIntegrationTestCase;

/**
 * @covers \MediaWiki\Extension\Translate\Synchronization\GroupSynchronizationCache
 * @covers \MediaWiki\Extension\Translate\Synchronization\GroupSynchronizationResponse
 */
class GroupSynchronizationCacheTest extends MediaWikiIntegrationTestCase {
	/** @var GroupSynchronizationCache */
	private $groupSyncCache;

	protected function setUp(): void {
		parent::setUp();
		$this->groupSyncCache = $this->getGroupSynchronizationCache();
	}

	public function testIsGroupBeingProcessed() {
		$groupId = 'hello';
		$this->groupSyncCache->markGroupForSync( $groupId );

		$this->assertTrue( $this->groupSyncCache->isGroupBeingProcessed( $groupId ) );

		$this->groupSyncCache->endSync( $groupId );

		$this->assertFalse( $this->groupSyncCache->isGroupBeingProcessed( $groupId ) );
	}

	public function testGetGroupMessages() {
		$groupId = 'hello';
		$title = 'Title';

		$this->groupSyncCache->markGroupForSync( $groupId );
		$messageParam = $this->getMessageParam( $groupId, $title );
		$this->groupSyncCache->addMessages( $groupId, $messageParam );

		$this->assertEquals(
			[ $title => $messageParam ],
			$this->groupSyncCache->getGroupMessages( $groupId )
		);

		$this->groupSyncCache->removeMessages( $groupId, $title );

		$this->assertSame( [], $this->groupSyncCache->getGroupMessages( $groupId ) );
	}

	public function testMultiGetGroupMessages() {
		$groupId = 'hello';

		$addedMessages = [
			'Title' => $this->getMessageParam( $groupId, 'Title' ),
			'Title_ABC' => $this->getMessageParam( $groupId, 'Title_ABC' )
		];

		$this->groupSyncCache->addMessages( $groupId, ...array_values( $addedMessages ) );

		$messages = $this->groupSyncCache->getGroupMessages( $groupId );
		$this->assertEquals( $addedMessages, $messages );
	}

	public function testIsMessageBeingProcessed() {
		$groupId = 'hello';
		$title = 'Title';

		$this->groupSyncCache->addMessages(
			$groupId, $this->getMessageParam( $groupId, $title )
		);

		$this->assertTrue( $this->groupSyncCache->isMessageBeingProcessed( $groupId, $title ) );

		$this->groupSyncCache->removeMessages( $groupId, $title );

		$this->assertFalse( $this->groupSyncCache->isMessageBeingProcessed( $groupId, $title ) );
	}

	public function testGetGroupsInSync() {
		$groupId = 'hello';

		$this->groupSyncCache->markGroupForSync( $groupId );
		$this->assertSame( [ $groupId ], $this->groupSyncCache->getGroupsInSync() );

		$this->groupSyncCache->endSync( $groupId );
		$this->assertSame( [], $this->groupSyncCache->getGroupsInSync() );
	}

	public function testEndSync() {
		$groupId = 'group-id';
		$title = 'hello';

		$this->startGroupSync( $groupId, $title );

		$this->expectExceptionMessageMatches( '/cannot end synchronization/i' );
		$this->expectException( InvalidArgumentException::class );

		$this->groupSyncCache->endSync( $groupId );

		$messages = $this->groupSyncCache->getGroupMessages( $groupId );
		$this->assertNotEmpty( $messages, 'endSync should not remove group messages' );
		$this->assertIsInt(
			$this->groupSyncCache->getSyncEndTime( $groupId ),
			'endSync should not remove the group key'
		);
	}

	public function testForceEndSync() {
		$groupId = 'group-id';
		$title = 'hello';

		$this->startGroupSync( $groupId, $title );

		$this->groupSyncCache->forceEndSync( $groupId );

		$messages = $this->groupSyncCache->getGroupMessages( $groupId );
		$this->assertEmpty( $messages, 'forceEndSync should remove group messages' );
		$this->assertNull(
			$this->groupSyncCache->getSyncEndTime( $groupId ),
			'forceEndSync should remove the group key'
		);
	}

	/** @dataProvider provideGetSynchronizationStatus */
	public function testGetSynchronizationStatus(
		GroupSynchronizationCache $syncCache,
		string $groupId,
		array $titlesToAdd,
		array $titlesToRemove,
		bool $hasTimedOut = false
	) {
		$syncCache->markGroupForSync( $groupId );

		$this->assertTrue( $syncCache->isGroupBeingProcessed( $groupId ) );

		$addedMessages = [];
		foreach ( $titlesToAdd as $title ) {
			$messageParam = $this->getMessageParam( $groupId, $title );
			$syncCache->addMessages( $groupId, $messageParam );
			$addedMessages[$title] = $messageParam;
		}

		$this->assertEqualsCanonicalizing( $addedMessages, $syncCache->getGroupMessages( $groupId ) );

		$syncCache->removeMessages( $groupId, ...$titlesToRemove );

		$groupSyncResponse = $syncCache->getSynchronizationStatus( $groupId );

		$diffTitles = array_values( array_diff( $titlesToAdd, $titlesToRemove ) );
		$diffMessages = [];
		foreach ( $diffTitles as $title ) {
			$diffMessages[$title] = $addedMessages[$title];
		}

		$this->assertEquals( $diffMessages, $syncCache->getGroupMessages( $groupId ) );

		if ( $diffMessages === [] ) {
			// getSynchronizationStatus does not perform any updates
			$this->assertContains( $groupId, $this->groupSyncCache->getGroupsInSync() );
		}

		$this->assertSame( $diffMessages === [], $groupSyncResponse->isDone() );
		$this->assertSame( $hasTimedOut, $groupSyncResponse->hasTimedOut() );
		$this->assertEquals( $diffMessages, $groupSyncResponse->getRemainingMessages() );
		$this->assertSame( $groupId, $groupSyncResponse->getGroupId() );
	}

	public function provideGetSynchronizationStatus() {
		$groupId = 'hello';

		yield [
			$this->getGroupSynchronizationCache(),
			$groupId,
			[ 'Title', 'Title1' ],
			[ 'Title' ],
			false
		];

		yield [
			$this->getGroupSynchronizationCache(),
			$groupId,
			[ 'Hello' ],
			[ 'Hello' ],
			false
		];

		yield [
			$this->getGroupSynchronizationCache( -1 ),
			$groupId,
			[ 'Hello' ],
			[ 'Hello' ],
			false
		];

		yield [
			$this->getGroupSynchronizationCache( -1 ),
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
		$lb = MediaWikiServices::getInstance()->getDBLoadBalancer();
		$jsonCodec = Services::getInstance()->getJsonCodec();
		$persistentCache = new PersistentDatabaseCache( $lb, $jsonCodec );
		$persistentCache->clear();

		if ( $timeout ) {
			return new GroupSynchronizationCache( $persistentCache, $timeout );
		} else {
			return new GroupSynchronizationCache( $persistentCache );
		}
	}

	private function startGroupSync( string $groupId, string $title ): void {
		$this->groupSyncCache->markGroupForSync( $groupId );
		$this->groupSyncCache->addMessages( $groupId, $this->getMessageParam( $groupId, $title ) );
	}
}
