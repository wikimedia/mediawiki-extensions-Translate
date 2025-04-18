<?php
/**
 * Tests for fuzzy flag change on edits.
 * @author Niklas Laxström
 * @file
 * @license GPL-2.0-or-later
 */

use MediaWiki\CommentStore\CommentStoreComment;
use MediaWiki\Content\ContentHandler;
use MediaWiki\Extension\Translate\MessageGroupProcessing\RevTagStore;
use MediaWiki\Extension\Translate\MessageLoading\MessageHandle;
use MediaWiki\Page\PageReference;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\Revision\SlotRecord;
use MediaWiki\Storage\EditResult;
use MediaWiki\Title\Title;
use MediaWiki\User\UserIdentity;

/**
 * Tests for fuzzy flag change on edits.
 * @group Database
 * @group medium
 * @covers \MediaWiki\Extension\Translate\TranslatorInterface\TranslateEditAddons
 * @covers \MediaWiki\Extension\Translate\HookHandler::onRevisionRecordInserted
 */
class TranslationFuzzyUpdaterTest extends MediaWikiIntegrationTestCase {
	use MessageGroupTestTrait;

	protected function setUp(): void {
		parent::setUp();
		$this->setupGroupTestEnvironmentWithGroups( $this, $this->getTestGroups() );
	}

	public function getTestGroups() {
		$messages = [ 'ugakey' => '$1 of $2', ];
		$list['test-group'] = new MockWikiMessageGroup( 'test-group', $messages );

		$otherMessages = [ 'nlkey' => 'Test message' ];
		$list['validation-test-group'] = new MockWikiValidationMessageGroup(
			'validation-test-group', $otherMessages );

		return $list;
	}

	private function saveRevision(
		WikiPage $page,
		string $content,
		UserIdentity $user,
		string $comment = __METHOD__
	): ?RevisionRecord {
		$contentObj = ContentHandler::makeContent( $content, $page->getTitle() );
		$updater = $page->newPageUpdater( $user )->setContent( SlotRecord::MAIN, $contentObj );
		$revisionRecord = $updater->saveRevision( CommentStoreComment::newUnsavedComment( $comment ) );
		$this->assertTrue( $updater->wasSuccessful(), "Saving edit should have suceeded" );
		return $revisionRecord;
	}

	private function saveRevert(
		WikiPage $page,
		string $content,
		UserIdentity $user,
		int $revertMethod,
		int $revertedId
	): ?RevisionRecord {
		$contentObj = ContentHandler::makeContent( $content, $page->getTitle() );
		$updater = $page->newPageUpdater( $user )->setContent( SlotRecord::MAIN, $contentObj );
		$updater->markAsRevert( $revertMethod, $revertedId );
		$revisionRecord = $updater->saveRevision( CommentStoreComment::newUnsavedComment( __METHOD__ ) );
		$this->assertTrue( $updater->wasSuccessful(), "Saving edit should have suceeded" );
		return $revisionRecord;
	}

	private function fuzzyRevision( RevisionRecord $rev ): void {
		$dbw = $this->getDb();
		$conds = [
			'rt_page' => $rev->getPageId(),
			'rt_type' => RevTagStore::FUZZY_TAG,
			'rt_revision' => $rev->getId()
		];

		$index = array_keys( $conds );
		$dbw->newReplaceQueryBuilder()
			->replaceInto( 'revtag' )
			->uniqueIndexFields( $index )
			->row( $conds )
			->caller( __METHOD__ )
			->execute();
	}

	/** @return int|false */
	private function getLastLogId( PageReference $title ) {
		return $this->getDb()->newSelectQueryBuilder()
				->select( 'max(log_id)' )
				->from( 'logging' )
				->where( [
					'log_type' => 'translationreview',
					'log_action' => 'unfuzzy',
					'log_namespace' => $title->getNamespace(),
					'log_title' => $title->getDBkey(),
				] )
				->caller( __METHOD__ )
				->fetchField();
	}

