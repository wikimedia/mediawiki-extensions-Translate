<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

use JobQueueGroup;
use LogicException;
use MediaWiki\Cache\LinkBatchFactory;
use MediaWiki\Extension\Translate\MessageGroupProcessing\MoveTranslatableBundleJob;
use MediaWiki\Extension\Translate\MessageGroupProcessing\SubpageListBuilder;
use MediaWiki\Extension\Translate\MessageGroupProcessing\TranslatableBundle;
use MediaWiki\Extension\Translate\MessageGroupProcessing\TranslatableBundleFactory;
use MediaWiki\Extension\Translate\SystemUsers\FuzzyBot;
use MediaWiki\Message\Message;
use MediaWiki\Page\MovePageFactory;
use MediaWiki\Status\Status;
use MediaWiki\Title\Title;
use MediaWiki\User\User;
use ObjectCacheFactory;
use SplObjectStorage;
use Wikimedia\ObjectCache\BagOStuff;
use Wikimedia\Rdbms\IConnectionProvider;

/**
 * Contains the core logic to validate and move translatable bundles
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 * @since 2021.03
 */
class TranslatableBundleMover {
	private const LOCK_TIMEOUT = 3600 * 2;
	private const FETCH_TRANSLATABLE_SUBPAGES = true;
	private MovePageFactory $movePageFactory;
	private ?int $pageMoveLimit;
	private JobQueueGroup $jobQueue;
	private LinkBatchFactory $linkBatchFactory;
	private TranslatableBundleFactory $bundleFactory;
	private SubpageListBuilder $subpageBuilder;
	private IConnectionProvider $dbProvider;
	private BagOStuff $cache;
	private bool $pageMoveLimitEnabled = true;

	private const REDIRECTABLE_PAGE_TYPES = [
		'pt-movepage-list-source' => true,
		'pt-movepage-list-section' => false,
		'pt-movepage-list-nonmovable' => false,
		'pt-movepage-list-translatable' => false,
		'pt-movepage-list-translation' => false,
		'pt-movepage-list-other' => true
	];

	public function __construct(
		MovePageFactory $movePageFactory,
		JobQueueGroup $jobQueue,
		LinkBatchFactory $linkBatchFactory,
		TranslatableBundleFactory $bundleFactory,
		SubpageListBuilder $subpageBuilder,
		IConnectionProvider $dbProvider,
		ObjectCacheFactory $objectCacheFactory,
		?int $pageMoveLimit
	) {
		$this->movePageFactory = $movePageFactory;
		$this->jobQueue = $jobQueue;
		$this->pageMoveLimit = $pageMoveLimit;
		$this->linkBatchFactory = $linkBatchFactory;
		$this->bundleFactory = $bundleFactory;
		$this->subpageBuilder = $subpageBuilder;
		$this->dbProvider = $dbProvider;
		$this->cache = $objectCacheFactory->getInstance( CACHE_ANYTHING );
	}

