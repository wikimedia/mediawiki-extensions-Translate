<?php
/**
 * Script for processing message changes in file based message groups.
 *
 * @author Niklas Laxström
 *
 * @copyright Copyright © 2012-2013, Niklas Laxström
 * @license GPL-2.0+
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
	protected $changes = array();

	/**
	 * @var int
	 */
	protected $counter;

	public function __construct() {
		parent::__construct();
		$this->mDescription = 'Script for processing message changes in file based message groups';
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
			'(optional) Import "safe" changes: message additions when no other kind of changes.',
			false, /*required*/
			false /*has arg*/
		);
	}

	public function execute() {
		$groups = $this->getGroups();
		$changes = array();
		$comparator = new ExternalMessageSourceStateComparator();

		$scripted = $this->hasOption( 'safe-import' );

		/** @var FileBasedMessageGroup $group */
		foreach ( $groups as $id => $group ) {
			if ( !$scripted ) {
				$this->output( "Processing $id\n" );
			}
			$changes[$id] = $comparator->processGroup( $group, $comparator::ALL_LANGUAGES );
		}

		// Remove all groups without changes
		$changes = array_filter( $changes );

		if ( $changes === array() ) {
			if ( !$scripted ) {
				$this->output( "No changes found\n" );
			}

			return;
		}

		if ( $this->hasOption( 'safe-import' ) ) {
			$importer = new ExternalMessageSourceStateImporter();
			$info = $importer->importSafe( $changes );
			$this->printChangeInfo( $info );

			return;
		}

		$name = $this->getOption( 'name', MessageChangeStorage::DEFAULT_NAME );
		if ( !MessageChangeStorage::isValidCdbName( $name ) ) {
			$this->error( 'Invalid name', 1 );
		}

		$file = MessageChangeStorage::getCdbPath( $name );

		MessageChangeStorage::writeChanges( $changes, $file );
		$url = SpecialPage::getTitleFor( 'ManageMessageGroups', $name )->getFullURL();
		$this->output( "Process changes at $url\n" );
	}

	/**
	 * Gets list of message groups filtered by user input.
	 * @return MessageGroup[]
	 */
	protected function getGroups() {
		$groups = MessageGroups::getGroupsByType( 'FileBasedMessageGroup' );

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
			function ( MessageGroup $group ) use ( $include, $exclude ) {
				$id = $group->getId();

				return isset( $include[$id] ) && !isset( $exclude[$id] );
			}
		);

		return $groups;
	}

	protected function printChangeInfo( array $info ) {
		foreach ( $info['processed'] as $group => $count ) {
			$this->output( "Imported $count new messages or translations for $group.\n" );
		}

		if ( $info['skipped'] !== array() ) {
			$skipped = implode( ', ', array_keys( $info['skipped'] ) );
			$this->output( "There are changes to check for groups $skipped.\n" );
			$url = SpecialPage::getTitleFor( 'ManageMessageGroups', $info['name'] )->getFullURL();
			$this->output( "You can process them at $url\n" );
		}
	}
}

$maintClass = 'ProcessMessageChanges';
require_once RUN_MAINTENANCE_IF_MAIN;