	public function testParsing() {
		$user = $this->getTestUser()->getUser();
		$container = $this->getServiceContainer();
		$title = Title::newFromText( 'MediaWiki:Ugakey/nl' );
		$page = $container->getWikiPageFactory()->newFromTitle( $title );
		$originalRevisionRecord = $this->saveRevision( $page, '$1 van $2 old', $user );

		$this->fuzzyRevision( $originalRevisionRecord );

		$handle = new MessageHandle( $title );
		$this->assertTrue( $handle->isValid(), 'Message is known' );
		$this->assertTrue( $handle->isFuzzy(), 'Message is fuzzy after database fuzzying' );

		$permissionManager = $container->getPermissionManager();
		$oldLastLogId = $this->getLastLogId( $title );

		// Attempt non-null-edit unfuzzy as a user without the unfuzzy right
		$permissionManager->overrideUserRightsForTesting( $user, [] );
		$firstRevisionRecord = $this->saveRevision( $page, '$1 van $2', $user );
		$this->assertTrue(
			$handle->isFuzzy(),
			'Message is still fuzzy after non-null edit without required permissions'
		);
		// And no null revision was created
		$this->assertEquals(
			$firstRevisionRecord->getId(),
			$container->getRevisionStore()->getRevisionByPageId( $page->getId() )->getId(),
			'No null revision was created after non-null edit'
		);
		$this->assertEquals(
			$oldLastLogId,
			$this->getLastLogId( $title ),
			'Failed unfuzzy should not have generated log entry'
		);

		// Attempt null-edit unfuzzy as a user without the unfuzzy right
		$secondRevisionRecord = $this->saveRevision( $page, '$1 van $2', $user );
		$this->assertTrue(
			$handle->isFuzzy(),
			'Message is still fuzzy after null edit without required permissions'
		);
		// No edit was made (it's a null edit) and no revision was added by Translate either
		$this->assertNull( $secondRevisionRecord, 'Null edit should not have been saved' );
		$this->assertEquals(
			$firstRevisionRecord->getId(),
			$container->getRevisionStore()->getRevisionByPageId( $page->getId() )->getId(),
			'Null editing page without permissions should not have created any revisions'
		);
		$this->assertEquals(
			$oldLastLogId,
			$this->getLastLogId( $title ),
			'Failed unfuzzy should not have generated log entry'
		);

		// Now add the required rights
		$rightsCallback = $permissionManager->addTemporaryUserRights( $user,
			[ 'editinterface', 'unfuzzy' ]
		);

		// Test that PageUpdater dummy revisions (like page moves, protections, etc. ) don't unfuzzy
		$page->newPageUpdater( $user )->saveDummyRevision( CommentStoreComment::newUnsavedComment( 'Blah' ) );
		$this->assertTrue( $handle->isFuzzy(), 'Dummy revision should not unfuzzy' );
		$this->assertEquals(
			$oldLastLogId,
			$this->getLastLogId( $title ),
			'Failed unfuzzy should not have generated log entry'
		);

		// Try null-edit unfuzzy again
		$nullEditUnfuzzySummaries = [
			[
				'',
				'Marked translation unit as no longer outdated with no changes',
				'Unfuzzying translation with an empty edit summary should have used the default one'
			],
			[
				'It’s still good!',
				'It’s still good!',
				'Unfuzzying translation with a non-empty edit summary should have used that one'
			],
			[
				'0',
				'0',
				'Unfuzzying translation with a non-empty but falsy edit summary should have used that one'
			],
		];
		foreach ( $nullEditUnfuzzySummaries as [ $enteredSummary, $actualSummary, $assertionText ] ) {
			if ( isset( $latestRevisionRecordAfterThird ) ) {
				// If not the first iteration, re-fuzzy the revision
				$this->fuzzyRevision( $latestRevisionRecordAfterThird );
			}
			$thirdRevisionRecord = $this->saveRevision( $page, '$1 van $2', $user, $enteredSummary );
			$this->assertFalse(
				$handle->isFuzzy(),
				'Message is no longer fuzzy after null edit with required permissions'
			);
			// This is (from core's POV) a null edit so no revision was created
			$this->assertNull( $thirdRevisionRecord, 'Null edit should not have been saved' );
			// But a null revision was created by Translate...
			$latestRevisionRecordAfterThird = $container->getRevisionStore()->getRevisionByPageId( $page->getId() );
			$this->assertNotEquals(
				$firstRevisionRecord->getId(),
				$latestRevisionRecordAfterThird->getId(),
				'Unfuzzying translation without changes should have created a null revision'
			);
			// ...with the appropriate edit summary (custom if provided, default otherwise)...
			$this->assertEquals( $actualSummary, $latestRevisionRecordAfterThird->getComment()->text, $assertionText );
			// ...as well as a log entry
			$newLastLogId = $this->getLastLogId( $title );
			$this->assertNotEquals(
				$newLastLogId,
				$oldLastLogId,
				'Unfuzzying translation without changes should have created a log entry'
			);
		}

		// Test adding !!FUZZY!! to refuzzy
		$this->saveRevision( $page, '!!FUZZY!!$1 van $2', $user );
		$this->assertTrue( $handle->isFuzzy(), 'Message is fuzzy after manual fuzzying' );

		// Test non-null edit unfuzzy as a user with the unfuzzy right
		$fourthRevisionRecord = $this->saveRevision( $page, '$1 van $2', $user );
		$this->assertFalse( $handle->isFuzzy(), 'Message is unfuzzy after non-null edit with required permissions' );
		// No null revision
		$this->assertEquals(
			$fourthRevisionRecord->getId(),
			$container->getRevisionStore()->getRevisionByPageId( $page->getId() )->getId(),
			'Non-null unfuzzy should not have created an extra null revision to mark it'
		);
		$this->assertEquals(
			$newLastLogId,
			$this->getLastLogId( $title ),
			'Unfuzzying translation with changes should not have created a log entry'
		);
	}

