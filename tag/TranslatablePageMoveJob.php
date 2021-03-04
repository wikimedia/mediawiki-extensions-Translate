<?php
declare( strict_types = 1 );

use MediaWiki\Extension\Translate\SystemUsers\FuzzyBot;
use MediaWiki\MediaWikiServices;

/**
 * Contains class with job for moving translation pages. Used together with
 * SpecialPageTranslationMovePage class.
 *
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @ingroup PageTranslation JobQueue
 */
class TranslatablePageMoveJob extends Job {
	private const LOCK_TIMEOUT = 3600 * 2;

	public static function newJob(
		Title $source,
		Title $target,
		array $moves,
		string $summary,
		User $performer
	): self {
		$params = [
			'source' => $source->getPrefixedText(),
			'target' => $target->getPrefixedText(),
			'moves' => $moves,
			'summary' => $summary,
			'performer' => $performer->getName(),
		];

		$self = new self( $target, $params );
		$self->lock( array_keys( $moves ) );
		$self->lock( array_values( $moves ) );

		return $self;
	}

	public function __construct( Title $title, array $params = [] ) {
		parent::__construct( __CLASS__, $title, $params );
	}

	public function run() {
		$sourceTitle = Title::newFromText( $this->params['source'] );
		$targetTitle = Title::newFromText( $this->params['target'] );
		$sourcePage = TranslatablePage::newFromTitle( $sourceTitle );
		$targetPage = TranslatablePage::newFromTitle( $targetTitle );

		$this->doMoves();

		$this->moveMetadata(
			$sourcePage->getMessageGroupId(),
			$targetPage->getMessageGroupId()
		);

		$entry = new ManualLogEntry( 'pagetranslation', 'moveok' );
		$entry->setPerformer( User::newFromName( $this->params['performer'] ) );
		$entry->setParameters( [ 'target' => $this->params['target'] ] );
		$entry->setTarget( $sourceTitle );
		$logid = $entry->insert();
		$entry->publish( $logid );

		// Re-render the pages to get everything in sync
		MessageGroups::singleton()->recache();
		// Update message index now so that, when after this job the MoveTranslationUnits hook
		// runs in deferred updates, it will not run MessageIndexRebuildJob (T175834).
		MessageIndex::singleton()->rebuild();

		$job = TranslationsUpdateJob::newFromPage( $targetPage );
		JobQueueGroup::singleton()->push( $job );

		return true;
	}

	private function doMoves(): void {
		$fuzzybot = FuzzyBot::getUser();
		$performer = User::newFromName( $this->params['performer'] );

		PageTranslationHooks::$allowTargetEdit = true;

		foreach ( $this->params['moves'] as $source => $target ) {
			$sourceTitle = Title::newFromText( $source );
			$targetTitle = Title::newFromText( $target );

			if ( $source === $this->params['source'] ) {
				$user = $performer;
			} else {
				$user = $fuzzybot;
			}

			$mover = MediaWikiServices::getInstance()
				->getMovePageFactory()
				->newMovePage( $sourceTitle, $targetTitle );
			$status = $mover->move( $user, $this->params['summary'], false );
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
}
