<?php

declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Synchronization;

use MediaWiki\Extension\Translate\LogNames;
use MediaWiki\Extension\Translate\MessageLoading\RebuildMessageIndexJob;
use MediaWiki\Extension\Translate\Services;
use MediaWiki\Logger\LoggerFactory;
use MediaWiki\Maintenance\Maintenance;
use MediaWiki\MediaWikiServices;
use Psr\Log\LoggerInterface;

/**
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 * @since 2020.06
 */
class CompleteExternalTranslationMaintenanceScript extends Maintenance {
	public function __construct() {
		parent::__construct();
		$this->addDescription(
			'Check and run RebuildMessageIndex and MessageGroupStats update once ' .
			'UpdateMessageJobs are done. Intended to be run periodically'
		);
		$this->requireExtension( 'Translate' );
	}

	public function execute() {
		$mwServices = MediaWikiServices::getInstance();
		$config = $mwServices->getMainConfig();

		if ( !$config->get( 'TranslateGroupSynchronizationCache' ) ) {
			$this->fatalError( 'GroupSynchronizationCache is not enabled' );
		}

		$logger = LoggerFactory::getInstance( LogNames::GROUP_SYNCHRONIZATION );
		$groupSyncCache = Services::getInstance()->getGroupSynchronizationCache();
		$groupsInSync = $groupSyncCache->getGroupsInSync();
		if ( !$groupsInSync ) {
			$logger->debug( 'Nothing to synchronize' );
			$this->printSummaryInfo( $groupSyncCache, $logger, $groupsInSync );
			return;
		}

		$logger->info(
			'Group synchronization is in progress for {count} groups. Checking latest status...',
			[ 'count' => count( $groupsInSync ) ]
		);

		$groupsInProgress = [];
		foreach ( $groupsInSync as $groupId ) {
			$groupResponse = $groupSyncCache->getSynchronizationStatus( $groupId );

			if ( $groupResponse->isDone() ) {
				$groupSyncCache->endSync( $groupId );
				continue;
			}

			if ( $groupResponse->hasTimedOut() ) {
				$remainingMessages = $groupResponse->getRemainingMessages();
				$logger->warning(
					'UpdateMessageJobs timed out for group - {groupId}; ' .
					'Messages - {messages}; ' .
					'Jobs remaining - {jobRemaining}',
					[
						'groupId' => $groupId,
						'jobRemaining' => count( $remainingMessages ),
						'messages' => implode( ', ', array_keys( $remainingMessages ) )
					]
				);

				$count = count( $remainingMessages );
				wfLogWarning( "UpdateMessageJob timed out for group $groupId with $count message(s) remaining" );
				$groupSyncCache->forceEndSync( $groupId );

				$groupSyncCache->addGroupErrors( $groupResponse );

			} else {
				$groupsInProgress[] = $groupId;
			}
		}

		if ( !$groupsInProgress ) {
			// No groups in progress.
			$logger->info( 'All message groups are now in sync.' );
			$mwServices->getJobQueueGroup()->push( RebuildMessageIndexJob::newJob() );
		}

		$logger->info( "Script completed successfully." );
		$this->printSummaryInfo( $groupSyncCache, $logger, $groupsInProgress );
	}

	private function printSummaryInfo(
		GroupSynchronizationCache $groupSyncCache,
		LoggerInterface $logger,
		array $groupsInSync
	): void {
		$summaryMessage = [ 'Current group sync summary:' ];
		$summaryParams = [];

		$summaryMessage[] = '{syncCount} in sync: {syncGroups}';
		$summaryParams[ 'syncCount' ] = count( $groupsInSync );
		$summaryParams[ 'syncGroups' ] = $groupsInSync ? implode( ', ', $groupsInSync ) : 'N/A';

		$groupsInReview = $groupSyncCache->getGroupsInReview();
		$summaryMessage[] = '{reviewCount} in review: {reviewGroups}';
		$summaryParams[ 'reviewCount' ] = count( $groupsInReview );
		$summaryParams[ 'reviewGroups' ] = $groupsInReview ? implode( ', ', $groupsInReview ) : 'N/A';

		$groupsWithError = $groupSyncCache->getGroupsWithErrors();
		$summaryMessage[] = '{errorCount} with errors: {errorGroups}';
		$summaryParams[ 'errorCount' ] = count( $groupsWithError );
		$summaryParams[ 'errorGroups' ] = $groupsWithError ? implode( ', ', $groupsWithError ) : 'N/A';

		$logger->info(
			implode( '; ', $summaryMessage ),
			$summaryParams
		);
	}
}