	public function getPageMoveCollection(
		Title $source,
		?Title $target,
		User $user,
		string $reason,
		bool $moveSubPages,
		bool $moveTalkPages,
		bool $leaveRedirect
	): PageMoveCollection {
		$blockers = new SplObjectStorage();

		if ( !$target ) {
			$blockers[$source] = Status::newFatal( 'pt-movepage-block-base-invalid' );
			throw new ImpossiblePageMove( $blockers );
		}

		if ( $target->inNamespaces( NS_MEDIAWIKI, NS_TRANSLATIONS ) ) {
			$blockers[$source] = Status::newFatal( 'immobile-target-namespace', $target->getNsText() );
			throw new ImpossiblePageMove( $blockers );
		}

		$movePage = $this->movePageFactory->newMovePage( $source, $target );
		$status = $movePage->isValidMove();
		$status->merge( $movePage->probablyCanMove( $user, $reason ) );
		if ( !$status->isOK() ) {
			$blockers[$source] = $status;
		}

		// Don't spam the same errors for all pages if base page fails
		if ( count( $blockers ) ) {
			throw new ImpossiblePageMove( $blockers );
		}

		$pageCollection = $this->getPagesToMove(
			$source, $target, $moveSubPages, self::FETCH_TRANSLATABLE_SUBPAGES, $moveTalkPages, $leaveRedirect
		);

		// Collect all the old and new titles for checks
		$titles = [
			'tp' => $pageCollection->getTranslationPagesPair(),
			'subpage' => $pageCollection->getSubpagesPair(),
			'section' => $pageCollection->getUnitPagesPair()
		];

		// Check that all new titles are valid and count them. Add 1 for source page.
		$moveCount = 1;
		$lb = $this->linkBatchFactory->newLinkBatch();
		foreach ( $titles as $type => $list ) {
			$moveCount += count( $list );
			// Give grep a chance to find the usages:
			// pt-movepage-block-tp-invalid, pt-movepage-block-section-invalid,
			// pt-movepage-block-subpage-invalid
			foreach ( $list as $pair ) {
				$old = $pair->getOldTitle();
				$new = $pair->getNewTitle();

				if ( $new === null ) {
					$blockers[$old] = $this->getRenameMoveBlocker( $old, $type, $pair->getRenameErrorCode() );
					continue;
				}
				$lb->addObj( $old );
				$lb->addObj( $new );
			}
		}

		if ( $this->pageMoveLimitEnabled ) {
			if ( $this->pageMoveLimit !== null && $moveCount > $this->pageMoveLimit ) {
				$blockers[$source] = Status::newFatal(
					'pt-movepage-page-count-limit',
					Message::numParam( $this->pageMoveLimit )
				);
			}
		}

		// Stop further validation if there are blockers already.
		if ( count( $blockers ) ) {
			throw new ImpossiblePageMove( $blockers );
		}

		// Check that there are no move blockers
		$lb->setCaller( __METHOD__ )->execute();
		foreach ( $titles as $type => $list ) {
			foreach ( $list as $pair ) {
				$old = $pair->getOldTitle();
				$new = $pair->getNewTitle();

				/* This method has terrible performance:
				 * - 2 queries by core
				 * - 3 queries by lqt
				 * - and no obvious way to preload the data! */
				$movePage = $this->movePageFactory->newMovePage( $old, $new );
				$status = $movePage->isValidMove();
				// Do not check for permissions here, as these pages are not editable/movable
				// in regular use
				if ( !$status->isOK() ) {
					$blockers[$old] = $status;
				}

				/* Because of the poor performance, check only one of the possibly thousands
				 * of section pages and assume rest are fine. This assumes section pages are
				 * listed last in the array. */
				if ( $type === 'section' ) {
					break;
				}
			}
		}

		if ( count( $blockers ) ) {
			throw new ImpossiblePageMove( $blockers );
		}

		return $pageCollection;
	}

	public function moveAsynchronously(
		Title $source,
		Title $target,
		bool $moveSubPages,
		User $user,
		string $moveReason,
		bool $moveTalkPages,
		bool $leaveRedirect,
		array $userSessionInfo
	): void {
		$pageCollection = $this->getPagesToMove(
			$source, $target, $moveSubPages, !self::FETCH_TRANSLATABLE_SUBPAGES, $moveTalkPages, $leaveRedirect
		);
		$pagesToMove = $pageCollection->getListOfPages();
		$pagesToLeaveRedirect = $pageCollection->getListOfPagesToRedirect();

		$job = MoveTranslatableBundleJob::newJob(
			$source,
			$target,
			$pagesToMove,
			$pagesToLeaveRedirect,
			$moveReason,
			$user,
			$userSessionInfo
		);

		$this->lock( array_keys( $pagesToMove ) );
		$this->lock( array_values( $pagesToMove ) );

		$this->jobQueue->push( $job );
	}

