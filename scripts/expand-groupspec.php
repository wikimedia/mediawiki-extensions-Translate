<?php
/**
 * Script that expands a message group specification (such as page-News*,page-Help*).
 *
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

class TranslateExpandGroupSpec extends Maintenance {
	public function __construct() {
		parent::__construct();
		$this->mDescription = 'Expands a message group specification.';
		$this->addArg(
			'specification',
			'For example page-*,main',
			true, /*required*/
			false /*has arg*/
		);
	}

	public function execute() {
		$spec = $this->getArg( 0 );
		$patterns = explode( ',', trim( $spec ) );
		$ids = MessageGroups::expandWildcards( $patterns );
		if ( $ids !== [] ) {
			// This should not be affected by --quiet
			echo implode( "\n", $ids ) . "\n";
		}
	}
}

$maintClass = 'TranslateExpandGroupSpec';
require_once RUN_MAINTENANCE_IF_MAIN;
