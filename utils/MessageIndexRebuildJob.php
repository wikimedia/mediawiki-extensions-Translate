<?php
/**
 * Contains class with job for rebuilding message index.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2011-2013, Niklas Laxström
 * @license GPL-2.0-or-later
 */

/**
 * Job for rebuilding message index.
 *
 * @ingroup JobQueue
 */
class MessageIndexRebuildJob extends Job {
	/**
	 * @return self
	 */
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
	}

	public function run() {
		// BC for existing jobs which may not have this parameter set
		$timestamp = $this->getParams()['timestamp'] ?? microtime( true );
		MessageIndex::singleton()->rebuild( $timestamp );

		return true;
	}

	/**
	 * Usually this job is fast enough to be executed immediately,
	 * in which case having it go through jobqueue only causes problems
	 * in installations with errant job queue processing.
	 */
	public function insertIntoJobQueue() {
		global $wgTranslateDelayedMessageIndexRebuild;
		if ( $wgTranslateDelayedMessageIndexRebuild ) {
			JobQueueGroup::singleton()->push( $this );
		} else {
			$this->run();
		}
	}
}
