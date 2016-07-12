<?php
/**
 * Contains class with job for moving translation pages.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0+
 */

/**
 * Contains class with job for moving translation pages. Used together with
 * SpecialPageTranslationMovePage class.
 *
 * @ingroup PageTranslation JobQueue
 */
class TranslateMoveJob extends Job {
	/**
	 * @param $target Title
	 * @param $params array, should include base-source and base-target
	 * @param $performer
	 * @return TranslateMoveJob
	 */
	public static function newJob(
		Title $source, Title $target, array $moves, $summary, User $performer
	) {
		$params = array(
			'source' => $source->getPrefixedText(),
			'target' => $target->getPrefixedText(),
			'moves' => $moves,
			'summary' => $summary,
			'performer' => $performer->getName(),
		);

		return new self( $target, $params );
	}

	public function __construct( $title, $params = array(), $id = 0 ) {
		parent::__construct( __CLASS__, $title, $params, $id );
		$this->params = $params;
	}

	public function run() {
		$sourceTitle = Title::newFromText( $this->params['source'] );
		$targetTitle = Title::newFromText( $this->params['target'] );

		$this->doMoves( $this->params['moves'] );

		$this->moveMetadata(
			TranslatablePage::newFromTitle( $sourceTitle )->getMessageGroupId(),
			TranslatablePage::newFromTitle( $targetTitle )->getMessageGroupId()
		);

		$entry = new ManualLogEntry( 'pagetranslation', 'moveok' );
		$entry->setPerformer( User::newFromName( $this->params['performer'] ) );
		$entry->setParameters( array( 'target' => $this->params['target'] ) );
		$entry->setTarget( $sourceTitle );
		$logid = $entry->insert();
		$entry->publish( $logid );

		// Re-render the pages to get everything in sync
		MessageGroups::singleton()->recache();

		$job = new TranslationsUpdateJob( $targetTitle, array( 'sections' => array() ) );
		JobQueueGroup::singleton()->push( $job );

		return true;
	}

	protected function doMoves( array $moves ) {
		$user = FuzzyBot::getUser();
		$performer = User::newFromName( $this->params['performer'] );

		PageTranslationHooks::$allowTargetEdit = true;
		PageTranslationHooks::$jobQueueRunning = true;

		foreach ( $this->params['moves'] as $source => $target ) {
			$sourceTitle = Title::newFromText( $source );
			$targetTitle = Title::newFromText( $target );

			$mover = new MovePage( $sourceTitle, $targetTitle );
			$status = $mover->move( $user, $this->params['summary'], false );
			if ( !$status->isOK() ) {
				$entry = new ManualLogEntry( 'pagetranslation', 'movenok' );
				$entry->setPerformer( $performer );
				$entry->setTarget( $sourceTitle );
				$entry->setParameters( array(
					'target' => $target,
					'error' => $status->getErrorsArray(),
				) );
				$logid = $entry->insert();
				$entry->publish( $logid );
			}
		}

		PageTranslationHooks::$allowTargetEdit = false;
		PageTranslationHooks::$jobQueueRunning = false;
	}

	protected function moveMetadata( $oldGroupId, $newGroupId ) {
		$types = array( 'prioritylangs', 'priorityforce', 'priorityreason' );

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
}
