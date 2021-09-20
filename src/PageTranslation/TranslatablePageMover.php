<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

use AggregateMessageGroup;
use JobQueueGroup;
use LogicException;
use ManualLogEntry;
use MediaWiki\Cache\LinkBatchFactory;
use MediaWiki\Extension\Translate\SystemUsers\FuzzyBot;
use MediaWiki\Page\MovePageFactory;
use Message;
use MessageGroups;
use MessageIndex;
use ObjectCache;
use PageTranslationHooks;
use SplObjectStorage;
use Status;
use Title;
use TranslatablePage;
use TranslatablePageMoveJob;
use TranslateMetadata;
use TranslateUtils;
use TranslationsUpdateJob;
use Traversable;
use User;

/**
 * Contains the core logic to validate and move translatable pages
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 * @since 2021.03
 */
class TranslatablePageMover {
	private const LOCK_TIMEOUT = 3600 * 2;
	private const FETCH_TRANSLATABLE_SUBPAGES = true;
	/** @var MovePageFactory */
	private $movePageFactory;
	/** @var int|null */
	private $pageMoveLimit;
	/** @var JobQueueGroup */
	private $jobQueue;
	/** @var LinkBatchFactory */
	private $linkBatchFactory;
	/** @var bool */
	private $pageMoveLimitEnabled = true;

	public function __construct(
		MovePageFactory $movePageFactory,
		JobQueueGroup $jobQueue,
		LinkBatchFactory $linkBatchFactory,
		?int $pageMoveLimit
	) {
		$this->movePageFactory = $movePageFactory;
		$this->jobQueue = $jobQueue;
		$this->pageMoveLimit = $pageMoveLimit;
		$this->linkBatchFactory = $linkBatchFactory;
	}

