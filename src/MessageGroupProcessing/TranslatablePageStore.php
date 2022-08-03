<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use AggregateMessageGroup;
use InvalidArgumentException;
use JobQueueGroup;
use MediaWiki\Extension\Translate\PageTranslation\TranslatablePage;
use MediaWiki\Extension\Translate\PageTranslation\UpdateTranslatablePageJob;
use MediaWiki\Revision\RevisionRecord;
use MessageGroups;
use MessageIndex;
use Title;
use TranslateMetadata;
use Wikimedia\Rdbms\ILoadBalancer;

/**
 * @author Abijeet Patro
 * @author Niklas Laxström
 * @since 2022.03
 * @license GPL-2.0-or-later
 */
class TranslatablePageStore implements TranslatableBundleStore {
	/** @var MessageIndex */
	private $messageIndex;
	/** @var JobQueueGroup */
	private $jobQueue;
	/** @var RevTagStore */
	private $revTagStore;
	/** @var ILoadBalancer */
	private $loadBalancer;

	public function __construct(
		MessageIndex $messageIndex,
		JobQueueGroup $jobQueue,
		RevTagStore $revTagStore,
		ILoadBalancer $loadBalancer
	) {
		$this->messageIndex = $messageIndex;
		$this->jobQueue = $jobQueue;
		$this->revTagStore = $revTagStore;
		$this->loadBalancer = $loadBalancer;
	}

	public function move( Title $oldName, Title $newName ): void {
		$oldTranslatablePage = TranslatablePage::newFromTitle( $oldName );
		$newTranslatablePage = TranslatablePage::newFromTitle( $newName );
		$oldGroupId = $oldTranslatablePage->getMessageGroupId();
		$newGroupId = $newTranslatablePage->getMessageGroupId();

		TranslateMetadata::moveMetadata( $oldGroupId, $newGroupId, TranslatablePage::METADATA_KEYS );

		$this->moveMetadata( $oldGroupId, $newGroupId );

		TranslatablePage::clearSourcePageCache();

		// Re-render the pages to get everything in sync
		MessageGroups::singleton()->recache();
		// Update message index now so that, when after this job the MoveTranslationUnits hook
		// runs in deferred updates, it will not run MessageIndexRebuildJob (T175834).
		$this->messageIndex->rebuild();

		$job = UpdateTranslatablePageJob::newFromPage( TranslatablePage::newFromTitle( $newName ) );
		$this->jobQueue->push( $job );
	}

	public function handleNullRevisionInsert( TranslatableBundle $bundle, RevisionRecord $revision ): void {
		if ( !$bundle instanceof TranslatablePage ) {
			throw new InvalidArgumentException(
				'Expected $bundle to be of type TranslatablePage, got ' . get_class( $bundle )
			);
		}

		$this->revTagStore->replaceTag( $bundle->getTitle(), RevTagStore::TP_READY_TAG, $revision->getId() );
		TranslatablePage::clearSourcePageCache();
	}

	public function delete( Title $title ): void {
		$dbw = $this->loadBalancer->getConnectionRef( DB_PRIMARY );

		$this->revTagStore->removeTags( $title, RevTagStore::TP_MARK_TAG, RevTagStore::TP_READY_TAG );
		$dbw->delete( 'translate_sections', [ 'trs_page' => $title->getArticleID() ], __METHOD__ );

		$translatablePage = TranslatablePage::newFromTitle( $title );
		$translatablePage->getTranslationPercentages();
		foreach ( $translatablePage->getTranslationPages() as $page ) {
			$page->invalidateCache();
		}

		$groupId = $translatablePage->getMessageGroupId();
		TranslateMetadata::clearMetadata( $groupId, TranslatablePage::METADATA_KEYS );
		$this->removeFromAggregateGroups( $groupId );
		TranslatablePage::clearSourcePageCache();

		MessageGroups::singleton()->recache();
		$this->messageIndex->rebuild();
	}

	private function moveMetadata( string $oldGroupId, string $newGroupId ): void {
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

	private function removeFromAggregateGroups( string $groupId ): void {
		// remove the page from aggregate groups, if present in any of them.
		$aggregateGroups = MessageGroups::getGroupsByType( AggregateMessageGroup::class );
		TranslateMetadata::preloadGroups( array_keys( $aggregateGroups ), __METHOD__ );
		foreach ( $aggregateGroups as $group ) {
			$subgroups = TranslateMetadata::get( $group->getId(), 'subgroups' );
			if ( $subgroups !== false ) {
				$subgroups = explode( ',', $subgroups );
				$subgroups = array_flip( $subgroups );
				if ( isset( $subgroups[$groupId] ) ) {
					unset( $subgroups[$groupId] );
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
}
