<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageLoading;

use GenericParameterJob;
use MediaWiki\Extension\Translate\Jobs\GenericTranslateJob;
use MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroups;
use MediaWiki\Extension\Translate\Services;
use MediaWiki\MediaWikiServices;

/**
 * Job for rebuilding message index.
 * @author Niklas Laxström
 * @copyright Copyright © 2011-2013, Niklas Laxström
 * @license GPL-2.0-or-later
 * @ingroup JobQueue
 */
class RebuildMessageIndexJob extends GenericTranslateJob implements GenericParameterJob {
	public static function newJob( ?string $caller = null ): self {
		$timestamp = microtime( true );
		return new self( [
			'timestamp' => $timestamp,
			'caller' => $caller ?? wfGetCaller(),
		] );
	}

	/** @inheritDoc */
	public function __construct( $params = [] ) {
		parent::__construct( 'RebuildMessageIndexJob', $params );
		$this->removeDuplicates = true;
	}

	public function run(): bool {
		$lb = MediaWikiServices::getInstance()->getDBLoadBalancerFactory();
		if ( !$lb->waitForReplication() ) {
			$this->logWarning( 'Continuing despite replication lag' );
		}

		// Make sure we have latest version of message groups from global cache.
		// This should be pretty fast, just a few cache fetches with some post processing.
		MessageGroups::singleton()->clearProcessCache();

		// BC for existing jobs which may not have this parameter set
		$timestamp = $this->getParams()['timestamp'] ?? microtime( true );

		try {
			Services::getInstance()->getMessageIndex()->rebuild( $timestamp );
		} catch ( MessageIndexException $e ) {
			// Currently there is just one type of exception: lock wait time exceeded.
			// Assuming no bugs, this is a transient issue and retry will solve it.
			$this->logWarning( $e->getMessage() );
			// Try again later. See ::allowRetries
			return false;
		}

		return true;
	}

	public function allowRetries(): bool {
		// This is the default, but added for explicitness and clarity
		return true;
	}

	public function getDeduplicationInfo(): array {
		$info = parent::getDeduplicationInfo();
		// The timestamp is different for every job, so ignore it. The worst that can
		// happen is that the front cache is not cleared until a future job is created.
		// There is a check in MessageIndex to spawn a new job if timestamp is smaller
		// than expected.
		//
		// Ideally we would take the latest timestamp, but it seems that the job queue
		// just prevents insertion of duplicate jobs instead.
		unset( $info['params']['timestamp'] );
		unset( $info['params']['caller'] );

		return $info;
	}
}
