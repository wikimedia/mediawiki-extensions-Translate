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
	/// @var Array Configuration of requested TTMServer
	protected $config;

	public function __construct() {
		parent::__construct();
		$this->mDescription = 'Script to bootstrap TTMServer';
		$this->addOption( 'threads', 'Number of threads', /*required*/false, /*has arg*/true );
		$this->addOption( 'ttmserver', 'Server configuration identifier', /*required*/false, /*has arg*/true );
		$this->setBatchSize( 500 );
		$this->start = microtime( true );
	}

	protected function statusLine( $text, $channel = null ) {
		$pid = sprintf( "%5s", getmypid() );
		$prefix = sprintf( "%6.2f", microtime( true ) - $this->start );
		$mem = sprintf( "%5.1fM", ( memory_get_usage( true ) / ( 1024 * 1024 ) ) );
		$this->output( "$pid $prefix $mem  $text", $channel );
	}

	public function execute() {
		global $wgTranslateTranslationServices;

		// TTMServer is the id of the enabled-by-default instance
		$configKey = $this->getOption( 'ttmserver', 'TTMServer' );
		if ( !isset( $wgTranslateTranslationServices[$configKey] ) ) {
			$this->error( "Translation memory is not configured properly", 1 );
		}

		$this->config = $config = $wgTranslateTranslationServices[$configKey];
		$server = TTMServer::factory( $config );

		$this->statusLine( "Loading groups...\n" );
		$groups = MessageGroups::singleton()->getGroups();

		$threads = $this->getOption( 'threads', 1 );
		$pids = array();

		$this->statusLine( "Cleaning up old entries...\n" );
		$server->beginBootstrap();

		foreach ( $groups as $id => $group ) {
			/**
			 * @var MessageGroup $group
			 */
			if ( $group->isMeta() ) {
				continue;
			}

			// Fork to avoid unbounded memory usage growth
			$pid = pcntl_fork();

			if ( $pid === 0 ) {
				// Child, reseed because there is no bug in PHP:
				// http://bugs.php.net/bug.php?id=42465
				mt_srand( getmypid() );

				// Make sure all existing connections are dead,
				// we can't use them in forked children.
				LBFactory::destroyInstance();

				$this->exportGroup( $group );
				exit();
			} elseif ( $pid === -1 ) {
				// Fork failed do it serialized
				$this->exportGroup( $group );
			} else {
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

		$this->statusLine( "Creating indexes and optimizing...\n" );
		$server->endBootstrap();
	}

	protected function exportGroup( MessageGroup $group ) {
		$server = TTMServer::factory( $this->config );

		$id = $group->getId();
		$sourceLanguage = $group->getSourceLanguage();

		$stats = MessageGroupStats::forGroup( $id );

		$collection = $group->initCollection( $sourceLanguage );
		$collection->filter( 'ignored' );
		$collection->initMessages();

		$server->beginBatch();
		$inserts = array();

		foreach ( $collection->keys() as $mkey => $title ) {
			$def = $collection[$mkey]->definition();
			$inserts[$mkey] = array( $title, $sourceLanguage, $def );
		}

		do {
			$batch = array_splice( $inserts, 0, $this->mBatchSize );
			$server->batchInsertDefinitions( $batch );
		} while ( count( $inserts ) );

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
				$inserts[$mkey] = array( $title, $targetLanguage, $collection[$mkey]->translation() );
			}

			do {
				$batch = array_splice( $inserts, 0, $this->mBatchSize );
				$server->batchInsertTranslations( $batch );
			} while ( count( $inserts ) );
		}

		$server->endBatch();
	}
}

$maintClass = 'TTMServerBootstrap';
require_once RUN_MAINTENANCE_IF_MAIN;
