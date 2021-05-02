<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Utilities;

use MediaWiki\MediaWikiServices;

/**
 * Helper class for maintenance scripts to run things in parallel.
 *
 * See also ForkContoller and https://phabricator.wikimedia.org/T201970.
 *
 * @since 2020.11
 * @license GPL-2.0-or-later
 * @author Niklas Laxström
 */
class ParallelExecutor {
	/** @var int[] */
	private $pids = [];
	/** @var int */
	private $threads;

	public function __construct( int $threads = 1 ) {
		$this->threads = $threads;
	}

	public function runInParallel( callable $mainThread, callable $forkThread ): void {
		// Fork to increase speed with parallelism. Also helps with memory usage if there are leaks.
		$pid = -1;
		if ( function_exists( 'pcntl_fork' ) ) {
			$pid = pcntl_fork();
		}

		if ( $pid === 0 ) {
			MediaWikiServices::resetChildProcessServices();
			$forkThread();
			exit();
		} elseif ( $pid === -1 ) {
			// Fork failed do it serialized
			$forkThread();
		} else {
			// Main thread
			$mainThread( $pid );
			$this->pids[$pid] = true;

			// If we hit the thread limit, wait for any child to finish.
			if ( count( $this->pids ) >= $this->threads ) {
				$status = 0;
				$pid = pcntl_wait( $status );
				unset( $this->pids[$pid] );
			}
		}
	}
}
