<?php
/**
 * Contains class with job for rebuilding message group stats.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */

/**
 * Job for rebuilding message index.
 *
 * @ingroup JobQueue
 */
class MessageGroupStatsRebuildJob extends Job {
	/**
	 * @param array $params
	 * @return self
	 */
	public static function newJob( $params ) {
		$job = new self( Title::newMainPage(), $params );
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
		$params = $this->params;
		$flags = 0;

		if ( isset( $params[ 'purge' ] ) && $params[ 'purge' ] ) {
			$flags |= MessageGroupStats::FLAG_NO_CACHE;
		}

		if ( isset( $params[ 'groupid' ] ) ) {
			MessageGroupStats::forGroup( $params[ 'groupid' ], $flags );
		}
		if ( isset( $params[ 'languagecode' ] ) ) {
			MessageGroupStats::forGroup( $params[ 'languagecode' ], $flags );
		}

		return true;
	}
}
