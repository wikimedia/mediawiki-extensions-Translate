<?php
/**
 * Script for processing message changes in file based message groups.
 *
 * @author Niklas Laxström
 *
 * @copyright Copyright © 2012-2013, Niklas Laxström
 * @license GPL-2.0-or-later
 * @file
 */

// Standard boilerplate to define $IP
if ( getenv( 'MW_INSTALL_PATH' ) !== false ) {
	$IP = getenv( 'MW_INSTALL_PATH' );
} else {
	$dir = __DIR__;
	$IP = "$dir/../../..";
}
require_once "$IP/maintenance/Maintenance.php";

use MediaWiki\Extension\Translate\MessageSync\MessageSourceChange;
use MediaWiki\Extension\Translate\Services;
use MediaWiki\Extension\Translate\Utilities\StringComparators\SimpleStringComparator;
use MediaWiki\MediaWikiServices;

/**
 * Script for processing message changes in file based message groups.
 *
 * We used to process changes during web request, but that was too slow. With
 * this command line script we can do all the work needed even if it takes
 * some time.
 *
 * @since 2012-04-23
 */
class ProcessMessageChanges extends Maintenance {
	public function __construct() {
		parent::__construct();
		$this->addDescription( 'Script for processing message changes in file based message groups' );
		$this->addOption(
			'group',
			'(optional) Comma separated list of group IDs to process (can use * as wildcard). ' .
				'Default: "*"',
			false, /*required*/
			true /*has arg*/
		);
		$this->addOption(
			'skipgroup',
			'(optional) Comma separated list of group IDs to not process (can use * ' .
				'as wildcard). Overrides --group parameter.',
			false, /*required*/
			true /*has arg*/
		);
		$this->addOption(
			'name',
			'(optional) Unique name to avoid conflicts with multiple invocations of this script.',
			false, /*required*/
			true /*has arg*/
		);
		$this->addOption(
			'safe-import',
			'(optional) Import "safe" changes: message additions when no other kind of changes.'
		);
		$this->addOption(
			'skip-group-sync-check',
			'(optional) Skip importing group if synchronization is still in progress or if there ' .
				'was an error during synchronization. See: ' .
				'https://www.mediawiki.org/wiki/Help:Extension:Translate/Group_management#Strong_synchronization'
		);
		$this->requireExtension( 'Translate' );
	}

	public function execute() {
		$name = $this->getOption( 'name', MessageChangeStorage::DEFAULT_NAME );
		if ( !MessageChangeStorage::isValidCdbName( $name ) ) {
			$this->fatalError( 'Invalid name' );
		}

		$groups = $this->getGroups();
		$changes = [];
		$comparator = new ExternalMessageSourceStateComparator( new SimpleStringComparator() );

		$scripted = $this->hasOption( 'safe-import' );
		$skipGroupSyncCache = $this->hasOption( 'skip-group-sync-check' );

		$services = Services::getInstance();
		$groupSyncCache = $services->getGroupSynchronizationCache();
		$groupSyncCacheEnabled = MediaWikiServices::getInstance()->getMainConfig()
			->get( 'TranslateGroupSynchronizationCache' );

		/** @var FileBasedMessageGroup $group */
		foreach ( $groups as $id => $group ) {
			if ( $groupSyncCacheEnabled && !$skipGroupSyncCache ) {
				if ( $groupSyncCache->isGroupBeingProcessed( $id ) ) {
					$this->error( "Group $id is currently being synchronized; skipping processing of changes\n" );
					continue;
				}

				if ( $groupSyncCache->groupHasErrors( $id ) ) {
					$this->error( "Skipping $id due to an error during synchronization\n" );
					continue;
				}
			}

			if ( !$scripted ) {
				$this->output( "Processing $id\n" );
			}
			try {
				$changes[$id] = $comparator->processGroup( $group, $comparator::ALL_LANGUAGES );
			} catch ( Exception $e ) {
				$errorMsg = "Exception occurred while processing group: $id.\nException: $e";
				$this->error( $errorMsg );
				error_log( $errorMsg );
			}
		}

		// Remove all groups without changes
		$changes = array_filter( $changes, static function ( MessageSourceChange $change ) {
			return $change->getAllModifications() !== [];
		} );

		if ( $changes === [] ) {
			if ( !$scripted ) {
				$this->output( "No changes found\n" );
			}

			return;
		}

		if ( $scripted ) {
			$importer = $services->getExternalMessageSourceStateImporter();
			$info = $importer->importSafe( $changes, $name );
			$this->printChangeInfo( $info );

			return;
		}

		$file = MessageChangeStorage::getCdbPath( $name );

		MessageChangeStorage::writeChanges( $changes, $file );
		$url = SpecialPage::getTitleFor( 'ManageMessageGroups', $name )->getFullURL();
		$this->output( "Process changes at $url\n" );
	}

	/**
	 * Gets list of message groups filtered by user input.
	 * @return FileBasedMessageGroup[]
	 */
	protected function getGroups() {
		$groups = MessageGroups::getGroupsByType( FileBasedMessageGroup::class );

		// Include all if option not given
		$include = $this->getOption( 'group', '*' );
		$include = explode( ',', $include );
		$include = array_map( 'trim', $include );
		$include = MessageGroups::expandWildcards( $include );

		// Exclude nothing if option not given
		$exclude = $this->getOption( 'skipgroup', '' );
		$exclude = explode( ',', $exclude );
		$exclude = array_map( 'trim', $exclude );
		$exclude = MessageGroups::expandWildcards( $exclude );

		// Flip to allow isset
		$include = array_flip( $include );
		$exclude = array_flip( $exclude );

		$groups = array_filter( $groups,
			static function ( MessageGroup $group ) use ( $include, $exclude ) {
				$id = $group->getId();

				return isset( $include[$id] ) && !isset( $exclude[$id] );
			}
		);

		return $groups;
	}

	protected function printChangeInfo( array $info ) {
		foreach ( $info['processed'] as $group => $languages ) {
			$newMessageCount = array_sum( $languages );
			if ( $newMessageCount ) {
				$this->output( "Imported $newMessageCount new messages or translations for $group.\n" );
			}
		}

		if ( $info['skipped'] !== [] ) {
			$skipped = implode( ', ', array_keys( $info['skipped'] ) );
			$this->output( "There are changes to check for groups $skipped.\n" );
			$url = SpecialPage::getTitleFor( 'ManageMessageGroups', $info['name'] )->getFullURL();
			$this->output( "You can process them at $url\n" );
		}
	}
}

$maintClass = ProcessMessageChanges::class;
require_once RUN_MAINTENANCE_IF_MAIN;
