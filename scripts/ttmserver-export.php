<?php
/**
 * Script to bootstrap TtmServer translation memory
 *
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @file
 */

use MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroups;
use MediaWiki\Extension\Translate\MessageLoading\MessageCollection;
use MediaWiki\Extension\Translate\MessageLoading\MessageHandle;
use MediaWiki\Extension\Translate\Services;
use MediaWiki\Extension\Translate\Statistics\MessageGroupStats;
use MediaWiki\Extension\Translate\TtmServer\FakeTtmServer;
use MediaWiki\Extension\Translate\TtmServer\ServiceCreationFailure;
use MediaWiki\Extension\Translate\TtmServer\WritableTtmServer;
use MediaWiki\Maintenance\Maintenance;
use MediaWiki\Title\Title;
use Wikimedia\Assert\Assert;

// Standard boilerplate to define $IP
if ( getenv( 'MW_INSTALL_PATH' ) !== false ) {
	$IP = getenv( 'MW_INSTALL_PATH' );
} else {
	$dir = __DIR__;
	$IP = "$dir/../../..";
}
require_once "$IP/maintenance/Maintenance.php";

/**
 * Script to bootstrap TtmServer translation memory.
 * @since 2012-01-26
 */
class TTMServerBootstrap extends Maintenance {
	private float $start;
	private const FAKE_TTM = 'dry-run';

	public function __construct() {
		parent::__construct();
		$this->addDescription( 'Script to bootstrap TtmServer.' );
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
		$this->addOption(
			'clean',
			'Only run setup and and cleanup. Skip inserting content.'
		);
		$this->setBatchSize( 500 );
		$this->requireExtension( 'Translate' );
		$this->start = microtime( true );
	}

	public function statusLine( string $text, ?string $channel = null ) {
		$pid = sprintf( '%5s', getmypid() );
		$prefix = sprintf( '%6.2f', microtime( true ) - $this->start );
		$mem = sprintf( '%5.1fM', memory_get_usage( true ) / ( 1024 * 1024 ) );
		$this->output( "$pid $prefix $mem  $text", $channel );
	}