	public function getPageMoveCollection(
		Title $source,
		?Title $target,
		User $user,
		string $reason,
		bool $moveSubPages,
		bool $moveTalkPages
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
		$status->merge( $movePage->checkPermissions( $user, $reason ) );
		if ( !$status->isOK() ) {
			$blockers[$source] = $status;
		}

		// Don't spam the same errors for all pages if base page fails
		if ( count( $blockers ) ) {
			throw new ImpossiblePageMove( $blockers );
		}

		$pageCollection = $this->getPagesToMove(
			$source, $target, $moveSubPages, self::FETCH_TRANSLATABLE_SUBPAGES, $moveTalkPages
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
		string $summary,
		bool $moveTalkPages
	): void {
		$pageCollection = $this->getPagesToMove(
			$source, $target, $moveSubPages, !self::FETCH_TRANSLATABLE_SUBPAGES, $moveTalkPages
		);
		$pagesToMove = $pageCollection->getListOfPages();

		$job = TranslatablePageMoveJob::newJob( $source, $target, $pagesToMove, $summary, $user );
		$this->lock( array_keys( $pagesToMove ) );
		$this->lock( array_values( $pagesToMove ) );

		$this->jobQueue->push( $job );
	}

	/**
	 * @param Title $source
	 * @param Title $target
	 * @param string[] $pagesToMove
	 * @param User $performer
	 * @param string $summary
	 * @return void
	 */
	public function moveSynchronously(
		Title $source,
		Title $target,
		array $pagesToMove,
		User $performer,
		string $summary,
		callable $progressCallback = null
	): void {
		$this->move( $source, $performer, $pagesToMove, $summary, $progressCallback );

		$sourcePage = TranslatablePage::newFromTitle( $source );
		$targetPage = TranslatablePage::newFromTitle( $target );

		$entry = new ManualLogEntry( 'pagetranslation', 'moveok' );
		$entry->setPerformer( $performer );
		$entry->setTarget( $source );
		$entry->setParameters( [ 'target' => $target->getPrefixedText() ] );
		$logid = $entry->insert();
		$entry->publish( $logid );

		$this->moveMetadata( $sourcePage->getMessageGroupId(), $targetPage->getMessageGroupId() );

		TranslatablePage::clearSourcePageCache();

		// Re-render the pages to get everything in sync
		MessageGroups::singleton()->recache();
		// Update message index now so that, when after this job the MoveTranslationUnits hook
		// runs in deferred updates, it will not run MessageIndexRebuildJob (T175834).
		MessageIndex::singleton()->rebuild();

		$job = TranslationsUpdateJob::newFromPage( $targetPage );
		$this->jobQueue->push( $job );
	}

	public function disablePageMoveLimit(): void {
		$this->pageMoveLimitEnabled = false;
	}

	public function enablePageMoveLimit(): void {
		$this->pageMoveLimitEnabled = true;
	}

	private function getPagesToMove(
		Title $source,
		Title $target,
		bool $moveSubPages,
		bool $fetchTranslatableSubpages,
		bool $moveTalkPages
	): PageMoveCollection {
		$page = TranslatablePage::newFromTitle( $source );
		$translatableMovePage = new PageMoveOperation( $source, $target );
		$pageTitleRenamer = new PageTitleRenamer( $source, $target );

		$translationPageList = [];
		foreach ( $page->getTranslationPages() as $from ) {
			$translationPageList[] = $this->createPageMoveOperation( $pageTitleRenamer, $from );
		}

		$translationUnitPageList = [];
		foreach ( $page->getTranslationUnitPages( 'all' ) as $from ) {
			$translationUnitPageList[] = $this->createPageMoveOperation( $pageTitleRenamer, $from );
		}

		$subpageList = [];
		if ( $moveSubPages && TranslateUtils::allowsSubpages( $source ) ) {
			$currentSubpages = $this->getNormalSubpages( $page );
			foreach ( $currentSubpages as $from ) {
				$subpageList[] = $this->createPageMoveOperation( $pageTitleRenamer, $from );
			}
		}

		$translatableTalkpageList = [];
		// If the source page is a talk page itself, no point looking for more talk pages
		if ( $moveTalkPages && !$source->isTalkPage() ) {
			$possiblePagesToBeMoved = array_merge(
				[ $translatableMovePage ],
				$translationPageList,
				$translationUnitPageList,
				$subpageList
			);

			$talkPages = $this->getTalkPagesForMove( $possiblePagesToBeMoved );
			foreach ( $possiblePagesToBeMoved as $index => $pageOperation ) {
				$currentTalkPage = $talkPages[$index] ?? null;
				if ( $currentTalkPage === null ) {
					continue;
				}

				// If the talk page is translatable, we do not move it, and inform the user
				// that this needs to be moved separately.
				if ( TranslatablePage::isSourcePage( $currentTalkPage ) ) {
					$translatableTalkpageList[] = $currentTalkPage;
					continue;
				}

				$pageOperation->setTalkpage(
					$currentTalkPage, $pageTitleRenamer->getNewTitle( $currentTalkPage )
				);
			}
		}

		$relatedTranslatablePageList = $translatableTalkpageList;
		if ( $fetchTranslatableSubpages ) {
			$relatedTranslatablePageList = array_merge(
				$relatedTranslatablePageList,
				$this->getTranslatableSubpages( TranslatablePage::newFromTitle( $source ) )
			);
		}

		return new PageMoveCollection(
			$translatableMovePage,
			$translationPageList,
			$translationUnitPageList,
			$subpageList,
			$relatedTranslatablePageList
		);
	}

	/** @return Title[] */
	private function getNormalSubpages( TranslatablePage $page ): array {
		return array_filter(
			$this->getSubpages( $page ),
			static function ( $page ) {
				return !(
					TranslatablePage::isTranslationPage( $page ) ||
					TranslatablePage::isSourcePage( $page )
				);
			}
		);
	}

	/** @return Title[] */
	private function getTranslatableSubpages( TranslatablePage $page ): array {
		return array_filter(
			$this->getSubpages( $page ),
			static function ( $page ) {
				return TranslatablePage::isSourcePage( $page );
			}
		);
	}

	/**
	 * Returns all subpages, if the namespace has them enabled.
	 * @return Title[]
	 */
	private function getSubpages( TranslatablePage $page ): array {
		$pages = $page->getTitle()->getSubpages();
		if ( $pages instanceof Traversable ) {
			$pages = iterator_to_array( $pages );
		}

		return $pages;
	}

	/** @param string[] $titles */
	private function lock( array $titles ): void {
		$cache = ObjectCache::getInstance( CACHE_ANYTHING );
		$data = [];
		foreach ( $titles as $title ) {
			$data[$cache->makeKey( 'pt-lock', sha1( $title ) )] = 'locked';
		}

		// Do not lock pages indefinitely during translatable page moves since
		// they can fail. Add a timeout so that the locks expire by themselves.
		// Timeout value has been chosen by a gut feeling
		$cache->setMulti( $data, self::LOCK_TIMEOUT );
	}

	/** @param string[] $titles */
	private function unlock( array $titles ): void {
		$cache = ObjectCache::getInstance( CACHE_ANYTHING );
		foreach ( $titles as $title ) {
			$cache->delete( $cache->makeKey( 'pt-lock', sha1( $title ) ) );
		}
	}

	/**
	 * @param Title $baseSource
	 * @param User $performer
	 * @param string[] $pagesToMove
	 * @param string $summary
	 * @param callable|null $progressCallback
	 * @return void
	 */
	private function move(
		Title $baseSource,
		User $performer,
		array $pagesToMove,
		string $summary,
		callable $progressCallback = null
	): void {
		$fuzzybot = FuzzyBot::getUser();

		PageTranslationHooks::$allowTargetEdit = true;

		$processed = 0;
		foreach ( $pagesToMove as $source => $target ) {
			$sourceTitle = Title::newFromText( $source );
			$targetTitle = Title::newFromText( $target );

			if ( $source === $baseSource->getPrefixedText() ) {
				$user = $performer;
			} else {
				$user = $fuzzybot;
			}

			$mover = $this->movePageFactory->newMovePage( $sourceTitle, $targetTitle );
			$status = $mover->move( $user, $summary, false );
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
				$entry = new ManualLogEntry( 'pagetranslation', 'movenok' );
				$entry->setPerformer( $performer );
				$entry->setTarget( $sourceTitle );
				$entry->setParameters( [
					'target' => $target,
					'error' => $status->getErrorsArray(),
				] );
				$logid = $entry->insert();
				$entry->publish( $logid );
			}

			$this->unlock( [ $source, $target ] );
		}

		PageTranslationHooks::$allowTargetEdit = false;
	}

