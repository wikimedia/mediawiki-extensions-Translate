<?php
/**
 * Contains class with job for updating translation memory.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */

use MediaWiki\Extension\Translate\MessageLoading\MessageHandle;
use MediaWiki\Extension\Translate\Services;
use MediaWiki\Extension\Translate\TtmServer\WritableTtmServer;
use MediaWiki\Extension\Translate\Utilities\Utilities;
use MediaWiki\Logger\LoggerFactory;
use MediaWiki\MediaWikiServices;
use MediaWiki\Title\Title;
use Psr\Log\LoggerInterface;

/**
 * Job for updating translation memory.
 *
 * job params:
 * - command: the command to run, defaults to 'rebuild'
 * - service: the service to write to, if set to null the job will write
 *   to the default (primary) service and its replicas.
 * - errorCount: number of errors encountered while trying to perform the write
 *   on this service
 *
 * This job handles retries itself and return false in allowRetries to disable
 * JobQueue's internal retry service.
 *
 * @ingroup JobQueue
 */
class TTMServerMessageUpdateJob extends Job {
	/**
	 * Number of *retries* allowed, 4 means we attempt
	 * to run the job 5 times (1 initial attempt + 4 retries).
	 */
	protected const MAX_ERROR_RETRY = 4;

	/**
	 * Constant used by backoffDelay().
	 * With 7 the cumulative delay between the first and last attempt is
	 * between 8 and 33 minutes.
	 */
	protected const WRITE_BACKOFF_EXPONENT = 7;
	/** @var JobQueueGroup */
	private $jobQueueGroup;
	private const CHANNEL_NAME = 'Translate.TtmServerUpdates';
	private LoggerInterface $logger;

	/**
	 * @param MessageHandle $handle
	 * @param string $command
	 * @return self
	 */
	public static function newJob( MessageHandle $handle, $command ) {
		$job = new self( $handle->getTitle(), [ 'command' => $command ] );

		return $job;
	}

	/**
	 * @param Title $title
	 * @param array $params
	 */
	public function __construct( $title, $params = [] ) {
		parent::__construct(
			'TTMServerMessageUpdateJob',
			$title,
			$params + [
				'command' => 'rebuild',
				'service' => null,
				'errorCount' => 0,
			]
		);

		$this->jobQueueGroup = MediaWikiServices::getInstance()->getJobQueueGroup();
		$this->logger = LoggerFactory::getInstance( self::CHANNEL_NAME );
	}

	/**
	 * Fetch all the translations and update them.
	 * @return bool
	 */
	public function run() {
		$services = $this->getServersToUpdate( $this->params['service'] );
		foreach ( $services as $serviceId => $service ) {
			$this->runCommandWithRetry( $service, $serviceId );
		}
		return true;
	}

	/** @inheritDoc */
	public function allowRetries() {
		return false;
	}

	/**
	 * Run the update on the specified service name.
	 * @param WritableTtmServer $ttmServer
	 * @param string $serviceName
	 */
	private function runCommandWithRetry( WritableTtmServer $ttmServer, string $serviceName ): void {
		try {
			$this->runCommand( $ttmServer, $serviceName );
		} catch ( Exception $e ) {
			$this->requeueError( $serviceName, $e );
		}
	}

	/**
	 * @param string $serviceName the service in error
	 * @param Exception $e the error
	 */
	private function requeueError( $serviceName, $e ) {
		$this->logger->warning(
			'Exception thrown while running {command} on ' .
			'service {service}: {errorMessage}',
			[
				'command' => $this->params['command'],
				'service' => $serviceName,
				'errorMessage' => $e->getMessage(),
				'exception' => $e,
			]
		);
		if ( $this->params['errorCount'] >= self::MAX_ERROR_RETRY ) {
			$this->logger->warning(
				'Dropping failing job {command} for service {service} ' .
				'after repeated failure',
				[
					'command' => $this->params['command'],
					'service' => $serviceName,
				]
			);
			return;
		}

		$delay = self::backoffDelay( $this->params['errorCount'] );
		$job = clone $this;
		$job->params['errorCount']++;
		$job->params['service'] = $serviceName;
		$job->setDelay( $delay );
		$this->logger->info(
			'Update job reported failure on service {service}. ' .
			'Requeueing job with delay of {delay}.',
			[
				'service' => $serviceName,
				'delay' => $delay
			]
		);
		$this->resend( $job );
	}

	/**
	 * Extracted for testing purpose
	 * @param self $job
	 */
	protected function resend( self $job ) {
		$this->jobQueueGroup->push( $job );
	}

