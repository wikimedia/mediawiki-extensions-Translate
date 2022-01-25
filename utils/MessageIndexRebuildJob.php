<?php
/**
 * Contains class with job for rebuilding message index.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2011-2013, Niklas Laxström
 * @license GPL-2.0-or-later
 */

use MediaWiki\Extension\Translate\Jobs\GenericTranslateJob;

/**
 * Job for rebuilding message index.
 *
 * @ingroup JobQueue
 */
class MessageIndexRebuildJob extends GenericTranslateJob {
	/** @return self */
	public static function newJob() {
		$timestamp = microtime( true );
		$job = new self( Title::newMainPage(), [ 'timestamp' => $timestamp ] );

		return $job;
	}

	/**
	 * @param Title $title
	 * @param array $params
	 */
	public function __construct( $title, $params = [] ) {
		parent::__construct( __CLASS__, $title, $params );
		$this->removeDuplicates = true;
	}

	public function run() {
		// Make sure we have latest version of message groups from global cache.
		// This should be pretty fast, just a few cache fetches with some post processing.
		MessageGroups::singleton()->clearProcessCache();

		// BC for existing jobs which may not have this parameter set
		$timestamp = $this->getParams()['timestamp'] ?? microtime( true );

		try {
			MessageIndex::singleton()->rebuild( $timestamp );
		} catch ( MessageIndexException $e ) {
			// Currently there is just one type of exception: lock wait time exceeded.
			// Assuming no bugs, this is a transient issue and retry will solve it.
			$this->logWarning( $e->getMessage() );
			// Try again later. See ::allowRetries
			return false;
		}

		return true;
	}

	/** @inheritDoc */
	public function allowRetries() {
		// This is the default, but added for explicitness and clarity
		return true;
	}

	/** @inheritDoc */
	public function getDeduplicationInfo() {
		$info = parent::getDeduplicationInfo();
		// The timestamp is different for every job, so ignore it. The worst that can
		// happen is that the front cache is not cleared until a future job is created.
		// There is a check in MessageIndex to spawn a new job if timestamp is smaller
		// than expected.
		//
		// Ideally we would take the latest timestamp, but it seems that the job queue
		// just prevents insertion of duplicate jobs instead.
		unset( $info['params']['timestamp'] );

		return $info;
	}

	/** @deprecated Just push directly to the job queue */
	public function insertIntoJobQueue(): void {
		TranslateUtils::getJobQueueGroup()->push( $this );
	}
}
