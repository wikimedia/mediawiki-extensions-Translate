<?php
/**
 * Creates a database of keys in all groups, so that namespace and key can be
 * used to get the group they belong to. This is used as a fallback when there
 * is no other way to know which message group a message belongs to.
 *
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @file
 */

use MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroups;
use MediaWiki\Extension\Translate\Services;
use MediaWiki\Maintenance\Maintenance;

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
		$this->addDescription( 'Creates or updates a message index.' );
		$this->requireExtension( 'Translate' );
	}

	public function execute() {
		MessageGroups::singleton()->recache();
		Services::getInstance()->getMessageIndex()->rebuild();
	}
}

$maintClass = CreateMessageIndex::class;
require_once RUN_MAINTENANCE_IF_MAIN;
