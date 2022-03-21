<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageBundleTranslation;

use InvalidArgumentException;
use MediaWiki\Extension\Translate\MessageGroupProcessing\RevTagStore;
use MediaWiki\Extension\Translate\MessageGroupProcessing\TranslatableBundle;
use MediaWiki\Extension\Translate\MessageGroupProcessing\TranslatableBundleStore;
use MediaWiki\Revision\RevisionRecord;
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

	public function __construct( RevTagStore $revTagStore ) {
		$this->revTagStore = $revTagStore;
	}

	public function move( Title $oldName, Title $newName ): void {
		// No specific actions needed post move for MessageBundle
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
}
