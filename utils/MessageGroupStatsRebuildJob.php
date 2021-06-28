<?php
/**
 * Contains class with job for rebuilding message group stats.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */

use MediaWiki\Extension\Translate\Jobs\GenericTranslateJob;
use MediaWiki\MediaWikiServices;

/**
 * Job for rebuilding message group stats.
 *
 * @ingroup JobQueue
 */
class MessageGroupStatsRebuildJob extends GenericTranslateJob {
	/**
	 * @param array $params
	 * @return self
	 */
	public static function newJob( $params ) {
		$job = new self( Title::newMainPage(), $params );
		return $job;
	}

	/**
	 * Force updating of message group stats for given groups.
	 *
	 * This uses cache for groups not given. If given groups have dependencies such
	 * as an aggregate group and it's subgroup, this attempts to take care of it so
	 * that no duplicate work is done.
	 *
	 * @param string[] $messageGroupIds
	 * @return self
	 */
	public static function newRefreshGroupsJob( array $messageGroupIds ) {
		return new self( Title::newMainPage(), [ 'cleargroups' => $messageGroupIds ] );
	}

	/**
	 * @param Title $title
	 * @param array $params
	 */
	public function __construct( $title, $params = [] ) {
		parent::__construct( __CLASS__, $title, $params );
	}

	public function run() {
		$lb = MediaWikiServices::getInstance()->getDBLoadBalancerFactory();
		if ( !$lb->waitForReplication() ) {
			$this->logWarning( 'Continuing despite replication lag' );
		}

		$params = $this->params;
		$flags = 0;

		// Sanity check that this is run via JobQueue. Immediate writes are only safe when they
		// are run in isolation, e.g. as a separate job in the JobQueue.
		if ( defined( 'MEDIAWIKI_JOB_RUNNER' ) ) {
			$flags |= MessageGroupStats::FLAG_IMMEDIATE_WRITES;
		}

		// This is to make sure the priority value is not read from the process cache.
		// There is still a possibility that, due to replication lag, an old value is read.
		MessageGroups::singleton()->clearProcessCache();

		if ( isset( $params[ 'purge' ] ) && $params[ 'purge' ] ) {
			$flags |= MessageGroupStats::FLAG_NO_CACHE;
		}

		if ( isset( $params[ 'groupid' ] ) ) {
			MessageGroupStats::forGroup( $params[ 'groupid' ], $flags );
		} elseif ( isset( $params[ 'cleargroups' ] ) ) {
			MessageGroupStats::clearGroup( $params[ 'cleargroups' ], $flags );
		} elseif ( isset( $params[ 'languagecode' ] ) ) {
			MessageGroupStats::forLanguage( $params[ 'languagecode' ], $flags );
		} else {
			throw new InvalidArgumentException( 'No groupid or languagecode or cleargroup provided' );
		}

		return true;
	}
}