	public function execute() {
		$dryRun = $this->hasOption( 'dry-run' );
		$ttmServerId = $this->getOption( 'ttmserver' );
		$shouldReindex = $this->getOption( 'reindex', false );

		if ( $this->mBatchSize !== null && $this->mBatchSize < 1 ) {
			$this->fatalError( 'Invalid value for option: "batch-size"' );
		}

		$servers = $this->getServers( $dryRun, $shouldReindex, $ttmServerId );

		// Do as little as possible in the main thread, to not clobber forked processes.
		// See also #resetStateForFork.
		foreach ( array_keys( $servers ) as $serverId ) {
			$pid = pcntl_fork();

			if ( $pid === 0 ) {
				$server = $this->getWritableServer( $serverId );
				$this->resetStateForFork();
				$this->beginBootstrap( $server, $serverId );
				exit();
			} elseif ( $pid === -1 ) {
				// Fork failed do it serialized
				$server = $this->getWritableServer( $serverId );
				$this->beginBootstrap( $server, $serverId );
			} else {
				// Main thread
				$this->statusLine( "Forked thread $pid to handle bootstrapping for '$serverId'\n" );
				$status = 0;
				pcntl_waitpid( $pid, $status );
				// beginBootstrap probably failed, give up.
				if ( !$this->verifyChildStatus( $pid, $status ) ) {
					$this->fatalError( "Bootstrap failed for '$serverId'." );
				}
			}
		}

		$hasErrors = false;
		$threads = $this->getOption( 'threads', 1 );
		$pids = [];

		if ( $this->hasOption( 'clean' ) ) {
			$groups = [];
		} else {
			$groups = MessageGroups::singleton()->getGroups();
		}

		foreach ( $groups as $id => $group ) {
			/** @var MessageGroup $group */
			if ( $group->isMeta() ) {
				continue;
			}

			// Fork to increase speed with parallelism. Also helps with memory usage if there are leaks.
			$pid = pcntl_fork();
			if ( $pid === 0 ) {
				$this->resetStateForFork();
				$this->exportGroup( $group, $servers );
				exit();
			} elseif ( $pid === -1 ) {
				$this->exportGroup( $group, $servers );
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
		$this->endBootstrap( $servers );

		if ( $hasErrors ) {
			$this->fatalError( '!!! Some threads failed. Review the script output !!!' );
		}
	}

	/**
	 * @param bool $isDryRun
	 * @param bool $shouldReindex
	 * @param string|null $ttmServerId
	 * @return WritableTtmServer[]
	 */
	private function getServers(
		bool $isDryRun,
		bool $shouldReindex,
		?string $ttmServerId = null
	): array {
		$servers = [];
		$ttmServerFactory = Services::getInstance()->getTtmServerFactory();
		if ( $isDryRun ) {
			$servers = [ self::FAKE_TTM => new FakeTtmServer() ];
		} else {
			if ( $ttmServerId !== null ) {
				try {
					$servers[ $ttmServerId ] = $ttmServerFactory->create( $ttmServerId );
				} catch ( ServiceCreationFailure $e ) {
					$this->fatalError( "Error while creating TtmServer $ttmServerId: " . $e->getMessage() );
				}
			} else {
				$servers = $ttmServerFactory->getWritable();
			}
		}

		if ( !$servers ) {
			$this->fatalError( "No writable TtmServers found." );
		}

		foreach ( $servers as $server ) {
			Assert::parameterType( WritableTtmServer::class, $server, '$server' );

			if ( method_exists( $server, 'setLogger' ) ) {
				// @phan-suppress-next-line PhanUndeclaredMethod
				$server->setLogger( $this );
			}

			if ( $shouldReindex ) {
				// This doesn't do the update, just sets a flag to do it
				$server->setDoReIndex();
			}
		}

		return $servers;
	}

	protected function beginBootstrap( WritableTtmServer $server, string $serverId ) {
		$this->statusLine( "Cleaning up old entries in '$serverId'...\n" );
		$server->beginBootstrap();
	}

	protected function endBootstrap( array $servers ) {
		foreach ( $servers as $serverId => $server ) {
			$this->statusLine( "Optimizing '$serverId'...\n" );
			$server->endBootstrap();
		}
	}

	/**
	 * @param MessageGroup $group
	 * @param WritableTtmServer[] $servers
	 * @return void
	 */
	private function exportGroup( MessageGroup $group, array $servers ): void {
		$times = [
			'total' => -microtime( true ),
			'stats' => 0,
			'init' => 0,
			'trans' => 0,
			'writes' => 0
		];
		$transWrites = 0;

		$sourceLanguage = $group->getSourceLanguage();

		$times[ 'init' ] -= microtime( true );
		$collection = $this->getCollection( $group, $sourceLanguage );
		$times[ 'init' ] += microtime( true );

		$times[ 'stats' ] -= microtime( true );
		$stats = MessageGroupStats::forGroup( $group->getId() );
		$times[ 'stats' ] += microtime( true );
		unset( $stats[ $sourceLanguage ] );

		$translationCount = $definitionCount = 0;

		foreach ( $servers as $server ) {
			$server->beginBatch();
		}

		foreach ( $this->getDefinitions( $collection, $sourceLanguage ) as $batch ) {
			$definitionCount += count( $batch );
			foreach ( $servers as $server ) {
				$times[ 'writes' ] -= microtime( true );
				$server->batchInsertDefinitions( $batch );
				$times[ 'writes' ] += microtime( true );
			}
		}

		$times[ 'trans' ] -= microtime( true );
		foreach ( $stats as $targetLanguage => $numbers ) {
			if ( $numbers[MessageGroupStats::TRANSLATED] === 0 ) {
				continue;
			}

			foreach ( $this->getTranslations( $collection, $targetLanguage ) as $batch ) {
				$translationCount += count( $batch );
				foreach ( $servers as $server ) {
					$transWrites -= microtime( true );
					$server->batchInsertTranslations( $batch );
					$transWrites += microtime( true );
				}
			}
		}

		$times[ 'trans' ] += ( microtime( true ) - $transWrites );
		$times[ 'writes' ] += $transWrites;

		foreach ( $servers as $server ) {
			$server->endBatch();
		}

		$times[ 'total' ] += microtime( true );
		$countItems = $translationCount + $definitionCount;

		if ( $countItems !== 0 ) {
			$debug = sprintf(
				"Total %.1f s for %d items on %d server(s) >> stats/init/trans/writes %%: %d/%d/%d/%d >> %.1f ms/item",
				$times['total'],
				$countItems,
				count( $servers ),
				$times['stats'] / $times['total'] * 100,
				$times['init'] / $times['total'] * 100,
				$times['trans'] / $times['total'] * 100,
				$times['writes'] / $times['total'] * 100,
				$times['total'] / $countItems * 1000
			);
			$this->logInfo( "Finished exporting {$group->getId()}. $debug\n" );
		}
	}

	private function getDefinitions( MessageCollection $collection, string $sourceLanguage ): Generator {
		$definitions = [];
		foreach ( $collection->keys() as $mKey => $titleValue ) {
			$title = Title::newFromLinkTarget( $titleValue );
			$handle = new MessageHandle( $title );
			$definition = [ $handle, $sourceLanguage, $collection[$mKey]->definition() ];
			$definitions[] = $definition;
			if ( $this->mBatchSize && count( $definitions ) === $this->mBatchSize ) {
					yield $definitions;
					$definitions = [];
			}
		}

		if ( $definitions ) {
			yield $definitions;
		}
	}

	private function getTranslations( MessageCollection $collection, string $targetLanguage ): Generator {
		$collection->resetForNewLanguage( $targetLanguage );
		$collection->filter( MessageCollection::FILTER_IGNORED, MessageCollection::EXCLUDE_MATCHING );
		$collection->filter( MessageCollection::FILTER_TRANSLATED, MessageCollection::INCLUDE_MATCHING );
		$collection->loadTranslations();
		$translations = [];

		foreach ( $collection->keys() as $mkey => $titleValue ) {
			$title = Title::newFromLinkTarget( $titleValue );
			$handle = new MessageHandle( $title );
			$translations[] = [ $handle, $targetLanguage, $collection[$mkey]->translation() ];
			if ( $this->mBatchSize && count( $translations ) === $this->mBatchSize ) {
				yield $translations;
				$translations = [];
			}
		}

		if ( $translations ) {
			yield $translations;
		}
	}

	private function logInfo( string $text ) {
		if ( $this->hasOption( 'verbose' ) ) {
			$this->statusLine( $text );
		}
	}

	protected function resetStateForFork() {
		// Make sure all existing connections are dead,
		// we can't use them in forked children.
		MediaWiki\MediaWikiServices::resetChildProcessServices();
		// Temporary workaround for https://phabricator.wikimedia.org/T258860.
		// This script just moves data around, so skipping the message cache should not
		// cause any major issues. Things like message documentation language name and
		// main page name were being checked from the message cache and sometimes failing.
		MediaWiki\MediaWikiServices::getInstance()->getMessageCache()->disable();
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

	private function getWritableServer( string $serverId ): WritableTtmServer {
		if ( $serverId === self::FAKE_TTM ) {
			return new FakeTtmServer();
		}

		$server = Services::getInstance()->getTtmServerFactory()->create( $serverId );
		if ( !$server instanceof WritableTtmServer ) {
			throw new InvalidArgumentException(
				"$serverId TTM server does not implement WritableTtmServer interface "
			);
		}

		return $server;
	}

	private function getCollection( MessageGroup $group, string $sourceLanguage ): MessageCollection {
		$collection = $group->initCollection( $sourceLanguage );
		$collection->filter( MessageCollection::FILTER_IGNORED, MessageCollection::EXCLUDE_MATCHING );
		$collection->initMessages();
		return $collection;
	}
}

$maintClass = TTMServerBootstrap::class;
require_once RUN_MAINTENANCE_IF_MAIN;
