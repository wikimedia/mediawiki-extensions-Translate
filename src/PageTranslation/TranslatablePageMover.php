<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

use AggregateMessageGroup;
use JobQueueGroup;
use LinkBatch;
use ManualLogEntry;
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
	/** @var MovePageFactory */
	private $movePageFactory;
	/** @var int|null */
	private $pageMoveLimit;
	/** @var JobQueueGroup */
	private $jobQueue;

	public function __construct( MovePageFactory $movePageFactory, JobQueueGroup $jobQueue, ?int $pageMoveLimit ) {
		$this->movePageFactory = $movePageFactory;
		$this->jobQueue = $jobQueue;
		$this->pageMoveLimit = $pageMoveLimit;
	}

	/** Makes old title into a new title by replacing $base part of old title with $target. */
	public function newPageTitle( string $base, Title $old, Title $target ): Title {
		$search = preg_quote( $base, '~' );

		if ( $old->inNamespace( NS_TRANSLATIONS ) ) {
			$new = $old->getText();
			$new = preg_replace( "~^$search~", $target->getPrefixedText(), $new, 1 );

			return Title::makeTitleSafe( NS_TRANSLATIONS, $new );
		} else {
			$new = $old->getPrefixedText();
			$new = preg_replace( "~^$search~", $target->getPrefixedText(), $new, 1 );

			return Title::newFromText( $new );
		}
	}

	/** @return SplObjectStorage Title => Status */
	public function checkMoveBlockers(
		Title $source,
		?Title $target,
		User $user,
		string $reason,
		bool $moveSubPages
	): SplObjectStorage {
		$blockers = new SplObjectStorage();

		$page = TranslatablePage::newFromTitle( $source );

		if ( !$target ) {
			$blockers[$source] = Status::newFatal( 'pt-movepage-block-base-invalid' );
			return $blockers;
		}

		if ( $target->inNamespaces( NS_MEDIAWIKI, NS_TRANSLATIONS ) ) {
			$blockers[$source] = Status::newFatal( 'immobile-target-namespace', $target->getNsText() );
			return $blockers;
		}

		if ( $target->exists() ) {
			$blockers[$source] = Status::newFatal(
				'pt-movepage-block-base-exists', $target->getPrefixedText()
			);
		} else {
			$movePage = $this->movePageFactory->newMovePage( $source, $target );
			$status = $movePage->isValidMove();
			$status->merge( $movePage->checkPermissions( $user, $reason ) );
			if ( !$status->isOK() ) {
				$blockers[$source] = $status;
			}
		}

		// Don't spam the same errors for all pages if base page fails
		if ( count( $blockers ) ) {
			return $blockers;
		}

		// Collect all the old and new titles for checcks
		$titles = [];
		$base = $source->getPrefixedText();
		$pages = $page->getTranslationPages();
		foreach ( $pages as $old ) {
			$titles['tp'][] = [ $old, $this->newPageTitle( $base, $old, $target ) ];
		}

		$subpages = $moveSubPages ? $this->getNormalSubpages( $page ) : [];
		foreach ( $subpages as $old ) {
			$titles['subpage'][] = [ $old, $this->newPageTitle( $base, $old, $target ) ];
		}

		$pages = $page->getTranslationUnitPages( 'all' );
		foreach ( $pages as $old ) {
			$titles['section'][] = [ $old, $this->newPageTitle( $base, $old, $target ) ];
		}

		// Check that all new titles are valid and count them. Add 1 for source page.
		$moveCount = 1;
		$lb = new LinkBatch();
		foreach ( $titles as $type => $list ) {
			$moveCount += count( $list );
			// Give grep a chance to find the usages:
			// pt-movepage-block-tp-invalid, pt-movepage-block-section-invalid,
			// pt-movepage-block-subpage-invalid
			foreach ( $list as $pair ) {
				[ $old, $new ] = $pair;
				if ( $new === null ) {
					$blockers[$old] = Status::newFatal(
						"pt-movepage-block-$type-invalid",
						$old->getPrefixedText()
					);
					continue;
				}
				$lb->addObj( $old );
				$lb->addObj( $new );
			}
		}

		if ( $this->pageMoveLimit !== null && $moveCount > $this->pageMoveLimit ) {
			$blockers[$source] = Status::newFatal(
				'pt-movepage-page-count-limit',
				Message::numParam( $this->pageMoveLimit )
			);
		}

		if ( count( $blockers ) ) {
			return $blockers;
		}

		// Check that there are no move blockers
		$lb->execute();
		foreach ( $titles as $type => $list ) {
			// Give grep a chance to find the usages:
			// pt-movepage-block-tp-exists, pt-movepage-block-section-exists,
			// pt-movepage-block-subpage-exists
			foreach ( $list as $pair ) {
				list( $old, $new ) = $pair;
				if ( $new->exists() ) {
					$blockers[$old] = Status::newFatal(
						"pt-movepage-block-$type-exists",
						$old->getPrefixedText(),
						$new->getPrefixedText()
					);
				} else {
					/* This method has terrible performance:
					 * - 2 queries by core
					 * - 3 queries by lqt
					 * - and no obvious way to preload the data! */
					$movePage = $this->movePageFactory->newMovePage( $old, $target );
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
		}

		return $blockers;
	}

	public function moveAsynchronously(
		Title $source,
		Title $target,
		bool $moveSubPages,
		User $user,
		string $summary
	): void {
		$pageMoves = $this->getPagesToMove( $source, $target, $moveSubPages );

		$job = TranslatablePageMoveJob::newJob( $source, $target, $pageMoves, $summary, $user );
		$this->lock( array_keys( $pageMoves ) );
		$this->lock( array_values( $pageMoves ) );

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
		string $summary
	): void {
		$this->move( $source, $performer, $pagesToMove, $summary );

		$sourcePage = TranslatablePage::newFromTitle( $source );
		$targetPage = TranslatablePage::newFromTitle( $target );

		$entry = new ManualLogEntry( 'pagetranslation', 'moveok' );
		$entry->setPerformer( $performer );
		$entry->setTarget( $source );
		$entry->setParameters( [ 'target' => $target->getPrefixedText() ] );
		$logid = $entry->insert();
		$entry->publish( $logid );

		$this->moveMetadata( $sourcePage->getMessageGroupId(), $targetPage->getMessageGroupId() );

		// Re-render the pages to get everything in sync
		MessageGroups::singleton()->recache();
		// Update message index now so that, when after this job the MoveTranslationUnits hook
		// runs in deferred updates, it will not run MessageIndexRebuildJob (T175834).
		MessageIndex::singleton()->rebuild();

		$job = TranslationsUpdateJob::newFromPage( $targetPage );
		$this->jobQueue->push( $job );
	}

	/** @return Title[] */
	public function getNormalSubpages( TranslatablePage $page ): array {
		return array_filter(
			$this->getSubpages( $page ),
			function ( $page ) {
				return !(
					TranslatablePage::isTranslationPage( $page ) ||
					TranslatablePage::isSourcePage( $page )
				);
			}
		);
	}

	/** @return Title[] */
	public function getTranslatableSubpages( TranslatablePage $page ): array {
		return array_filter(
			$this->getSubpages( $page ),
			function ( $page ) {
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

	/** @return string[] */
	private function getPagesToMove( Title $source, Title $target, bool $moveSubPages ): array {
		$page = TranslatablePage::newFromTitle( $source );
		$base = $source->getPrefixedText();

		$moves = [];
		$moves[$base] = $target->getPrefixedText();

		foreach ( $page->getTranslationPages() as $from ) {
			$to = $this->newPageTitle( $base, $from, $target );
			$moves[$from->getPrefixedText()] = $to->getPrefixedText();
		}

		foreach ( $page->getTranslationUnitPages( 'all' ) as $from ) {
			$to = $this->newPageTitle( $base, $from, $target );
			$moves[$from->getPrefixedText()] = $to->getPrefixedText();
		}

		if ( $moveSubPages ) {
			$subpages = $this->getNormalSubpages( $page );
			foreach ( $subpages as $from ) {
				$to = $this->newPageTitle( $base, $from, $target );
				$moves[$from->getPrefixedText()] = $to->getPrefixedText();
			}
		}

		return $moves;
	}

	/**
	 * @param Title $baseSource
	 * @param User $performer
	 * @param string[] $pagesToMove
	 * @param string $summary
	 * @return void
	 */
	private function move(
		Title $baseSource,
		User $performer,
		array $pagesToMove,
		string $summary
	): void {
		$fuzzybot = FuzzyBot::getUser();
		$performer = User::newFromName( $performer );

		PageTranslationHooks::$allowTargetEdit = true;

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
		TranslateMetadata::preloadGroups( [ $oldGroupId, $newGroupId ] );
		foreach ( TranslatablePage::METADATA_KEYS as $type ) {
			$value = TranslateMetadata::get( $oldGroupId, $type );
			if ( $value !== false ) {
				TranslateMetadata::set( $oldGroupId, $type, false );
				TranslateMetadata::set( $newGroupId, $type, $value );
			}
		}

		// Make the changes in aggregate groups metadata, if present in any of them.
		$aggregateGroups = MessageGroups::getGroupsByType( AggregateMessageGroup::class );
		TranslateMetadata::preloadGroups( array_keys( $aggregateGroups ) );

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
	}
}
