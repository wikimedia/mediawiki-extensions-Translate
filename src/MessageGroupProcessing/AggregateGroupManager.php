<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use MediaWiki\Extension\Translate\MessageBundleTranslation\MessageBundleMessageGroup;
use MediaWiki\Title\Title;
use MediaWiki\Title\TitleFactory;
use MessageGroup;
use RuntimeException;
use WikiPageMessageGroup;

/**
 * Contains logic to store, validate, fetch aggregate groups created via Special:AggregateGroups.
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 * @since 2024.06
 */
class AggregateGroupManager {
	private TitleFactory $titleFactory;

	public function __construct( TitleFactory $titleFactory ) {
		$this->titleFactory = $titleFactory;
	}

	public function supportsAggregation( MessageGroup $group ): bool {
		return $group instanceof WikiPageMessageGroup || $group instanceof MessageBundleMessageGroup;
	}

	public function getTargetTitleByGroupId( string $groupId ): Title {
		$group = MessageGroups::getGroup( $groupId );
		if ( $group ) {
			return $this->getTargetTitleByGroup( $group );
		} else {
			/* To allow removing no longer existing groups from aggregate message groups,
			 * the message group object $group might not always be available.
			 * In this case we need to fake some title. */
			return $this->titleFactory->newFromText( "Special:Translate/$groupId" );
		}
	}

	/** @throws RuntimeException If MessageGroup::getRelatedPage returns null */
	public function getTargetTitleByGroup( MessageGroup $group ): Title {
		$relatedGroupPage = $group->getRelatedPage();
		if ( !$relatedGroupPage ) {
			throw new RuntimeException( "No related page found for group " . $group->getId() );
		}

		return $this->titleFactory->newFromLinkTarget( $relatedGroupPage );
	}
}
