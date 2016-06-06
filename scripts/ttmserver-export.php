<?php
/**
 * Script to bootstrap TTMServer translation memory
 *
 * @author Niklas Laxström
 * @license GPL-2.0+
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
	/**
	 * @var bool Option for reindexing
	 */
	protected $reindex;

	public function __construct() {
		parent::__construct();
		$this->mDescription = 'Script to bootstrap TTMServer.';
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
		$this->setBatchSize( 500 );
		$this->start = microtime( true );
	}

	public function statusLine( $text, $channel = null ) {
		$pid = sprintf( '%5s', getmypid() );
		$prefix = sprintf( '%6.2f', microtime( true ) - $this->start );
		$mem = sprintf( '%5.1fM', ( memory_get_usage( true ) / ( 1024 * 1024 ) ) );
		$this->output( "$pid $prefix $mem  $text", $channel );
	}

	public function execute() {
		global $wgTranslateTranslationServices;

		// TTMServer is the id of the enabled-by-default instance
		$configKey = $this->getOption( 'ttmserver', 'TTMServer' );
		if ( !isset( $wgTranslateTranslationServices[$configKey] ) ) {
			$this->error( 'Translation memory is not configured properly', 1 );
		}

		$config = $wgTranslateTranslationServices[$configKey];
		$this->reindex = $this->getOption( 'reindex', false );

		// Do as little as possible in the main thread, to not clobber forked processes.
		// See also #resetStateForFork.
		$pid = pcntl_fork();
		if ( $pid === 0 ) {
			$this->resetStateForFork();
			$this->beginBootStrap( $config );
			exit();
		} elseif ( $pid === -1 ) {
			// Fork failed do it serialized
			$this->beginBootStrap( $config );
		} else {
			// Main thread
			$this->statusLine( "Forked thread $pid to handle bootstrapping\n" );
			$status = 0;
			pcntl_waitpid( $pid, $status );
		}

		$threads = $this->getOption( 'threads', 1 );
		$pids = array();

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
				$this->exportGroup( $group, $config );
				exit();
			} elseif ( $pid === -1 ) {
				// Fork failed do it serialized
				$this->exportGroup( $group, $config );
			} else {
				// Main thread
				$this->statusLine( "Forked thread $pid to handle $id\n" );
				$pids[$pid] = true;

				// If we hit the thread limit, wait for any child to finish.
				if ( count( $pids ) >= $threads ) {
					$status = 0;
					$pid = pcntl_wait( $status );
					unset( $pids[$pid] );
				}
			}
		}

		// Return control after all threads have finished.
		foreach ( array_keys( $pids ) as $pid ) {
			$status = 0;
			pcntl_waitpid( $pid, $status );
		}

		// It's okay to do this in the main thread as it is the last thing
		$this->endBootstrap( $config );
	}

	protected function beginBootStrap( $config ) {
		$this->statusLine( "Cleaning up old entries...\n" );
		$server = TTMServer::factory( $config );
		$server->setLogger( $this );
		if ( $this->reindex ) {
			$server->doMappingUpdate();
		}
		$server->beginBootstrap();
	}

	protected function endBootstrap( $config ) {
		$this->statusLine( "Optimizing...\n" );
		$server = TTMServer::factory( $config );
		$server->setLogger( $this );
		$server->endBootstrap();
	}

	protected function exportGroup( MessageGroup $group, $config ) {
		$server = TTMServer::factory( $config );
		$server->setLogger( $this );

		$id = $group->getId();
		$sourceLanguage = $group->getSourceLanguage();

		$stats = MessageGroupStats::forGroup( $id );

		$collection = $group->initCollection( $sourceLanguage );
		$collection->filter( 'ignored' );
		$collection->initMessages();

		$server->beginBatch();

		$inserts = array();
		foreach ( $collection->keys() as $mkey => $title ) {
			$handle = new MessageHandle( $title );
			$inserts[] = array( $handle, $sourceLanguage, $collection[$mkey]->definition() );
		}

		while ( $inserts !== array() ) {
			$batch = array_splice( $inserts, 0, $this->mBatchSize );
			$server->batchInsertDefinitions( $batch );
		}

		$inserts = array();
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

			foreach ( $collection->keys() as $mkey => $title ) {
				$handle = new MessageHandle( $title );
				$inserts[] = array( $handle, $sourceLanguage, $collection[$mkey]->translation() );
			}

			while ( count( $inserts ) >= $this->mBatchSize ) {
				$batch = array_splice( $inserts, 0, $this->mBatchSize );
				$server->batchInsertTranslations( $batch );
			}
		}

		while ( $inserts !== array() ) {
			$batch = array_splice( $inserts, 0, $this->mBatchSize );
			$server->batchInsertTranslations( $batch );
		}

		$server->endBatch();
	}

	protected function resetStateForFork() {
		// Make sure all existing connections are dead,
		// we can't use them in forked children.
		if ( method_exists( 'MediaWiki\MediaWikiServices', 'resetChildProcessServices' ) ) {
			MediaWiki\MediaWikiServices::resetChildProcessServices();
		} else {
			// BC for MediaWiki <= 1.27
			LBFactory::destroyInstance();

			// Child, reseed because there is no bug in PHP:
			// http://bugs.php.net/bug.php?id=42465
			mt_srand( getmypid() );
		}
	}
}

$maintClass = 'TTMServerBootstrap';
require_once RUN_MAINTENANCE_IF_MAIN;
