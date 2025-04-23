<?php
/**
 * Tests for transver change on edits.
 * @author Perry Olum
 * @file
 * @license GPL-2.0-or-later
 */

use MediaWiki\CommentStore\CommentStoreComment;
use MediaWiki\Content\ContentHandler;
use MediaWiki\Extension\Translate\MessageGroupProcessing\RevTagStore;
use MediaWiki\Extension\Translate\MessageLoading\MessageHandle;
use MediaWiki\Extension\Translate\Services;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\Revision\SlotRecord;
use MediaWiki\Storage\EditResult;
use MediaWiki\Title\Title;
use MediaWiki\User\UserIdentity;

/**
 * Tests for transver flag change on edits.
 * @group Database
 * @group medium
 * @covers MediaWiki\Extension\Translate\TranslatorInterface\TranslateEditAddons
 */
class TranslationTransverUpdaterTest extends MediaWikiIntegrationTestCase {
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
		$revertMethod,
		$revertedId
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

	public function testTransver() {
		// Setup
		$user = $this->getTestUser()->getUser();
		$container = $this->getServiceContainer();
		$revTagStore = Services::getInstance()->getRevTagStore();
		// Need to have permissions to edit and unfuzzy the page or else transver stuff will
		// get skipped
		$container->getPermissionManager()->overrideUserRightsForTesting( $user,
			[ 'editinterface', 'unfuzzy' ]
		);

		// Transver only makes sense if the source page exists
		$sourceTitle = Title::newFromText( 'MediaWiki:Ugakey/en' );
		$sourcePage = $container->getWikiPageFactory()->newFromTitle( $sourceTitle );
		$firstSourceRev = $this->saveRevision( $sourcePage, '$1 of $2', $user );

		// Now create the translation
		$translTitle = Title::newFromText( 'MediaWiki:Ugakey/nl' );
		$handle = new MessageHandle( $translTitle );
		$translPage = $container->getWikiPageFactory()->newFromTitle( $translTitle );
		$this->saveRevision( $translPage, '$1 van $2', $user );
		$this->assertEquals( $firstSourceRev->getId(), $revTagStore->getTransver( $translPage ) );

		// Make an edit to the source page. Note that this does not run Translate's code to
		// fuzzy the translation, because that's done in UpdateMessageJob (and tested in UpdateMessageJobTest)
		$secondSourceRev = $this->saveRevision( $sourcePage, '$1 of $2 new', $user );
		$this->assertEquals( $firstSourceRev->getId(), $revTagStore->getTransver( $translPage ) );

		// Fuzzy translations don't update the transver
		$this->saveRevision( $translPage, '!!FUZZY!!$1 van $2 new', $user );
		$this->assertEquals( $firstSourceRev->getId(), $revTagStore->getTransver( $translPage ) );

		// Non-fuzzy translations do
		$thirdTransRev = $this->saveRevision( $translPage, '$1 van $2 new', $user );
		$this->assertEquals( $secondSourceRev->getId(), $revTagStore->getTransver( $translPage ) );

		// Now make another edit to the source text
		$thirdSourceRev = $this->saveRevision( $sourcePage, '$1 of $2 new 2', $user );
		$this->fuzzyRevision( $thirdTransRev );

		// ... pretend this edit is done by a vandal on a wiki that doesn't restrict unfuzzy
		$fourthTransRev = $this->saveRevision( $translPage, "$1 van 2 xxx", $user );
		$this->assertFalse( $handle->isFuzzy() );
		$this->assertEquals( $thirdSourceRev->getId(), $revTagStore->getTransver( $translPage ) );

		// And then revert it
		$firstRevert = $this->saveRevert(
			$translPage, '$1 van $2 new',
			$user, EditResult::REVERT_UNDO,
			$fourthTransRev->getId()
		);
		$this->assertTrue( $handle->isFuzzy() );
		$this->assertEquals( $secondSourceRev->getId(), $revTagStore->getTransver( $translPage ) );

		// Repeat that test with rollback
		$fifthTransRev = $this->saveRevision( $translPage, "$1 van 2 yyy", $user );
		$secondRevert = $this->saveRevert(
			$translPage, '$1 van $2 new',
			$user, EditResult::REVERT_ROLLBACK,
			$fifthTransRev->getId()
		);
		$this->assertTrue( $handle->isFuzzy() );
		$this->assertEquals( $secondSourceRev->getId(), $revTagStore->getTransver( $translPage ) );

		$fourthSourceRev = $this->saveRevision( $sourcePage, '$1 of $2 new 3', $user );

		// Now revert to the unfuzzy version
		$thirdRevert = $this->saveRevert(
			$translPage, '$1 van 2 yyy',
			$user, EditResult::REVERT_UNDO,
			$secondRevert->getId()
		);
		$this->assertFalse( $handle->isFuzzy() );
		$this->assertEquals( $fourthSourceRev->getId(), $revTagStore->getTransver( $translPage ) );

		// And back
		$fourthRevert = $this->saveRevert(
			$translPage, '$1 van $2 new',
			$user, EditResult::REVERT_UNDO,
			$thirdRevert->getId()
		);
		$this->assertTrue( $handle->isFuzzy() );
		$this->assertEquals( $secondSourceRev->getId(), $revTagStore->getTransver( $translPage ) );

		// Manual reverts are treated like ordinary edits
		$sixthTransRev = $this->saveRevision( $translPage, '$1 van $2 zzz', $user );
		$thirdRevert = $this->saveRevert(
			$translPage, '$1 van $2 new',
			$user, EditResult::REVERT_MANUAL,
			$sixthTransRev->getId()
		);
		$this->assertEquals( $fourthSourceRev->getId(), $revTagStore->getTransver( $translPage ) );

		// Make sure that transvers are not set if a revision is fuzzy for some other reason like lacking permission
		$this->fuzzyRevision( $thirdRevert );
		$container->getPermissionManager()->overrideUserRightsForTesting( $user, [] );
		$this->saveRevision( $translPage, '$1 van $2 aaa', $user );
		$this->saveRevert( $translPage, '$1 van $2 new', $user, EditResult::REVERT_UNDO, $sixthTransRev->getId() );
		$this->assertEquals( $fourthSourceRev->getId(), $revTagStore->getTransver( $translPage ) );
		$this->assertTrue( $handle->isFuzzy() );
	}
}