	public function testFuzzyHandlingOnUndo() {
		// Setup
		$user = $this->getTestUser()->getUser();
		$container = $this->getServiceContainer();
		$container->getPermissionManager()->overrideUserRightsForTesting( $user, [ 'editinterface', 'unfuzzy' ] );
		$title = Title::newFromText( 'MediaWiki:Ugakey/nl' );
		$page = $container->getWikiPageFactory()->newFromTitle( $title );
		$originalRevisionRecord = $this->saveRevision( $page, '$1 van $2', $user );
		$this->fuzzyRevision( $originalRevisionRecord );
		$handle = new MessageHandle( $title );

		// Manual reverts aren't handled specially
		$tweakManual = $this->saveRevision( $page, '$1 van $2 tweaked', $user );
		$this->assertFalse( $handle->isFuzzy() );
		$revertRevision = $this->saveRevert( $page, '$1 van $2', $user,
			EditResult::REVERT_MANUAL, $tweakManual->getId()
		);
		$this->assertFalse( $handle->isFuzzy(), "Undo detection shouldn't work on manual reverts" );
		$this->fuzzyRevision( $revertRevision );

		// Undos and rollbacks are
		$revertMechanisms = [ EditResult::REVERT_UNDO, EditResult::REVERT_ROLLBACK ];
		foreach ( $revertMechanisms as $revertHow ) {
			$tweakedRevert = $this->saveRevision( $page, '$1 van $2 tweaked 2', $user );
			$this->assertFalse( $handle->isFuzzy() );
			$revertRevision = $this->saveRevert( $page, '$1 van $2', $user,
				$revertHow, $tweakedRevert->getId()
			);
			$this->assertTrue( $handle->isFuzzy(), "Undoing or rolling back edit should refuzzy" );
		}

		// Make sure you can't unfuzzy by reverting to an unfuzzy version if you don't have permission to do so
		// (the code doesn't actually detect reverts to unfuzzy, but it's worth testing anyway)

		$container->getPermissionManager()->overrideUserRightsForTesting( $user, [] );
		$this->saveRevert(
			$page, '$1 van $2 tweaked 2', $user,
			EditResult::REVERT_UNDO, $revertRevision->getId()
		);
		$this->assertTrue( $handle->isFuzzy(), "Undoing without permission should not unfuzzy" );
	}

	public function testValidationFuzzy() {
		$title = Title::newFromText( 'MediaWiki:nlkey/en-gb' );
		$content = ContentHandler::makeContent( 'Test message', $title );

		$this->editPage( $title, $content, __METHOD__, NS_MAIN, $this->getTestUser()->getUser() );

		$handle = new MessageHandle( $title );
		$this->assertTrue( $handle->isValid(), 'Message is known' );
		$this->assertTrue( $handle->isFuzzy(), 'Message is fuzzy due to validation failure' );
	}
}
