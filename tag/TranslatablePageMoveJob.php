<?php
/**
 * Contains class with job for moving translation pages.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */

/**
 * Contains class with job for moving translation pages. Used together with
 * SpecialPageTranslationMovePage class.
 *
 * @ingroup PageTranslation JobQueue
 */
class TranslatablePageMoveJob extends Job {

	/**
	 * @param Title $source
	 * @param Title $target
	 * @param array $moves should include base-source and base-target
	 * @param string $summary
	 * @param User $performer
	 * @return TranslateMoveJob
	 */
	public static function newJob(
		Title $source, Title $target, array $moves, $summary, User $performer
	) {
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

	public function __construct( $title, $params = [] ) {
		parent::__construct( __CLASS__, $title, $params );
		$this->params = $params;
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

	protected function doMoves() {
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

			$mover = new MovePage( $sourceTitle, $targetTitle );
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

	protected function moveMetadata( $oldGroupId, $newGroupId ) {
		$types = [ 'prioritylangs', 'priorityforce', 'priorityreason' ];

		foreach ( $types as $type ) {
			$value = TranslateMetadata::get( $oldGroupId, $type );
			if ( $value !== false ) {
				TranslateMetadata::set( $oldGroupId, $type, false );
				TranslateMetadata::set( $newGroupId, $type, $value );
			}
		}

		// Make the changes in aggregate groups metadata, if present in any of them.
		$groups = MessageGroups::getAllGroups();
		foreach ( $groups as $group ) {
			if ( !$group instanceof AggregateMessageGroup ) {
				continue;
			}

			$subgroups = TranslateMetadata::get( $group->getId(), 'subgroups' );
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

	private function lock( array $titles ) {
		$cache = wfGetCache( CACHE_ANYTHING );
		$data = [];
		foreach ( $titles as $title ) {
			$data[wfMemcKey( 'pt-lock', sha1( $title ) )] = 'locked';
		}
		$cache->setMulti( $data );
	}

	private function unlock( array $titles ) {
		$cache = wfGetCache( CACHE_ANYTHING );
		foreach ( $titles as $title ) {
			$cache->delete( wfMemcKey( 'pt-lock', sha1( $title ) ) );
		}
	}
}
