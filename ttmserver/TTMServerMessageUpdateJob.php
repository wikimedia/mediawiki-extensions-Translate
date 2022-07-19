<?php
/**
 * Contains class with job for updating translation memory.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */

use MediaWiki\Logger\LoggerFactory;

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
 * If mirroring is activated on the primary service then the first job
 * will try to write to all services, it will resend a new job to
 * every single service that failed and will increment errorCount.
 * When too many errors occur on single service the job is dropped.
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
			__CLASS__,
			$title,
			$params + [
				'command' => 'rebuild',
				'service' => null,
				'errorCount' => 0,
			]
		);
	}

	/**
	 * Fetch all the translations and update them.
	 * @return bool
	 */
	public function run() {
		global $wgTranslateTranslationServices,
			$wgTranslateTranslationDefaultService;

		$service = $this->params['service'];
		$writeToMirrors = false;

		if ( $service === null ) {
			$service = $wgTranslateTranslationDefaultService;
			$writeToMirrors = true;
		}

		if ( !isset( $wgTranslateTranslationServices[$service] ) ) {
			LoggerFactory::getInstance( 'TTMServerUpdates' )->warning(
				'Received update job for a an unknown service {service}.',
				[ 'service' => $service ]
			);
			return true;
		}

		$services = [ $service ];
		if ( $writeToMirrors ) {
			$config = $wgTranslateTranslationServices[$service];
			$server = TTMServer::factory( $config );
			$services = array_unique(
				array_merge( $services, $server->getMirrors() )
			);
		}

		foreach ( $services as $service ) {
			$this->runCommandWithRetry( $service );
		}
		return true;
	}

	/** @inheritDoc */
	public function allowRetries() {
		return false;
	}

	/**
	 * Run the update on the specified service name.
	 *
	 * @param string $serviceName the service name
	 */
	private function runCommandWithRetry( $serviceName ) {
		global $wgTranslateTranslationServices;

		if ( !isset( $wgTranslateTranslationServices[$serviceName] ) ) {
			LoggerFactory::getInstance( 'TTMServerUpdates' )->warning(
				'Cannot write to {service}: service is unknown.',
				[ 'service' => $serviceName ]
			);
			return;
		}
		$ttmserver = TTMServer::factory( $wgTranslateTranslationServices[$serviceName] );

		if ( $serviceName === null || !( $ttmserver instanceof WritableTTMServer ) ) {
			LoggerFactory::getInstance( 'TTMServerUpdates' )->warning(
				'Received update job for a service that does not implement ' .
				'WritableTTMServer, please check config for {service}.',
				[ 'service' => $serviceName ]
			);
			return;
		}

		try {
			$this->runCommand( $ttmserver );
		} catch ( Exception $e ) {
			$this->requeueError( $serviceName, $e );
		}
	}

	/**
	 * @param string $serviceName the service in error
	 * @param Exception $e the error
	 */
	private function requeueError( $serviceName, $e ) {
		LoggerFactory::getInstance( 'TTMServerUpdates' )->warning(
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
			LoggerFactory::getInstance( 'TTMServerUpdates' )->warning(
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
		LoggerFactory::getInstance( 'TTMServerUpdates' )->info(
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
		TranslateUtils::getJobQueueGroup()->push( $job );
	}

	private function runCommand( WritableTTMServer $ttmserver ) {
		$handle = $this->getHandle();
		$command = $this->params['command'];

		if ( $command === 'delete' ) {
			$this->updateItem( $ttmserver, $handle, null, false );
		} elseif ( $command === 'rebuild' ) {
			$this->updateMessage( $ttmserver, $handle );
		} elseif ( $command === 'refresh' ) {
			$this->updateTranslation( $ttmserver, $handle );
		}
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
		return TranslateUtils::getMessageContent(
			$handle->getKey(),
			$handle->getCode(),
			$handle->getTitle()->getNamespace()
		);
	}

	private function updateMessage( WritableTTMServer $ttmserver, MessageHandle $handle ) {
		// Base page update, e.g. group change. Update everything.
		$translations = TranslateUtils::getTranslations( $handle );
		foreach ( $translations as $page => $data ) {
			$tTitle = Title::makeTitle( $this->title->getNamespace(), $page );
			$tHandle = new MessageHandle( $tTitle );
			$this->updateItem( $ttmserver, $tHandle, $data[0], $tHandle->isFuzzy() );
		}
	}

	private function updateTranslation( WritableTTMServer $ttmserver, MessageHandle $handle ) {
		// Update only this translation
		$translation = $this->getTranslation( $handle );
		$this->updateItem( $ttmserver, $handle, $translation, $handle->isFuzzy() );
	}

	private function updateItem( WritableTTMServer $ttmserver, MessageHandle $handle, $text, $fuzzy ) {
		if ( $fuzzy ) {
			$text = null;
		}
		$ttmserver->update( $handle, $text );
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
		$jobQueue = TranslateUtils::getJobQueueGroup()->get( $this->getType() );
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
