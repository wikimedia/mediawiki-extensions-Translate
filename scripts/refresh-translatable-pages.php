<?php
/**
 * Script to ensure all translation pages are up to date.
 *
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @file
 */

// Standard boilerplate to define $IP

use MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroups;
use MediaWiki\Extension\Translate\PageTranslation\TranslatablePage;
use MediaWiki\Extension\Translate\PageTranslation\UpdateTranslatablePageJob;
use MediaWiki\Maintenance\Maintenance;
use MediaWiki\MediaWikiServices;

if ( getenv( 'MW_INSTALL_PATH' ) !== false ) {
	$IP = getenv( 'MW_INSTALL_PATH' );
} else {
	$dir = __DIR__;
	$IP = "$dir/../../..";
}
require_once "$IP/maintenance/Maintenance.php";

/**
 * Script to ensure all translation pages are up to date
 * @since 2013-04
 */
class RefreshTranslatablePages extends Maintenance {
	private const USE_NON_PRIORITIZED_JOBS = true;

	public function __construct() {
		parent::__construct();
		$this->addDescription( 'Ensure all translation pages are up to date.' );
		$this->setBatchSize( 300 );
		$this->addOption( 'jobqueue', 'Use JobQueue (asynchronous)' );
		$this->requireExtension( 'Translate' );
	}

	public function execute() {
		$groups = MessageGroups::singleton()->getGroups();
		$mwInstance = MediaWikiServices::getInstance();
		$jobQueueGroup = $mwInstance->getJobQueueGroup();

		$counter = 0;
		$jobCounter = 0;
		$useJobQueue = $this->hasOption( 'jobqueue' );

		foreach ( $groups as $group ) {
			if ( !$group instanceof WikiPageMessageGroup ) {
				continue;
			}

			$counter++;
			if ( ( $counter % $this->mBatchSize ) === 0 ) {
				$this->waitForReplication();
			}

			$page = TranslatablePage::newFromTitle( $group->getTitle() );
			$jobs = UpdateTranslatablePageJob::getRenderJobs( $page, self::USE_NON_PRIORITIZED_JOBS );
			if ( $useJobQueue ) {
				$jobCounter += count( $jobs );
				$jobQueueGroup->push( $jobs );
			} else {
				foreach ( $jobs as $job ) {
					$job->run();
				}
			}
		}

		if ( $useJobQueue ) {
			$this->output( "Queued $jobCounter refresh job(s) for $counter translatable pages.\n" );
		} else {
			$this->output( "Refreshed $counter translatable pages.\n" );
		}
	}
}

$maintClass = RefreshTranslatablePages::class;
require_once RUN_MAINTENANCE_IF_MAIN;
