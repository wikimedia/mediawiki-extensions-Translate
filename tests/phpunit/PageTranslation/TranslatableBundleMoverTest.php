<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

use MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroups;
use MediaWiki\Extension\Translate\Services;
use MediaWiki\MediaWikiServices;
use MediaWiki\Status\Status;
use MediaWiki\Title\Title;
use MediaWikiIntegrationTestCase;
use MessageGroupTestTrait;
use TranslatablePageTestTrait;

/**
 * @covers \MediaWiki\Extension\Translate\PageTranslation\TranslatableBundleMover
 * @group Database
 * @group medium
 */
class TranslatableBundleMoverTest extends MediaWikiIntegrationTestCase {
	use MessageGroupTestTrait;
	use TranslatablePageTestTrait;

	private TranslatableBundleMover $bundleMover;

	protected function setUp(): void {
		parent::setUp();
		$this->setupGroupTestEnvironment( $this );
		$this->setGroupPermissions( 'sysop', 'pagetranslation', true );

		$this->bundleMover = Services::getInstance()->getTranslatableBundleMover();
		$this->bundleMover->disablePageMoveLimit();
	}

	public function testMoveSynchronouslyMovesAllPages(): void {
		$user = $this->getTestSysop()->getUser();
		$sourceTitle = Title::newFromText( 'MoveTestSource' );
		$targetTitle = Title::newFromText( 'MoveTestTarget' );

		$this->createMarkedTranslatablePage(
			$sourceTitle->getPrefixedText(),
			"Unit one\n\nUnit two",
			$user
		);
		MessageGroups::singleton()->recache();

		$pageCollection = $this->bundleMover->getPageMoveCollection(
			source: $sourceTitle,
			target: $targetTitle,
			user: $user,
			reason: 'test move',
			moveSubPages: true,
			moveTalkPages: true,
			leaveRedirect: false
		);

		$pagesToMove = $pageCollection->getListOfPages();
		$pagesToRedirect = $pageCollection->getListOfPagesToRedirect();

		$this->assertArrayHasKey(
			$sourceTitle->getPrefixedText(),
			$pagesToMove,
			'Source page must be in the list of pages to move'
		);

		$this->bundleMover->moveSynchronously(
			source: $sourceTitle,
			target: $targetTitle,
			pagesToMove: $pagesToMove,
			pagesToRedirect: $pagesToRedirect,
			performer: $user,
			moveReason: 'test move'
		);

		// Use fresh Title objects to avoid stale article ID caches
		$this->assertFalse(
			Title::newFromText( 'MoveTestSource' )->exists(),
			'Source page must not exist after move without redirect'
		);
		$this->assertTrue(
			Title::newFromText( 'MoveTestTarget' )->exists(),
			'Target page must exist after move'
		);
	}

	public function testMoveSynchronouslyCallsProgressCallback(): void {
		$user = $this->getTestSysop()->getUser();
		$sourceTitle = Title::newFromText( 'MoveCallbackSource' );
		$targetTitle = Title::newFromText( 'MoveCallbackTarget' );

		$this->createMarkedTranslatablePage(
			$sourceTitle->getPrefixedText(),
			'Content here',
			$user
		);
		MessageGroups::singleton()->recache();

		$pageCollection = $this->bundleMover->getPageMoveCollection(
			source: $sourceTitle,
			target: $targetTitle,
			user: $user,
			reason: 'test move',
			moveSubPages: true,
			moveTalkPages: true,
			leaveRedirect: true
		);

		$pagesToMove = $pageCollection->getListOfPages();
		$pagesToRedirect = $pageCollection->getListOfPagesToRedirect();

		$callbackInvocations = [];
		$callback = static function (
			Title $old,
			Title $new,
			Status $status,
			int $total,
			int $processed
		) use ( &$callbackInvocations ) {
			$callbackInvocations[] = [
				'old' => $old->getPrefixedText(),
				'new' => $new->getPrefixedText(),
				'status' => $status->isOK(),
				'total' => $total,
				'processed' => $processed,
			];
		};

		$this->bundleMover->moveSynchronously(
			source: $sourceTitle,
			target: $targetTitle,
			pagesToMove: $pagesToMove,
			pagesToRedirect: $pagesToRedirect,
			performer: $user,
			moveReason: 'test move',
			progressCallback: $callback
		);

		$this->assertSameSize(
			$pagesToMove,
			$callbackInvocations,
			'Progress callback must be invoked once per page moved'
		);

		$this->assertSame(
			1,
			$callbackInvocations[0]['processed'],
			'First callback invocation must have processed = 1'
		);

		$this->assertSame(
			count( $pagesToMove ),
			$callbackInvocations[0]['total'],
			'Callback must receive the total number of pages'
		);

		foreach ( $callbackInvocations as $invocation ) {
			$this->assertTrue(
				$invocation['status'],
				"Move of {$invocation['old']} must succeed"
			);
		}
	}