	/**
	 * @param Title $source
	 * @param Title $target
	 * @param string[] $pagesToMove
	 * @param array<string,bool> $pagesToRedirect
	 * @param User $performer
	 * @param string $moveReason
	 * @param ?callable $progressCallback
	 */
	public function moveSynchronously(
		Title $source,
		Title $target,
		array $pagesToMove,
		array $pagesToRedirect,
		User $performer,
		string $moveReason,
		?callable $progressCallback = null
	): void {
		$sourceBundle = $this->bundleFactory->getValidBundle( $source );

		$this->move( $sourceBundle, $performer, $pagesToMove, $pagesToRedirect, $moveReason, $progressCallback );

		$this->bundleFactory->getStore( $sourceBundle )->move( $source, $target );

		$this->bundleFactory->getPageMoveLogger( $sourceBundle )
			->logSuccess( $performer, $target, $moveReason );
	}

	public function disablePageMoveLimit(): void {
		$this->pageMoveLimitEnabled = false;
	}

	public function enablePageMoveLimit(): void {
		$this->pageMoveLimitEnabled = true;
	}

	public static function shouldLeaveRedirect( string $pageType, bool $leaveRedirect ): bool {
		return self::REDIRECTABLE_PAGE_TYPES[ $pageType ] && $leaveRedirect;
	}

	private function getPagesToMove(
		Title $source,
		Title $target,
		bool $moveSubPages,
		bool $fetchTranslatableSubpages,
		bool $moveTalkPages,
		bool $leaveRedirect
	): PageMoveCollection {
		$sourceBundle = $this->bundleFactory->getValidBundle( $source );

		$classifiedSubpages = $this->subpageBuilder->getSubpagesPerType( $sourceBundle, $moveTalkPages );

		$talkPages = $moveTalkPages ? $classifiedSubpages['talkPages'] : [];
		$subpages = $moveSubPages ? $classifiedSubpages['normalSubpages'] : [];
		$nonMovableSubpages = [];

		$relatedTranslatablePageList = [];
		if ( $fetchTranslatableSubpages ) {
			$relatedTranslatablePageList = array_merge(
				$classifiedSubpages['translatableSubpages'],
				$classifiedSubpages['translatableTalkPages']
			);
		}

		$movePageFactory = $this->movePageFactory;
		$pageTitleRenamer = new PageTitleRenamer( $source, $target );
		$createOps = function ( array $pages, string $pageType )
			use ( $pageTitleRenamer, $talkPages, &$nonMovableSubpages, $leaveRedirect, $movePageFactory ) {
			$leaveRedirect = self::shouldLeaveRedirect( $pageType, $leaveRedirect );
			$ops = [];
			foreach ( $pages as $from ) {
				$to = $pageTitleRenamer->getNewTitle( $from );
				$op = new PageMoveOperation( $from, $to );

				if ( $pageType === 'pt-movepage-list-other' ) {
					// TODO: In the future, think of moving all checks regarding whether a page
					// is movable into this method. Currently its' being checked in two places
					// making things slow.
					$movePage = $movePageFactory->newMovePage( $from, $to );
					$status = $movePage->isValidMove();

					// Remove non movable subpages
					if ( !$status->isOK() ) {
						$nonMovableSubpages[ $from->getPrefixedText() ] = $status;
						continue;
					}
				}

				$op->setLeaveRedirect( $leaveRedirect );

				$talkPage = $talkPages[ $from->getPrefixedDBkey() ] ?? null;
				if ( $talkPage ) {
					$op->setTalkpage( $talkPage, $pageTitleRenamer->getNewTitle( $talkPage ) );
				}
				$ops[] = $op;
			}

				return $ops;
		};

		return new PageMoveCollection(
			$createOps( [ $source ], 'pt-movepage-list-source' )[0],
			$createOps( $classifiedSubpages['translationPages'], 'pt-movepage-list-translation' ),
			$createOps( $classifiedSubpages['translationUnitPages'], 'pt-movepage-list-section' ),
			$createOps( $subpages, 'pt-movepage-list-other' ),
			$nonMovableSubpages,
			$relatedTranslatablePageList
		);
	}