	private function runCommand( WritableTtmServer $ttmServer, string $serverName ) {
		$handle = $this->getHandle();
		$command = $this->params['command'];

		if ( $command === 'delete' ) {
			$this->updateItem( $ttmServer, $handle, null, false );
		} elseif ( $command === 'rebuild' ) {
			$this->updateMessage( $ttmServer, $handle );
		} elseif ( $command === 'refresh' ) {
			$this->updateTranslation( $ttmServer, $handle );
		}

		$this->logger->info(
			"{command} command completed on {server} for {handle}",
			[
				'command' => $command,
				'server' => $serverName,
				'handle' => $handle->getTitle()->getPrefixedText()
			]
		);
	}

	/**
	 * Extracted for testing purpose
	 *
	 * @return MessageHandle
	 */
	protected function getHandle() {
		return new MessageHandle( $this->title );
	}

	/**
	 * Extracted for testing purpose
	 *
	 * @param MessageHandle $handle
	 * @return string
	 */
	protected function getTranslation( MessageHandle $handle ) {
		return Utilities::getMessageContent(
			$handle->getKey(),
			$handle->getCode(),
			$handle->getTitle()->getNamespace()
		);
	}

	private function updateMessage( WritableTtmServer $ttmserver, MessageHandle $handle ) {
		// Base page update, e.g. group change. Update everything.
		$translations = Utilities::getTranslations( $handle );
		foreach ( $translations as $page => $data ) {
			$tTitle = Title::makeTitle( $this->title->getNamespace(), $page );
			$tHandle = new MessageHandle( $tTitle );
			$this->updateItem( $ttmserver, $tHandle, $data[0], $tHandle->isFuzzy() );
		}
	}

	private function updateTranslation( WritableTtmServer $ttmserver, MessageHandle $handle ) {
		// Update only this translation
		$translation = $this->getTranslation( $handle );
		$this->updateItem( $ttmserver, $handle, $translation, $handle->isFuzzy() );
	}

	private function updateItem( WritableTtmServer $ttmserver, MessageHandle $handle, $text, $fuzzy ) {
		if ( $fuzzy ) {
			$text = null;
		}
		$ttmserver->update( $handle, $text );
	}

	private function getServersToUpdate( ?string $requestedServiceId ): array {
		$ttmServerFactory = Services::getInstance()->getTtmServerFactory();
		if ( $requestedServiceId ) {
			if ( !$ttmServerFactory->has( $requestedServiceId ) ) {
				$this->logger->warning(
					'Received update job for a an unknown service {service}.',
					[ 'service' => $requestedServiceId ]
				);
				return [];
			}

			return [ $requestedServiceId => $ttmServerFactory->create( $requestedServiceId ) ];
		}

		try {
			return $ttmServerFactory->getWritable();
		} catch ( Exception $e ) {
			$this->logger->error(
				'There was an error while fetching writable TTM services. Error: {error}',
				[ 'error' => $e->getMessage() ]
			);
		}

		return [];
	}

	/**
	 * Set a delay for this job. Note that this might not be possible, the JobQueue
	 * implementation handling this job doesn't support it (JobQueueDB) but is possible
	 * for the high performance JobQueueRedis. Note also that delays are minimums -
	 * at least JobQueueRedis makes no effort to remove the delay as soon as possible
	 * after it has expired. By default it only checks every five minutes or so.
	 * Note yet again that if another delay has been set that is longer then this one
	 * then the _longer_ delay stays.
	 *
	 * @param int $delay seconds to delay this job if possible
	 */
	public function setDelay( $delay ) {
		$jobQueue = $this->jobQueueGroup->get( $this->getType() );
		if ( !$delay || !$jobQueue->delayedJobsEnabled() ) {
			return;
		}
		$oldTime = $this->getReleaseTimestamp();
		$newTime = time() + $delay;
		if ( $oldTime !== null && $oldTime >= $newTime ) {
			return;
		}
		$this->params[ 'jobReleaseTimestamp' ] = $newTime;
	}

	/**
	 * @param int $errorCount The number of times the job has errored out.
	 * @return int Number of seconds to delay. With the default minimum exponent
	 * of 6 the possible return values are 64, 128, 256, 512 and 1024 giving a
	 * maximum delay of 17 minutes.
	 */
	public static function backoffDelay( $errorCount ) {
		return ceil( pow(
			2,
			static::WRITE_BACKOFF_EXPONENT + rand( 0, min( $errorCount, 4 ) )
		) );
	}
}
