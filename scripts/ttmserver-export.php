<?php
/**
 * Script to bootstrap TTMServer translation memory
 *
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @file
 */

// Standard boilerplate to define $IP
if ( getenv( 'MW_INSTALL_PATH' ) !== false ) {
	$IP = getenv( 'MW_INSTALL_PATH' );
} else {
	$dir = __DIR__;
	$IP = "$dir/../../..";
}
require_once "$IP/maintenance/Maintenance.php";

/**
 * Script to bootstrap TTMServer translation memory.
 * @since 2012-01-26
 */
class TTMServerBootstrap extends Maintenance {
	/** @var int */
	private $start;

	public function __construct() {
		parent::__construct();
		$this->addDescription( 'Script to bootstrap TTMServer.' );
		$this->addOption(
			'threads',
			'(optional) Number of threads',
			/*required*/false,
			/*has arg*/true
		);
		$this->addOption(
			'ttmserver',
			'(optional) Server configuration identifier',
			/*required*/false,
			/*has arg*/true
		);
		// This option erases all data, empties the index and rebuilds it.
		$this->addOption(
			'reindex',
			'Update the index mapping. Warning: Clears all existing data in the index.'
		);
		$this->addOption(
			'dry-run',
			'Do not make any changes to the index.'
		);
		$this->addOption(
			'verbose',
			'Output more status information.'
		);
		$this->setBatchSize( 500 );
		$this->requireExtension( 'Translate' );
		$this->start = microtime( true );
	}

	public function statusLine( $text, $channel = null ) {
		$pid = sprintf( '%5s', getmypid() );
		$prefix = sprintf( '%6.2f', microtime( true ) - $this->start );
		$mem = sprintf( '%5.1fM', memory_get_usage( true ) / ( 1024 * 1024 ) );
		$this->output( "$pid $prefix $mem  $text", $channel );
	}

	public function execute() {
		global $wgTranslateTranslationServices,
			$wgTranslateTranslationDefaultService;

		$configKey = $this->getOption( 'ttmserver', $wgTranslateTranslationDefaultService );
		if ( !isset( $wgTranslateTranslationServices[$configKey] ) ) {
			$this->fatalError( 'Translation memory is not configured properly' );
		}

		$dryRun = $this->getOption( 'dry-run' );
		if ( $dryRun ) {
			$config = [ 'class' => FakeTTMServer::class ];
		} else {
			$config = $wgTranslateTranslationServices[$configKey];
		}

		$server = $this->getServer( $config );
		$this->logInfo( "Implementation: " . get_class( $server ) . "\n" );

		// Do as little as possible in the main thread, to not clobber forked processes.
		// See also #resetStateForFork.
		$pid = pcntl_fork();
		if ( $pid === 0 ) {
			$this->resetStateForFork();
			$server = $this->getServer( $config );
			$this->beginBootstrap( $server );
			exit();
		} elseif ( $pid === -1 ) {
			// Fork failed do it serialized
			$this->beginBootstrap( $server );
		} else {
			// Main thread
			$this->statusLine( "Forked thread $pid to handle bootstrapping\n" );
			$status = 0;
			pcntl_waitpid( $pid, $status );
			// beginBootstrap probably failed, give up.
			if ( !$this->verifyChildStatus( $pid, $status ) ) {
				$this->fatalError( 'Bootstrap failed.' );
			}
		}

		$hasErrors = false;
		$threads = $this->getOption( 'threads', 1 );
		$pids = [];

		$groups = MessageGroups::singleton()->getGroups();
		foreach ( $groups as $id => $group ) {
			/** @var MessageGroup $group */
			if ( $group->isMeta() ) {
				continue;
			}

			// Fork to increase speed with parallelism. Also helps with memory usage if there are leaks.
			$pid = pcntl_fork();

			if ( $pid === 0 ) {
				$this->resetStateForFork();
				$server = $this->getServer( $config );
				$this->exportGroup( $group, $server );
				exit();
			} elseif ( $pid === -1 ) {
				// Fork failed do it serialized
				$this->exportGroup( $group, $server );
			} else {
				// Main thread
				$this->statusLine( "Forked thread $pid to handle $id\n" );
				$pids[$pid] = true;

				// If we hit the thread limit, wait for any child to finish.
				if ( count( $pids ) >= $threads ) {
					$status = 0;
					$pid = pcntl_wait( $status );
					$hasErrors = $hasErrors || !$this->verifyChildStatus( $pid, $status );
					unset( $pids[$pid] );
				}
			}
		}

		// Return control after all threads have finished.
		foreach ( array_keys( $pids ) as $pid ) {
			$status = 0;
			pcntl_waitpid( $pid, $status );
			$hasErrors = $hasErrors || !$this->verifyChildStatus( $pid, $status );
		}

		// It's okay to do this in the main thread as it is the last thing
		$this->endBootstrap( $server );

		if ( $hasErrors ) {
			$this->fatalError( '!!! Some threads failed. Review the script output !!!' );
		}
	}

