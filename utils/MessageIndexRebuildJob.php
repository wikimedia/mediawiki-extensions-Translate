<?php
/**
 * Contains class with job for rebuilding message index.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2011-2013, Niklas Laxström
 * @license GPL-2.0+
 */

/**
 * Job for rebuilding message index.
 *
 * @ingroup JobQueue
 */
class MessageIndexRebuildJob extends Job {

	/**
	 * @return MessageIndexRebuildJob
	 */
	public static function newJob() {
		$job = new self( Title::newMainPage() );

		return $job;
	}

	/**
	 * @param Title $title
	 * @param array $params
	 * @param int $id
	 */
	public function __construct( $title, $params = array(), $id = 0 ) {
		parent::__construct( __CLASS__, $title, $params, $id );
	}

	public function run() {
		MessageIndex::singleton()->rebuild();

		return true;
	}

	/**
	 * Usually this job is fast enough to be executed immediately,
	 * in which case having it go through jobqueue only causes problems
	 * in installations with errant job queue processing.
	 * @override
	 */
	public function insert() {
		global $wgTranslateDelayedMessageIndexRebuild;
		if ( $wgTranslateDelayedMessageIndexRebuild ) {
			JobQueueGroup::singleton()->push( $this );
		} else {
			$this->run();
		}
	}
}
