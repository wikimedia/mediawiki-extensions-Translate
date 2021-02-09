<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Synchronization;

use MediaWiki\Extension\Translate\Services;
use MediaWiki\Extension\Translate\Utilities\BaseMaintenanceScript;
use MediaWiki\MediaWikiServices;

/**
 * Query information in the group synchronization cache.
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 * @since 2021.01
 */
class QueryGroupSyncCacheMaintenanceScript extends BaseMaintenanceScript {
	public function __construct() {
		parent::__construct();
		$this->addDescription( 'Query the contents of the group synchronization cache' );

		$this->addOption(
			'group',
			'(optional) Group Id being queried',
			self::OPTIONAL,
			self::HAS_ARG
		);

		$this->requireExtension( 'Translate' );
	}

	public function execute() {
		$config = MediaWikiServices::getInstance()->getMainConfig();

		if ( !$config->get( 'TranslateGroupSynchronizationCache' ) ) {
			$this->fatalError( 'GroupSynchronizationCache is not enabled' );
		}

		$groupSyncCache = Services::getInstance()->getGroupSynchronizationCache();

		$groupId = $this->getOption( 'group' );
		if ( $groupId ) {
			$groupMessages = $groupSyncCache->getGroupMessages( $groupId );
			$this->displayGroupMessages( $groupId, $groupMessages );
		} else {
			$groups = $groupSyncCache->getGroupsInSync();
			$this->displayGroups( $groups );
		}
	}

	private function displayGroups( array $groupIds ): void {
		if ( !$groupIds ) {
			$this->output( "No groups found in synchronization\n" );
			return;
		}

		$this->output( "Groups found in sync:\n" );
		foreach ( $groupIds as $groupId ) {
			$this->output( "\t- $groupId\n" );
		}
	}

	/**
	 * @param string $groupId
	 * @param MessageUpdateParameter[] $groupMessages
	 */
	private function displayGroupMessages( string $groupId, array $groupMessages ): void {
		if ( !$groupMessages ) {
			$this->output( "No messages found for group $groupId\n" );
			return;
		}

		$this->output( "Messages in group $groupId:\n" );
		foreach ( $groupMessages as $message ) {
			$this->displayMessageDetails( $message );
		}
	}

	private function displayMessageDetails( MessageUpdateParameter $messageParam ): void {
		$tags = [];
		if ( $messageParam->isRename() ) {
			$tags[] = 'rename';
		}

		if ( $messageParam->isFuzzy() ) {
			$tags[] = 'fuzzy';
		}

		$otherLangs = $messageParam->getOtherLangs() ?: [ 'N/A' ];
		$this->output( "\t- Title: " . $messageParam->getPageName() . "\n" );
		$this->output( "\t  Tags: " . ( $tags ? implode( ', ', $tags ) : 'N/A' ) . "\n" );
		if ( $messageParam->isRename() ) {
			$this->output( "\t  Target: " . $messageParam->getTargetValue() . "\n" );
			$this->output( "\t  Replacement: " . $messageParam->getReplacementValue() . "\n" );
			$this->output( "\t  Other languages: " . ( implode( ', ', $otherLangs ) ) . "\n" );
		}
	}
}
