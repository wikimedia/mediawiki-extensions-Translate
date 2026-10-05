<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Synchronization;

use MediaWiki\Diff\DifferenceEngine;
use MediaWiki\Extension\Translate\MessageGroups\MessageGroup;
use MediaWiki\Extension\Translate\MessageSync\MessageSourceChange;
use MediaWiki\MainConfigNames;
use MediaWiki\Page\LinkBatch;
use MediaWiki\Page\LinkBatchFactory;
use MediaWiki\Request\FauxRequest;
use MediaWikiIntegrationTestCase;
use ReflectionMethod;
use ReflectionProperty;

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

	public function testStaleChangesHaveBoundedHiddenIds(): void {
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
		$html = $page->getOutput()->getHTML();
		$this->assertSame( 997, substr_count( $html, 'type="hidden"' ) );
		$this->assertStringNotContainsString( 'smg-change-deletion', $html );
	}

	public function testPostponesUnsubmittedChangesAndCleansMissingTitles(): void {
		$batchSizes = [];
		$page = $this->newPage( $this->newTrackingLinkBatchFactory( $batchSizes ) );
		$group = $this->newGroup();
		$changes = new MessageSourceChange();

		$changes->addAddition( 'en', 'submitted-addition', 'new' );
		$changes->addAddition( 'en', 'postponed-addition', 'later' );
		$changes->addDeletion( 'en', 'submitted-deletion', 'old' );
		$changes->addDeletion( 'en', 'postponed-deletion', 'old' );
		$changes->addDeletion( 'en', 'missing-submitted-deletion', 'old' );
		$changes->addDeletion( 'en', 'missing-postponed-deletion', 'old' );
		$changes->addChange( 'en', 'submitted-change', 'new' );
		$changes->addChange( 'en', 'postponed-change', 'new' );
		$changes->addChange( 'en', 'missing-submitted-change', 'new' );
		$changes->addChange( 'en', 'missing-postponed-change', 'new' );

		foreach ( [
			'submitted-deletion',
			'postponed-deletion',
			'submitted-change',
			'postponed-change',
		] as $key ) {
			$this->insertPage( "$key/en", 'existing' );
		}

		$requestValues = [];
		foreach ( [
			[ MessageSourceChange::ADDITION, 'submitted-addition', 'import' ],
			[ MessageSourceChange::DELETION, 'submitted-deletion', null ],
			[ MessageSourceChange::DELETION, 'missing-submitted-deletion', null ],
			[ MessageSourceChange::CHANGE, 'submitted-change', 'import' ],
			[ MessageSourceChange::CHANGE, 'missing-submitted-change', 'import' ],
		] as [ $type, $key, $selection ] ) {
			$id = $this->changeId( 'large-group', 'en', $type, $key );
			$requestValues[$id] = 1;
			if ( $selection !== null ) {
				$requestValues["msg/$id"] = $selection;
			}
		}

		$postponed = [];
		$jobs = [];
		$method = new ReflectionMethod( ManageGroupsSpecialPage::class, 'handleModificationsSubmit' );
		$method->invokeArgs( $page, [
			$group,
			$changes,
			new FauxRequest( $requestValues, true ),
			'en',
			&$postponed,
			&$jobs,
		] );

		$this->assertCount( 2, $jobs );
		$this->assertSame(
			[ 'postponed-addition' ],
			array_column( $postponed['large-group']['en'][MessageSourceChange::ADDITION], 'key' )
		);
		$this->assertSame(
			[ 'postponed-deletion', 'missing-postponed-deletion' ],
			array_column( $postponed['large-group']['en'][MessageSourceChange::DELETION], 'key' )
		);
		$this->assertSame(
			[ 'postponed-change', 'missing-postponed-change' ],
			array_column( $postponed['large-group']['en'][MessageSourceChange::CHANGE], 'key' )
		);
		$this->assertSame( [ 2, 2 ], $batchSizes );
	}

	private function changeId( string $groupId, string $language, string $type, string $key ): string {
		$method = new ReflectionMethod( ManageGroupsSpecialPage::class, 'changeId' );
		return $method->invoke( null, $groupId, $language, $type, $key );
	}

	public function testUnsubmittedLargeChangesetDoesNotPrefetchTitles(): void {
		$batchSizes = [];
		$page = $this->newPage( $this->newTrackingLinkBatchFactory( $batchSizes ) );
		$messages = [];
		for ( $index = 0; $index < 100000; $index++ ) {
			$messages[] = [ 'key' => "unsubmitted-$index", 'content' => 'old' ];
		}
		$changes = new MessageSourceChange( [ 'en' => [ MessageSourceChange::DELETION => $messages ] ] );
		$postponed = $jobs = [];
		$method = new ReflectionMethod( ManageGroupsSpecialPage::class, 'handleModificationsSubmit' );
		$method->invokeArgs( $page, [
			$this->newGroup(), $changes, new FauxRequest( [], true ), 'en', &$postponed, &$jobs,
		] );

		$this->assertSame( [], $batchSizes );
		$this->assertSame( [], $jobs );
		$this->assertSame( $messages, $postponed['large-group']['en'][MessageSourceChange::DELETION] );
	}

	public function testStaleEntriesDisappearAsTheirHiddenIdsAreSubmitted(): void {
		$batchSizes = [];
		$page = $this->newPage( $this->newTrackingLinkBatchFactory( $batchSizes ) );
		$group = $this->newGroup();
		$changes = new MessageSourceChange();
		$changes->addDeletion( 'en', 'stale-deletion', 'old' );
		$changes->addChange( 'fi', 'stale-change', 'new' );
		$render = new ReflectionMethod( ManageGroupsSpecialPage::class, 'showModificationChanges' );
		$submit = new ReflectionMethod( ManageGroupsSpecialPage::class, 'handleModificationsSubmit' );

		$types = [ 'en' => MessageSourceChange::DELETION, 'fi' => MessageSourceChange::CHANGE ];
		foreach ( $types as $language => $type ) {
			$messages = $changes->getModificationsForLanguage( $language )[$type];
			$limit = 1;
			$this->assertTrue( $render->invokeArgs( $page, [
				$group, $changes, $page->getOutput(), $language, $type, $messages, &$limit,
			] ) );
			$id = $this->changeId( 'large-group', $language, $type, $messages[0]['key'] );
			$this->assertStringContainsString( 'name="' . $id . '"', $page->getOutput()->getHTML() );
			$this->assertSame( 0, $limit );
			$request = new FauxRequest( [ $id => 'stale' ], true );
			$postponed = $jobs = [];
			foreach ( $changes->getLanguages() as $pendingLanguage ) {
				$submit->invokeArgs( $page, [
					$group, $changes, $request, $pendingLanguage, &$postponed, &$jobs,
				] );
			}
			$this->assertArrayNotHasKey( $language, $postponed['large-group'] ?? [] );
			$this->assertSame( [], $jobs );
			$changes = new MessageSourceChange( $postponed['large-group'] ?? [] );
		}

		$this->assertSame( [], $changes->getLanguages() );
	}

	public function testRecreatedStalePagesArePostponedForReview(): void {
		$page = $this->newPage();
		$group = $this->newGroup();
		$format = new ReflectionMethod( ManageGroupsSpecialPage::class, 'formatChange' );
		$submit = new ReflectionMethod( ManageGroupsSpecialPage::class, 'handleModificationsSubmit' );
		foreach ( [ MessageSourceChange::DELETION, MessageSourceChange::CHANGE ] as $type ) {
			$params = [ 'key' => "recreated-$type", 'content' => 'incoming' ];
			$changes = new MessageSourceChange( [ 'en' => [ $type => [ $params ] ] ] );
			$limit = 1;
			$html = $format->invokeArgs( $page, [ $group, $changes, 'en', $type, $params, &$limit ] );
			$this->assertStringContainsString( 'value="stale"', $html );
			$this->insertPage( $params['key'] . '/en', 'recreated content' );
			$id = $this->changeId( 'large-group', 'en', $type, $params['key'] );
			$postponed = $jobs = [];
			$submit->invokeArgs( $page, [
				$group, $changes, new FauxRequest( [ $id => 'stale' ], true ), 'en', &$postponed, &$jobs,
			] );
			$this->assertSame( [ $params ], $postponed['large-group']['en'][$type] );
			$this->assertSame( [], $jobs );
		}
	}

	public function testRenderingUsesTheVariableBudgetRatherThanTheEntryCount(): void {
		$batchSizes = [];
		$page = $this->newPage( $this->newTrackingLinkBatchFactory( $batchSizes ) );
		$group = $this->newGroup();
		$this->insertPage( 'visible-change/en', 'old' );
		$diff = new ReflectionProperty( ManageGroupsSpecialPage::class, 'diff' );
		$diff->setValue( $page, new DifferenceEngine( $page->getContext() ) );
		$changes = new MessageSourceChange();
		$changes->addChange( 'en', 'first-stale', 'new' );
		$changes->addChange( 'en', 'visible-change', 'new' );
		$changes->addChange( 'en', 'next-stale', 'new' );
		$changes->addChange( 'en', 'last-stale', 'new' );
		$limit = 3;
		$render = new ReflectionMethod( ManageGroupsSpecialPage::class, 'showModificationChanges' );
		$this->assertTrue( $render->invokeArgs( $page, [
			$group, $changes, $page->getOutput(), 'en', MessageSourceChange::CHANGE,
			$changes->getChanges( 'en' ), &$limit,
		] ) );

		$this->assertSame( 0, $limit );
		$this->assertSame( [ 3 ], $batchSizes );
		$html = $page->getOutput()->getHTML();
		foreach ( [ 'first-stale', 'visible-change' ] as $key ) {
			$id = $this->changeId( 'large-group', 'en', MessageSourceChange::CHANGE, $key );
			$this->assertStringContainsString( 'name="' . $id . '"', $html );
		}
		$id = $this->changeId( 'large-group', 'en', MessageSourceChange::CHANGE, 'next-stale' );
		$this->assertStringNotContainsString( 'name="' . $id . '"', $html );
	}
}
