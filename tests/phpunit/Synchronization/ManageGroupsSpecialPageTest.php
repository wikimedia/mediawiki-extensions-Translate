<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Synchronization;

use MediaWiki\Extension\Translate\MessageGroups\MessageGroup;
use MediaWiki\Extension\Translate\MessageSync\MessageSourceChange;
use MediaWiki\MainConfigNames;
use MediaWiki\Page\LinkBatch;
use MediaWiki\Page\LinkBatchFactory;
use MediaWikiIntegrationTestCase;
use ReflectionMethod;

/**
 * @covers \MediaWiki\Extension\Translate\Synchronization\ManageGroupsSpecialPage
 * @group Database
 */
class ManageGroupsSpecialPageTest extends MediaWikiIntegrationTestCase {
	protected function setUp(): void {
		parent::setUp();
		$this->overrideConfigValues( [
			MainConfigNames::Server => 'https://wiki.local',
			MainConfigNames::CanonicalServer => 'https://wiki.local',
		] );
	}

	private function newPage( ?LinkBatchFactory $linkBatchFactory = null ): ManageGroupsSpecialPage {
		$services = $this->getServiceContainer();
		return new ManageGroupsSpecialPage(
			$services->getContentLanguage(),
			$services->getNamespaceInfo(),
			$services->getRevisionLookup(),
			$services->get( 'Translate:GroupSynchronizationCache' ),
			$services->getJobQueueGroup(),
			$services->get( 'Translate:MessageIndex' ),
			$linkBatchFactory ?? $services->getLinkBatchFactory(),
			$services->get( 'Translate:MessageGroupSubscription' )
		);
	}

	private function newGroup(): MessageGroup {
		$group = $this->createMock( MessageGroup::class );
		$group->method( 'getId' )->willReturn( 'large-group' );
		$group->method( 'getNamespace' )->willReturn( NS_MAIN );
		$group->method( 'getSourceLanguage' )->willReturn( 'qqq' );
		return $group;
	}

	/** Returns a LinkBatchFactory that records the number of titles added to each batch. */
	private function newTrackingLinkBatchFactory( array &$batchSizes ): LinkBatchFactory {
		$factory = $this->createMock( LinkBatchFactory::class );
		$factory->method( 'newLinkBatch' )->willReturnCallback( function () use ( &$batchSizes ) {
			$index = count( $batchSizes );
			$batchSizes[$index] = 0;
			$batch = $this->createMock( LinkBatch::class );
			$batch->method( 'add' )->willReturnCallback( static function () use ( &$batchSizes, $index ) {
				$batchSizes[$index]++;
			} );
			$batch->method( 'setCaller' )->willReturnSelf();
			return $batch;
		} );
		return $factory;
	}

	public function testStaleChangesHaveBoundedPrefetch(): void {
		$batchSizes = [];
		$page = $this->newPage( $this->newTrackingLinkBatchFactory( $batchSizes ) );
		$group = $this->newGroup();
		$changes = new MessageSourceChange();
		$messages = [];
		for ( $index = 0; $index < 100000; $index++ ) {
			$messages[] = [
				'key' => "key-$index",
				'content' => '',
			];
		}

		$limit = 997;
		$method = new ReflectionMethod( ManageGroupsSpecialPage::class, 'showModificationChanges' );
		$limitReached = $method->invokeArgs( $page, [
			$group,
			$changes,
			$page->getOutput(),
			'en',
			MessageSourceChange::DELETION,
			$messages,
			&$limit,
		] );

		$this->assertTrue( $limitReached );
		$this->assertSame( 0, $limit );
		$this->assertSame( [ 997 ], $batchSizes );
		$this->assertSame( '', $page->getOutput()->getHTML() );
	}
}
