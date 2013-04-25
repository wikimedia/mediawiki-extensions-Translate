<?php
/**
 * Script to ensure all translation pages are up to date.
 *
 * @author Niklas Laxström
 * @license GPL2+
 * @file
 */

// Standard boilerplate to define $IP
if ( getenv( 'MW_INSTALL_PATH' ) !== false ) {
	$IP = getenv( 'MW_INSTALL_PATH' );
} else {
	$dir = dirname( __FILE__ );
	$IP = "$dir/../../..";
}
require_once( "$IP/maintenance/Maintenance.php" );

/**
 * Script to ensure all translation pages are up to date
 * @since 2013-04
 */
class RefreshTranslatablePages extends Maintenance {
	public function __construct() {
		parent::__construct();
		$this->mDescription = 'Ensure all translation pages are up to date';
	}

	public function execute() {
		$groups = MessageGroups::singleton()->getGroups();

		/** @var MessageGroup $group */
		foreach ( $groups as $id => $group ) {
			if ( !$group instanceof WikiPageMessageGroup ) {
				continue;
			}

			// Get all translation subpages and refresh each one of them
			$page = TranslatablePage::newFromTitle( $group->getTitle() );
			$translationPages = $page->getTranslationPages();

			foreach ( $translationPages as $subpage ) {
				$job = TranslateRenderJob::newJob( $subpage );
				$job->run();
			}
		}
	}
}

$maintClass = 'RefreshTranslatablePages';
require_once( RUN_MAINTENANCE_IF_MAIN );