	private function moveMetadata( string $oldGroupId, string $newGroupId ): void {
		TranslateMetadata::preloadGroups( [ $oldGroupId, $newGroupId ], __METHOD__ );
		foreach ( TranslatablePage::METADATA_KEYS as $type ) {
			$value = TranslateMetadata::get( $oldGroupId, $type );
			if ( $value !== false ) {
				TranslateMetadata::set( $oldGroupId, $type, false );
				TranslateMetadata::set( $newGroupId, $type, $value );
			}
		}

		// Make the changes in aggregate groups metadata, if present in any of them.
		$aggregateGroups = MessageGroups::getGroupsByType( AggregateMessageGroup::class );
		TranslateMetadata::preloadGroups( array_keys( $aggregateGroups ), __METHOD__ );

		foreach ( $aggregateGroups as $id => $group ) {
			$subgroups = TranslateMetadata::get( $id, 'subgroups' );
			if ( $subgroups === false ) {
				continue;
			}

			$subgroups = explode( ',', $subgroups );
			$subgroups = array_flip( $subgroups );
			if ( isset( $subgroups[$oldGroupId] ) ) {
				$subgroups[$newGroupId] = $subgroups[$oldGroupId];
				unset( $subgroups[$oldGroupId] );
				$subgroups = array_flip( $subgroups );
				TranslateMetadata::set(
					$group->getId(),
					'subgroups',
					implode( ',', $subgroups )
				);
			}
		}

		// Move discouraged status
		$priority = MessageGroups::getPriority( $oldGroupId );
		if ( $priority !== '' ) {
			MessageGroups::setPriority( $newGroupId, $priority );
			MessageGroups::setPriority( $oldGroupId, '' );
		}
	}

	/**
	 * To identify the talk pages, we first gather the possible talk pages into
	 * and then check that they exist. Title::exists perform a database check so
	 * we gather them into LinkBatch to reduce the performance impact.
	 * @param PageMoveOperation[] $pageMoveOperations
	 * @return Title[]
	 */
	private function getTalkPagesForMove( array $pageMoveOperations ): array {
		$lb = $this->linkBatchFactory->newLinkBatch();
		$talkPageList = [];

		foreach ( $pageMoveOperations as $pageOperation ) {
			$talkPage = $pageOperation->getOldTitle()->getTalkPageIfDefined();
			$talkPageList[] = $talkPage;
			if ( $talkPage ) {
				$lb->addObj( $talkPage );
			}
		}

		$lb->setCaller( __METHOD__ )->execute();
		foreach ( $talkPageList as $index => $talkPage ) {
			if ( !$talkPage || !$talkPage->exists() ) {
				$talkPageList[$index] = null;
			}
		}

		return $talkPageList;
	}

	private function createPageMoveOperation( PageTitleRenamer $renamer, Title $from ): PageMoveOperation {
		try {
			$to = $renamer->getNewTitle( $from );
			$operation = new PageMoveOperation( $from, $to );
		} catch ( InvalidPageTitleRename $e ) {
			$operation = new PageMoveOperation( $from, null, $e );
		}

		return $operation;
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
