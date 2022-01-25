<?php

declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Synchronization;

use Maintenance;
use MediaWiki\Extension\Translate\Services;
use MediaWiki\Logger\LoggerFactory;
use MediaWiki\MediaWikiServices;
use MessageIndexRebuildJob;
use TranslateUtils;

/**
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 * @since 2020.06
 */
class CompleteExternalTranslationMaintenanceScript extends Maintenance {
	public function __construct() {
		parent::__construct();
		$this->addDescription(
			'Check and run MessageIndexRebuild and MessageGroupStats update once ' .
			'MessageUpdateJobs are done. Intended to be run periodically'
		);
		$this->requireExtension( 'Translate' );
	}

	public function execute() {
		$config = MediaWikiServices::getInstance()->getMainConfig();

		if ( !$config->get( 'TranslateGroupSynchronizationCache' ) ) {
			$this->fatalError( 'GroupSynchronizationCache is not enabled' );
		}

		$logger = LoggerFactory::getInstance( 'Translate.GroupSynchronization' );
		$groupSyncCache = Services::getInstance()->getGroupSynchronizationCache();
		$groupsInSync = $groupSyncCache->getGroupsInSync();
		if ( !$groupsInSync ) {
			$logger->info( 'All message groups are in sync' );
			return;
		}

		$logger->info( 'Group synchronization is in progress' );

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
					'MessageUpdateJobs timed out for group - {groupId}; ' .
					'Messages - {messages}; ' .
					'Jobs remaining - {jobRemaining}',
					[
						'groupId' => $groupId ,
						'jobRemaining' => count( $remainingMessages ),
						'messages' => implode( ', ', array_keys( $remainingMessages ) )
					]
				);

				$count = count( $remainingMessages );
				wfLogWarning( "MessageUpdateJob timed out for group $groupId with $count message(s) remaining" );
				$groupSyncCache->forceEndSync( $groupId );

				$groupSyncCache->addGroupErrors( $groupResponse );

			} else {
				$groupsInProgress[] = $groupId;
			}
		}

		if ( !$groupsInProgress ) {
			// No groups in progress.
			$logger->info( 'All message groups are now in sync.' );
			TranslateUtils::getJobQueueGroup()->push( MessageIndexRebuildJob::newJob() );
		}

		$logger->info(
			"Script completed successfully. " .
			"{inProgressGroupCount} group synchronization(s) is/are in progress",
			[
				'inProgressGroupCount' => count( $groupsInProgress )
			]
		);
	}
}