	public function testMoveAsynchronouslyAcquiresLocks(): void {
		$user = $this->getTestSysop()->getUser();
		$sourceTitle = Title::newFromText( 'MoveLockAsyncSource' );
		$targetTitle = Title::newFromText( 'MoveLockAsyncTarget' );

		$this->createMarkedTranslatablePage(
			$sourceTitle->getPrefixedText(),
			'Lock test content',
			$user
		);
		MessageGroups::singleton()->recache();

		$this->bundleMover->moveAsynchronously(
			source: $sourceTitle,
			target: $targetTitle,
			moveSubPages: true,
			user: $user,
			moveReason: 'test move',
			moveTalkPages: true,
			leaveRedirect: true,
			userSessionInfo: []
		);

		$cache = MediaWikiServices::getInstance()->getObjectCacheFactory()->getInstance( CACHE_ANYTHING );
		$sourceKey = $cache->makeKey( 'pt-lock', sha1( $sourceTitle->getPrefixedText() ) );
		$targetKey = $cache->makeKey( 'pt-lock', sha1( $targetTitle->getPrefixedText() ) );

		$this->assertSame(
			'locked',
			$cache->get( $sourceKey ),
			'Source page must be locked after moveAsynchronously'
		);
		$this->assertSame(
			'locked',
			$cache->get( $targetKey ),
			'Target page must be locked after moveAsynchronously'
		);
	}

	public function testMoveSynchronouslyAcquiresLocks(): void {
		$user = $this->getTestSysop()->getUser();
		$sourceTitle = Title::newFromText( 'MoveLockSyncSource' );
		$targetTitle = Title::newFromText( 'MoveLockSyncTarget' );

		$this->createMarkedTranslatablePage(
			$sourceTitle->getPrefixedText(),
			'Lock test content',
			$user
		);
		MessageGroups::singleton()->recache();

		$pageCollection = $this->bundleMover->getPageMoveCollection(
			source: $sourceTitle,
			target: $targetTitle,
			user: $user,
			reason: 'test move',
			moveSubPages: true,
			moveTalkPages: true,
			leaveRedirect: false
		);

		$cache = MediaWikiServices::getInstance()->getObjectCacheFactory()->getInstance( CACHE_ANYTHING );
		$targetKey = $cache->makeKey( 'pt-lock', sha1( $targetTitle->getPrefixedText() ) );

		$this->assertFalse(
			$cache->get( $targetKey ),
			'Target page must not be locked before moveSynchronously'
		);

		// Use the progress callback to observe that locks are held during the move
		$lockObservedDuringMove = false;
		$callback = static function () use ( $cache, $targetKey, &$lockObservedDuringMove ) {
			if ( $cache->get( $targetKey ) === 'locked' ) {
				$lockObservedDuringMove = true;
			}
		};

		$this->bundleMover->moveSynchronously(
			source: $sourceTitle,
			target: $targetTitle,
			pagesToMove: $pageCollection->getListOfPages(),
			pagesToRedirect: $pageCollection->getListOfPagesToRedirect(),
			performer: $user,
			moveReason: 'test move',
			progressCallback: $callback
		);

		$this->assertTrue(
			$lockObservedDuringMove,
			'Target page must be locked during moveSynchronously'
		);
	}

	public function testMoveSynchronouslyContinuesOnIndividualFailure(): void {
		$user = $this->getTestSysop()->getUser();
		$sourceTitle = Title::newFromText( 'MoveFailSource' );
		$targetTitle = Title::newFromText( 'MoveFailTarget' );

		$this->createMarkedTranslatablePage(
			$sourceTitle->getPrefixedText(),
			'Some content',
			$user
		);
		MessageGroups::singleton()->recache();

		$pageCollection = $this->bundleMover->getPageMoveCollection(
			source: $sourceTitle,
			target: $targetTitle,
			user: $user,
			reason: 'test move',
			moveSubPages: true,
			moveTalkPages: true,
			leaveRedirect: true
		);

		$pagesToMove = $pageCollection->getListOfPages();
		$pagesToRedirect = $pageCollection->getListOfPagesToRedirect();

		// Inject a non-existent page into the move list to trigger a failure
		$pagesToMove['Page that does not exist at all'] = 'Page that does not exist target';

		$failedMoves = [];
		$successfulMoves = [];
		$callback = static function (
			Title $old,
			Title $new,
			Status $status,
			int $total,
			int $processed
		) use ( &$failedMoves, &$successfulMoves ) {
			if ( $status->isOK() ) {
				$successfulMoves[] = $old->getPrefixedText();
			} else {
				$failedMoves[] = $old->getPrefixedText();
			}
		};

		$this->bundleMover->moveSynchronously(
			source: $sourceTitle,
			target: $targetTitle,
			pagesToMove: $pagesToMove,
			pagesToRedirect: $pagesToRedirect,
			performer: $user,
			moveReason: 'test move',
			progressCallback: $callback
		);

		$this->assertContains(
			'Page that does not exist at all',
			$failedMoves,
			'Non-existent page must fail to move'
		);

		$this->assertNotEmpty(
			$successfulMoves,
			'Other pages must still be moved despite the failure'
		);

		$this->assertTrue(
			Title::newFromText( 'MoveFailTarget' )->exists(),
			'Target page must exist even when some subpage moves fail'
		);
	}
}
