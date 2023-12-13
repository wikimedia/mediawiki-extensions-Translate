<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Statistics;

use InvalidArgumentException;
use MediaWiki\Extension\Translate\Jobs\GenericTranslateJob;
use MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroups;
use MediaWiki\MediaWikiServices;
use MediaWiki\Title\Title;
use MessageGroupStats;

/**
 * Job for rebuilding message group stats.
 *
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @ingroup JobQueue
 */
class RebuildMessageGroupStatsJob extends GenericTranslateJob {
	/** @inheritdoc */
	protected $removeDuplicates = true;

	public static function newJob( array $params ): self {
		return new self( Title::newMainPage(), $params );
	}

	/**
	 * Force updating of message group stats for given groups.
	 *
	 * This uses cache for groups not given. If given groups have dependencies such
	 * as an aggregate group and it's subgroup, this attempts to take care of it so
	 * that no duplicate work is done.
	 *
	 * @param string[] $messageGroupIds
	 */
	public static function newRefreshGroupsJob( array $messageGroupIds ): self {
		return new self( Title::newMainPage(), [ 'cleargroups' => $messageGroupIds ] );
	}

	public function __construct( Title $title, array $params = [] ) {
		parent::__construct( 'RebuildMessageGroupStatsJob', $title, $params );
	}

	public function run(): bool {
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
