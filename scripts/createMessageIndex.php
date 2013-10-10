<?php
/**
 * Creates a database of keys in all groups, so that namespace and key can be
 * used to get the group they belong to. This is used as a fallback when
 * loadgroup parameter is not provided in the request, which happens if someone
 * reaches a messages from somewhere else than Special:Translate.
 *
 * @author Niklas Laxstrom
 * @copyright Copyright © 2008-2011, Niklas Laxström
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

class CreateMessageIndex extends Maintenance {
	public function __construct() {
		parent::__construct();
		$this->mDescription = 'Creates or updates a message index.';
	}

	public function execute() {
		$this->output( 'Clear cache...', 'cache' );
		MessageGroups::clearCache();
		$this->output( "Done." );

		$this->output( 'Rebuild message index...', 'rebuild' );
		MessageIndex::singleton()->rebuild();
		$this->output( 'Done.', 'rebuild' );
		$this->output( 'Done.', 'main' );
	}
}

$maintClass = 'CreateMessageIndex';
require_once DO_MAINTENANCE;
