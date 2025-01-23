<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Diagnostics;

use Cdb\Reader;
use MediaWiki\Extension\Translate\MessageSync\MessageSourceChange;
use MediaWiki\Extension\Translate\Synchronization\ManageGroupsSpecialPage;
use MediaWiki\Extension\Translate\Synchronization\MessageChangeStorage;
use MediaWiki\Extension\Translate\Utilities\BaseMaintenanceScript;
use MediaWiki\Extension\Translate\Utilities\Utilities;
use MediaWiki\MediaWikiServices;
use MediaWiki\Title\Title;

/**
 * This maintenance script finds translations that are deleted from
 * upstream file based message groups
 *
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 * @since 2025.01
 */
class FindDeletedTranslationsMaintenanceScript extends BaseMaintenanceScript {
	public function __construct() {
		parent::__construct();
		$this->addDescription(
			'Script to find translations that are deleted upstream for file based message groups'
		);
		$this->addOption(
			'cdb-name',
			'Name of the cdb file containing the incoming changes',
			self::REQUIRED,
			self::HAS_ARG
		);
		$this->addOption(
			'group',
			'Name of the group to identify the deletions for',
			self::REQUIRED,
			self::HAS_ARG
		);
		$this->requireExtension( 'Translate' );
	}

	public function execute() {
		$cdbName = $this->getOption( 'cdb-name' );
		$cdbPath = MessageChangeStorage::getCdbPath( $cdbName );
		$groupId = $this->getOption( 'group' );

		// Initialize services
		$mwInstance = MediaWikiServices::getInstance();
		$nsInfo = $mwInstance->getNamespaceInfo();
		$contentLanguage = $mwInstance->getContentLanguage();
		$linkBatchFactory = $mwInstance->getLinkBatchFactory();

		if ( !file_exists( $cdbPath ) ) {
			$this->fatalError( "Cdb file $cdbPath not found" );
		}

		$reader = Reader::open( $cdbPath );
		$groups = ManageGroupsSpecialPage::getGroupsFromCdb( $reader );
		// Verify that the group has changes
		if ( !isset( $groups[$groupId] ) ) {
			$this->fatalError( "No changes found for group $groupId" );
		}
		$group = $groups[$groupId];
		$groupNamespace = $group->getNamespace();
		$isCap = $nsInfo->isCapitalized( $groupNamespace );

		// Get the changes
		$sourceChanges = MessageSourceChange::loadModifications(
			Utilities::deserialize( $reader->get( $groupId ) )
		);

		// Get all languages that have changes
		$languages = $sourceChanges->getLanguages();
		$deletionChanges = [];
		$titleCache = [];
		$lb = $linkBatchFactory->newLinkBatch();
		foreach ( $languages as $language ) {
			$languageDeletions = $sourceChanges->getDeletions( $language );
			foreach ( $languageDeletions as $change ) {
				$key = $change[ 'key' ];
				if ( $isCap ) {
					$key = $contentLanguage->ucfirst( $key );
				}
				$messageTitle = Title::makeTitle( $groupNamespace, "$key/$language" );
				$titleCache[] = $messageTitle;
				$lb->addObj( $messageTitle );
			}
		}

		$lb->setCaller( __METHOD__ );
		$lb->execute();

		foreach ( $titleCache as $messageTitle ) {
			if ( $messageTitle->exists() ) {
				$deletionChanges[] = $messageTitle;
			}
		}

		if ( !$deletionChanges ) {
			$this->output( "No deletion changes found for $groupId" );
		}

		// Print out the list of changes
		foreach ( $deletionChanges as $deletedTitle ) {
			$this->output( $deletedTitle->getPrefixedDBkey() . "\n" );
		}
	}
}
