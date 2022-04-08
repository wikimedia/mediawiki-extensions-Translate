<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageBundleTranslation;

use InvalidArgumentException;
use MediaWiki\Extension\Translate\MessageGroupProcessing\RevTagStore;
use MediaWiki\Extension\Translate\MessageGroupProcessing\TranslatableBundle;
use MediaWiki\Extension\Translate\MessageGroupProcessing\TranslatableBundleStore;
use MediaWiki\Revision\RevisionRecord;
use MessageGroups;
use MessageIndex;
use Title;

/**
 * @author Abijeet Patro
 * @author Niklas Laxström
 * @since 2022.04
 * @license GPL-2.0-or-later
 */
class MessageBundleStore implements TranslatableBundleStore {
	/** @var RevTagStore */
	private $revTagStore;
	/** @var MessageIndex */
	private $messageIndex;

	public function __construct( RevTagStore $revTagStore, MessageIndex $messageIndex ) {
		$this->revTagStore = $revTagStore;
		$this->messageIndex = $messageIndex;
	}

	public function move( Title $oldName, Title $newName ): void {
		MessageBundle::clearSourcePageCache();

		// Re-render the bundles to get everything in sync
		MessageGroups::singleton()->recache();
		// Update message index now so that, when after this job the MoveTranslationUnits hook
		// runs in deferred updates, it will not run MessageIndexRebuildJob (T175834).
		$this->messageIndex->rebuild();
	}

	public function handleNullRevisionInsert( TranslatableBundle $bundle, RevisionRecord $revision ): void {
		if ( !$bundle instanceof MessageBundle ) {
			throw new InvalidArgumentException(
				'Expected $bundle to be of type MessageBundle, got ' . get_class( $bundle )
			);
		}

		$this->revTagStore->addTag( $bundle->getTitle(), 'mb:valid', $revision->getId() );
		MessageBundle::clearSourcePageCache();
	}

	public function delete( Title $title ): void {
		$this->revTagStore->removeTags( $title, 'mb:ready' );
		MessageBundle::clearSourcePageCache();
	}
}