	/** @param string[] $titles */
	private function lock( array $titles ): void {
		$data = [];
		foreach ( $titles as $title ) {
			$data[$this->cache->makeKey( 'pt-lock', sha1( $title ) )] = 'locked';
		}

		// Do not lock pages indefinitely during translatable page moves since
		// they can fail. Add a timeout so that the locks expire by themselves.
		// Timeout value has been chosen by a gut feeling
		$this->cache->setMulti( $data, self::LOCK_TIMEOUT );
	}

	/** @param string[] $titles */
	private function unlock( array $titles ): void {
		foreach ( $titles as $title ) {
			$this->cache->delete( $this->cache->makeKey( 'pt-lock', sha1( $title ) ) );
		}
	}

	/**
	 * @param TranslatableBundle $sourceBundle
	 * @param User $performer
	 * @param string[] $pagesToMove
	 * @param array<string,bool> $pagesToRedirect
	 * @param string $reason
	 * @param ?callable $progressCallback
	 */
	private function move(
		TranslatableBundle $sourceBundle,
		User $performer,
		array $pagesToMove,
		array $pagesToRedirect,
		string $reason,
		?callable $progressCallback = null
	): void {
		$fuzzyBot = FuzzyBot::getUser();

		Hooks::$allowTargetEdit = true;

		$processed = 0;

		$this->dbProvider->getPrimaryDatabase()->startAtomic( __METHOD__ );
		foreach ( $pagesToMove as $source => $target ) {
			$sourceTitle = Title::newFromText( $source );
			$targetTitle = Title::newFromText( $target );

			if ( $source === $sourceBundle->getTitle()->getPrefixedText() ) {
				$user = $performer;
				$moveSummary = $reason;
			} else {
				$user = $fuzzyBot;
				$moveSummary = wfMessage(
					'pt-movepage-logreason', $sourceBundle->getTitle()->getPrefixedText()
				)->text();
			}

			$mover = $this->movePageFactory->newMovePage( $sourceTitle, $targetTitle );
			$status = $mover->move( $user, $moveSummary, $pagesToRedirect[$source] ?? false );
			$processed++;

			if ( $progressCallback ) {
				$progressCallback(
					$sourceTitle,
					$targetTitle,
					$status,
					count( $pagesToMove ),
					$processed
				);
			}

			if ( !$status->isOK() ) {
				$this->bundleFactory->getPageMoveLogger( $sourceBundle )
					->logError( $performer, $sourceTitle, $targetTitle, $status );
			}

			$this->unlock( [ $source, $target ] );
		}
		$this->dbProvider->getPrimaryDatabase()->endAtomic( __METHOD__ );

		Hooks::$allowTargetEdit = false;
	}

	private function getRenameMoveBlocker( Title $old, string $pageType, int $renameError ): Status {
		if ( $renameError === PageTitleRenamer::NO_ERROR ) {
			throw new LogicException(
				'Trying to fetch MoveBlocker when there was no error during rename. Title: ' .
				$old->getPrefixedText() . ', page type: ' . $pageType
			);
		}

		if ( $renameError === PageTitleRenamer::UNKNOWN_PAGE ) {
			$status = Status::newFatal( 'pt-movepage-block-unknown-page', $old->getPrefixedText() );
		} elseif ( $renameError === PageTitleRenamer::NS_TALK_UNSUPPORTED ) {
			$status = Status::newFatal( 'pt-movepage-block-ns-talk-unsupported', $old->getPrefixedText() );
		} elseif ( $renameError === PageTitleRenamer::RENAME_FAILED ) {
			$status = Status::newFatal( 'pt-movepage-block-rename-failed', $old->getPrefixedText() );
		} else {
			return Status::newFatal( "pt-movepage-block-$pageType-invalid", $old->getPrefixedText() );
		}

		return $status;
	}
}
