<?php
/**
 * Tests for fuzzy flag change on edits.
 * @author Niklas Laxström
 * @file
 * @license GPL-2.0-or-later
 */

use MediaWiki\Extension\Translate\MessageGroupProcessing\RevTagStore;
use MediaWiki\Extension\Translate\MessageLoading\MessageHandle;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\Revision\SlotRecord;
use MediaWiki\Title\Title;

/**
 * Tests for fuzzy flag change on edits.
 * @group Database
 * @group medium
 * @covers MediaWiki\Extension\Translate\TranslatorInterface\TranslateEditAddons
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

	private function saveRevision( WikiPage $page, string $content, User $user ): ?RevisionRecord {
		$contentObj = ContentHandler::makeContent( $content, $page->getTitle() );
		$updater = $page->newPageUpdater( $user )->setContent( SlotRecord::MAIN, $contentObj );
		$revisionRecord = $updater->saveRevision( CommentStoreComment::newUnsavedComment( __METHOD__ ) );
		$this->assertTrue( $updater->wasSuccessful(), "Saving edit should have suceeded" );
		return $revisionRecord;
	}

	/** @return int|false */
	private function getLastLogId( Title $title ) {
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

		$dbw = $this->getDb();
		$conds = [
			'rt_page' => $title->getArticleID(),
			'rt_type' => RevTagStore::FUZZY_TAG,
			'rt_revision' => $originalRevisionRecord->getId()
		];

		$index = array_keys( $conds );
		$dbw->replace( 'revtag', [ $index ], $conds, __METHOD__ );

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

		// Now add the required rights and try null-edit unfuzzy again
		$rightsCallback = $permissionManager->addTemporaryUserRights( $user,
			[ 'editinterface', 'unfuzzy' ]
		);
		$thirdRevisionRecord = $this->saveRevision( $page, '$1 van $2', $user );
		$this->assertFalse(
			$handle->isFuzzy(),
			'Message is no longer fuzzy after null edit with required permissions'
		);
		// This is (from core's POV) a null edit so no revision was created
		$this->assertNull( $thirdRevisionRecord, 'Null edit should not have been saved' );
		// But a null revision was created by Translate
		$this->assertNotEquals(
			$firstRevisionRecord->getId(),
			$container->getRevisionStore()->getRevisionByPageId( $page->getId() )->getId(),
			'Unfuzzying translation without changes should have created a null revision'
		);
		// And a log entry
		$newLastLogId = $this->getLastLogId( $title );
		$this->assertNotEquals(
			$newLastLogId,
			$oldLastLogId,
			'Unfuzzying translation without changes should have created a log entry'
		);

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

	public function testValidationFuzzy() {
		$user = $this->getTestUser()->getUser();
		$title = Title::newFromText( 'MediaWiki:nlkey/en-gb' );
		$page = $this->getServiceContainer()->getWikiPageFactory()->newFromTitle( $title );
		$content = ContentHandler::makeContent( 'Test message', $title );
		$page->doUserEditContent( $content, $user, __METHOD__ );

		$handle = new MessageHandle( $title );
		$this->assertTrue( $handle->isValid(), 'Message is known' );
		$this->assertTrue( $handle->isFuzzy(), 'Message is fuzzy due to validation failure' );
	}
}