	private function getServer( array $config ): WritableTTMServer {
		$server = TTMServer::factory( $config );
		if ( !$server instanceof WritableTTMServer ) {
			$this->fatalError( "Service must implement WritableTTMServer" );
		}

		if ( is_callable( [ $server, 'setLogger' ] ) ) {
			// Phan, why you so strict?
			// @phan-suppress-next-line PhanUndeclaredMethod
			$server->setLogger( $this );
		}

		if ( $server->isFrozen() ) {
			$this->fatalError( "The service is frozen, giving up." );
		}

		if ( $this->getOption( 'reindex', false ) ) {
			// This doesn't do the update, just sets a flag to do it
			$server->setDoReIndex();
		}

		return $server;
	}

	protected function beginBootstrap( WritableTTMServer $server ) {
		$this->statusLine( "Cleaning up old entries...\n" );
		$server->beginBootstrap();
	}

	protected function endBootstrap( WritableTTMServer $server ) {
		$this->statusLine( "Optimizing...\n" );
		$server->endBootstrap();
	}

	protected function exportGroup( MessageGroup $group, WritableTTMServer $server ) {
		$times = [
			'total' => -microtime( true ),
			'stats' => 0,
			'init' => 0,
			'trans' => 0,
		];
		$countItems = 0;

		$id = $group->getId();
		$sourceLanguage = $group->getSourceLanguage();

		$times[ 'stats' ] -= microtime( true );
		$stats = MessageGroupStats::forGroup( $id );
		$times[ 'stats' ] += microtime( true );

		$times[ 'init' ] -= microtime( true );
		$collection = $group->initCollection( $sourceLanguage );
		$collection->filter( 'ignored' );
		$collection->initMessages();

		$server->beginBatch();
		$inserts = [];
		foreach ( $collection->keys() as $mkey => $titleValue ) {
			$title = Title::newFromLinkTarget( $titleValue );
			$handle = new MessageHandle( $title );
			$inserts[] = [ $handle, $sourceLanguage, $collection[$mkey]->definition() ];
			$countItems++;
		}

		while ( $inserts !== [] ) {
			$batch = array_splice( $inserts, 0, $this->mBatchSize );
			$server->batchInsertDefinitions( $batch );
		}
		$inserts = [];
		$times[ 'init' ] += microtime( true );

		$times[ 'trans' ] -= microtime( true );
		foreach ( $stats as $targetLanguage => $numbers ) {
			if ( $targetLanguage === $sourceLanguage ) {
				continue;
			}
			if ( $numbers[MessageGroupStats::TRANSLATED] === 0 ) {
				continue;
			}

			$collection->resetForNewLanguage( $targetLanguage );
			$collection->filter( 'ignored' );
			$collection->filter( 'translated', false );
			$collection->loadTranslations();

			foreach ( $collection->keys() as $mkey => $titleValue ) {
				$title = Title::newFromLinkTarget( $titleValue );
				$handle = new MessageHandle( $title );
				$inserts[] = [ $handle, $sourceLanguage, $collection[$mkey]->translation() ];
				$countItems++;
			}

			while ( count( $inserts ) >= $this->mBatchSize ) {
				$batch = array_splice( $inserts, 0, $this->mBatchSize );
				$server->batchInsertTranslations( $batch );
			}
		}

		while ( $inserts !== [] ) {
			$batch = array_splice( $inserts, 0, $this->mBatchSize );
			$server->batchInsertTranslations( $batch );
		}

		$server->endBatch();
		$times[ 'trans' ] += microtime( true );
		$times[ 'total' ] += microtime( true );

		$debug = sprintf(
			"Total %.1f s for %d items >> stats/init/trans %%: %d/%d/%d >> %.1f ms/item",
			$times[ 'total' ],
			$countItems,
			$times[ 'stats'] / $times[ 'total' ] * 100,
			$times[ 'init'] / $times[ 'total' ] * 100,
			$times[ 'trans'] / $times[ 'total' ] * 100,
			$times[ 'total' ] / $countItems * 1000
		);

		$this->logInfo( "Finished exporting $id. $debug\n" );
	}

	private function logInfo( string $text ) {
		if ( $this->getOption( 'verbose', false ) ) {
			$this->statusLine( $text );
		}
	}

	protected function resetStateForFork() {
		// Make sure all existing connections are dead,
		// we can't use them in forked children.
		MediaWiki\MediaWikiServices::resetChildProcessServices();
	}

	private function verifyChildStatus( int $pid, int $status ): bool {
		if ( pcntl_wifexited( $status ) ) {
			$code = pcntl_wexitstatus( $status );
			if ( $code ) {
				$this->output( "Pid $pid exited with status $code !!\n" );
				return false;
			}
		} elseif ( pcntl_wifsignaled( $status ) ) {
			$signum = pcntl_wtermsig( $status );
			$this->output( "Pid $pid terminated by signal $signum !!\n" );
			return false;
		}

		return true;
	}
}

$maintClass = TTMServerBootstrap::class;
require_once RUN_MAINTENANCE_IF_MAIN;
