<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageBundleTranslation;

use Job;
use MediaWiki\Extension\Translate\Services;
use MediaWiki\Logger\LoggerFactory;
use MediaWiki\MediaWikiServices;
use MessageGroups;
use MessageGroupStats;
use MessageIndexRebuildJob;
use MessageUpdateJob;
use Title;

/**
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @since 2021.12
 */
class UpdateMessageBundleJob extends Job {
	/** @inheritDoc */
	public function __construct( Title $title, $params = [] ) {
		parent::__construct( 'UpdateMessageBundle', $title, $params );
	}

	public static function newJob( Title $bundlePageTitle, int $revisionId, ?int $previousRevisionId ): self {
		return new self(
			$bundlePageTitle,
			[
				'revisionId' => $revisionId,
				'previousRevisionId' => $previousRevisionId,
			]
		);
	}

	/** @inheritDoc */
	public function run(): void {
		$mwInstance = MediaWikiServices::getInstance();
		$lb = $mwInstance->getDBLoadBalancerFactory();
		$jobQueue = $mwInstance->getJobQueueGroup();
		$logger = LoggerFactory::getInstance( 'Translate.MessageBundle' );
		$messageIndex = Services::getInstance()->getMessageIndex();

		$logger->info( 'UpdateMessageBundleJob: Starting job for: ' . $this->getTitle()->getPrefixedText() );

		// Not sure if this is necessary, but it should ensure that this job, which was created
		// when a revision was saved, can read that revision from the replica. In addition, this
		// may potentially do a bunch of more writes that could cause more replication lag.
		if ( !$lb->waitForReplication() ) {
			$logger->warning( 'UpdateMessageBundleJob: Continuing despite replication lag' );
		}

		// Setup
		$bundlePageTitle = $this->getTitle();
		$name = $bundlePageTitle->getPrefixedText();
		$pageId = $bundlePageTitle->getId();
		$groupId = MessageBundleMessageGroup::getGroupId( $name );
		$params = $this->getParams();
		// We don't care about the group description, so no need to pass it through
		$group = new MessageBundleMessageGroup( $groupId, $name, $pageId, $params['revisionId'], null );
		$messages = $group->getDefinitions();
		$previousMessages = [];
		if ( $params['previousRevisionId'] ) {
			$groupPreviousVersion = new MessageBundleMessageGroup(
				$groupId, $name, $pageId, $params['previousRevisionId'], null
			);
			$previousMessages = $groupPreviousVersion->getDefinitions();
		}

		// Fill in the front-cache. Ideally this should be done right away, but hopefully
		// this is okay since we only trigger message group cache rebuild later in this job.
		// It's possible that some other change triggers it earlier and makes the new group
		// available before this step is complete.
		$newKeys = array_diff( array_keys( $messages ), array_keys( $previousMessages ) );
		$messageIndex->storeInterim( $group, $newKeys );

		// Create jobs that will update the '/' source language pages. These pages should
		// exist so that the editor can show differences for changed messages. Also compare
		// against previous version (if any) to determine whether to mark translations as
		// outdated. There is no support for renames.
		$jobs = [];
		$namespace = $group->getNamespace();
		$code = $group->getSourceLanguage();
		foreach ( $messages as $key => $value ) {
			$title = Title::makeTitle( $namespace, "$key/$code" );
			$previousValue = $previousMessages[$key] ?? null;
			$fuzzy = $previousMessages !== null && $previousValue !== $value;
			$jobs[] = MessageUpdateJob::newJob( $title, $value, $fuzzy );
		}
		$jobQueue->push( $jobs );
		$logger->info(
			'UpdateMessageBundleJob: Added {number} MessageUpdateJobs to the job queue for: {title}',
			[
				'number' => count( $jobs ),
				'title' => $name
			]
		);

		// This is somewhat slow, so it has been postponed until now, but it's needed to
		// make the group available for the message index rebuild.
		MessageGroups::singleton()->recache();

		$logger->info(
			'UpdateMessageBundleJob: {title}: Recaching message groups',
			[ 'title' => $name ]
		);

		// Schedule message index update. Thanks to front caching, it is okay if this takes
		// a while (and on large wikis it does take a while!). Running it as a separate job
		// also allows de-duplication.
		$job = MessageIndexRebuildJob::newJob();
		$jobQueue->push( $job );
		$logger->info(
			'UpdateMessageBundleJob: {title}: Queue MessageIndexRebuildJob',
			[ 'title' => $name ]
		);

		// Refresh or fill translations statistics. If this a new group, this prevents
		// calculating the stats on the fly during read requests. If an existing group, this
		// makes sure that the statistics are up-to-date.
		MessageGroupStats::forGroup(
			$groupId,
			MessageGroupStats::FLAG_NO_CACHE | MessageGroupStats::FLAG_IMMEDIATE_WRITES
		);
	}
}
