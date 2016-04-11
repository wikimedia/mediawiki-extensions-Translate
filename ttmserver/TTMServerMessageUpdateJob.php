<?php
/**
 * Contains class with job for updating translation memory.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0+
 */

/**
 * Job for updating translation memory.
 *
 * @ingroup JobQueue
 */
class TTMServerMessageUpdateJob extends Job {
	/**
	 * @param MessageHandle $handle
	 * @return TTMServerMessageUpdateJob
	 */
	public static function newJob( MessageHandle $handle, $command ) {
		$job = new self( $handle->getTitle(), array( 'command' => $command ) );

		return $job;
	}

	/**
	 * @param Title $title
	 * @param array $params
	 * @param int $id
	 */
	public function __construct( $title, $params = array(), $id = 0 ) {
		parent::__construct( __CLASS__, $title, $params, $id );
	}

	/**
	 * Fetch all the translations and update them.
	 */
	public function run() {
		$handle = new MessageHandle( $this->title );

		// For old jobs before this param was added
		$command = isset( $this->params['command'] ) ? $this->params['command'] : 'rebuild';

		// JobQueue will catch exceptions and retry the job few times,
		// after which it will be abandoned.
		if ( $command === 'delete' ) {
			$this->updateItem( $handle, null, false );
		} elseif ( $command === 'rebuild' ) {
			$this->updateMessage( $handle );
		} elseif ( $command === 'refresh' ) {
			$this->updateTranslation( $handle );
		}

		return true;
	}

	private function updateMessage( MessageHandle $handle ) {
		// Base page update, e.g. group change. Update everything.
		$translations = ApiQueryMessageTranslations::getTranslations( $handle );
		foreach ( $translations as $page => $data ) {
			$tTitle = Title::makeTitle( $this->title->getNamespace(), $page );
			$tHandle = new MessageHandle( $tTitle );
			$this->updateItem( $tHandle, $data[0], $tHandle->isFuzzy() );
		}
	}

	private function updateTranslation( MessageHandle $handle ) {
		// Update only this translation
		$translation = TranslateUtils::getMessageContent(
			$handle->getKey(),
			$handle->getCode(),
			$handle->getTitle()->getNamespace()
		);
		$this->updateItem( $handle, $translation, $handle->isFuzzy() );
	}

	private function updateItem( MessageHandle $handle, $text, $fuzzy ) {
		if ( $fuzzy ) {
			$text = null;
		}
		TTMServer::primary()->update( $handle, $text );
	}
}
