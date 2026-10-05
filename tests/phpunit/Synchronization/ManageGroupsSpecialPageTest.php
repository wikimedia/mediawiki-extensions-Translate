<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Synchronization;

use MediaWiki\Diff\DifferenceEngine;
use MediaWiki\Extension\Translate\MessageGroupProcessing\RevTagStore;
use MediaWiki\Extension\Translate\MessageGroups\MessageGroup;
use MediaWiki\Extension\Translate\MessageSync\MessageSourceChange;
use MediaWiki\MainConfigNames;
use MediaWiki\Page\LinkBatch;
use MediaWiki\Page\LinkBatchFactory;
use MediaWiki\Request\FauxRequest;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\Revision\SlotRecord;
use MediaWiki\Title\Title;
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
			$services->getRevisionStore(),
			$services->get( 'Translate:GroupSynchronizationCache' ),
			$services->getJobQueueGroup(),
			$services->get( 'Translate:MessageIndex' ),
			$linkBatchFactory ?? $services->getLinkBatchFactory(),
			$services->get( 'Translate:MessageGroupSubscription' )
		);
	}

	private function newGroup( string $groupId = 'large-group' ): MessageGroup {
		$group = $this->createMock( MessageGroup::class );
		$group->method( 'getId' )->willReturn( $groupId );
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

	public function testSubmittingDeletionDoesNotAcknowledgeCollidingTranslation(): void {
		$page = $this->newPage();
		$group = $this->newGroup( 'ohm-website3' );
		$englishKey = 'ohm-activerecord.attributes.user.pass_crypt';
		$chechenKey = 'ohm-geocoder.search_osm_nominatim.prefix.leisure.yes';
		$englishId = $this->changeId( 'ohm-website3', 'en', MessageSourceChange::DELETION, $englishKey );
		$chechenId = $this->changeId( 'ohm-website3', 'ce', MessageSourceChange::DELETION, $chechenKey );
		// These real migration entries collide when the hash is truncated to seven characters.
		$this->assertStringStartsWith( 'smg/e45545c', $englishId );
		$this->assertStringStartsWith( 'smg/e45545c', $chechenId );
		$this->assertNotSame( $englishId, $chechenId );

		$changes = new MessageSourceChange();
		$changes->addDeletion( 'en', $englishKey, 'old' );
		$changes->addDeletion( 'ce', $chechenKey, 'old' );
		$this->insertPage( "$englishKey/en", 'old' );
		$this->insertPage( "$chechenKey/ce", 'old' );
		$request = new FauxRequest( [ $englishId => 1 ], true );
		$postponed = $jobs = [];
		$submit = new ReflectionMethod( ManageGroupsSpecialPage::class, 'handleModificationsSubmit' );
		foreach ( $changes->getLanguages() as $language ) {
			$submit->invokeArgs( $page, [ $group, $changes, $request, $language, &$postponed, &$jobs ] );
		}

		$this->assertArrayNotHasKey( 'en', $postponed['ohm-website3'] );
		$this->assertSame(
			$changes->getDeletions( 'ce' ),
			$postponed['ohm-website3']['ce'][MessageSourceChange::DELETION]
		);
		$this->assertSame( [], $jobs );
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

	public function testBatchLoadsLatestContentAndCurrentFuzzyTags(): void {
		$page = $this->newPage();
		$group = $this->newGroup();
		$messages = [];
		foreach ( [ 'current-fuzzy', 'old-fuzzy' ] as $key ) {
			$this->insertPage( "$key/en", 'previous content' );
			$title = Title::newFromText( "$key/en" );
			$store = $this->getServiceContainer()->getRevisionStore();
			$oldRevision = $store->getRevisionByTitle( $title );
			$this->editPage( $title, 'latest content' );
			$currentRevision = $store->getRevisionByTitle( $title );
			$this->getDb()->newInsertQueryBuilder()
				->insertInto( 'revtag' )
				->row( [
					'rt_page' => $title->getArticleID(),
					'rt_revision' => $key === 'current-fuzzy' ? $currentRevision->getId() : $oldRevision->getId(),
					'rt_type' => RevTagStore::FUZZY_TAG,
				] )
				->caller( __METHOD__ )
				->execute();
			$messages[] = [ 'key' => $key, 'content' => 'incoming' ];
		}
		$messages[] = [ 'key' => 'missing-page', 'content' => 'incoming' ];
		$load = new ReflectionMethod( ManageGroupsSpecialPage::class, 'loadCurrentRevisions' );
		$current = $load->invoke( $page, $group, 'en', $messages );

		$this->assertArrayNotHasKey( 'missing-page', $current );
		$this->assertTrue( $current['current-fuzzy']['fuzzy'] );
		$this->assertFalse( $current['old-fuzzy']['fuzzy'] );
		$diff = new ReflectionProperty( ManageGroupsSpecialPage::class, 'diff' );
		$diff->setValue( $page, new DifferenceEngine( $page->getContext() ) );
		$format = new ReflectionMethod( ManageGroupsSpecialPage::class, 'formatChange' );
		foreach ( array_slice( $messages, 0, 2 ) as $params ) {
			$entry = $current[$params['key']];
			$this->assertSame( 'latest content', $entry['revision']->getContent( SlotRecord::MAIN )->getText() );
			$limit = 2;
			$html = $format->invokeArgs( $page, [
				$group, new MessageSourceChange(), 'en', MessageSourceChange::CHANGE, $params, &$limit,
				$entry['revision'], $entry['fuzzy'],
			] );
			$this->assertSame( $entry['fuzzy'], str_contains( strip_tags( $html ), TRANSLATE_FUZZY ) );
		}
	}

	public function testReusedAdditionRendersCurrentContentFromBatch(): void {
		$page = $this->newPage();
		$group = $this->newGroup();
		$this->insertPage( 'reused-addition/en', 'existing reused content' );
		$diff = new ReflectionProperty( ManageGroupsSpecialPage::class, 'diff' );
		$diff->setValue( $page, new DifferenceEngine( $page->getContext() ) );
		$changes = new MessageSourceChange();
		$changes->addAddition( 'en', 'reused-addition', 'incoming' );
		$limit = 2;
		$render = new ReflectionMethod( ManageGroupsSpecialPage::class, 'showModificationChanges' );
		$render->invokeArgs( $page, [
			$group, $changes, $page->getOutput(), 'en', MessageSourceChange::ADDITION,
			$changes->getAdditions( 'en' ), &$limit,
		] );

		$html = $page->getOutput()->getHTML();
		$this->assertStringContainsString( 'smg-change-change', $html );
		$this->assertStringContainsString( 'existing reused content', strip_tags( $html ) );
		$this->assertSame( 0, $limit );
	}

	public function testBatchPreservesHiddenMetadataVisibility(): void {
		$page = $this->newPage();
		$this->insertPage( 'hidden-metadata/en', 'public content' );
		$title = Title::newFromText( 'hidden-metadata/en' );
		$revision = $this->getServiceContainer()->getRevisionStore()->getRevisionByTitle( $title );
		$this->getDb()->newUpdateQueryBuilder()
			->update( 'revision' )
			->set( [ 'rev_deleted' => RevisionRecord::DELETED_USER | RevisionRecord::DELETED_COMMENT ] )
			->where( [ 'rev_id' => $revision->getId() ] )
			->caller( __METHOD__ )
			->execute();
		$load = new ReflectionMethod( ManageGroupsSpecialPage::class, 'loadCurrentRevisions' );
		$current = $load->invoke( $page, $this->newGroup(), 'en', [
			[ 'key' => 'hidden-metadata', 'content' => 'incoming' ],
		] );

		$record = $current['hidden-metadata']['revision'];
		$this->assertNull( $record->getUser( RevisionRecord::FOR_PUBLIC ) );
		$this->assertNull( $record->getComment( RevisionRecord::FOR_PUBLIC ) );
		$this->assertSame( 'public content', $record->getContent( SlotRecord::MAIN )->getText() );
	}
}
