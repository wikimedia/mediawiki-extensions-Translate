<?php
/**
 * Contains class with job for updating translation memory.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2012-2013, Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
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
	public static function newJob( MessageHandle $handle ) {
		$job = new self( $handle->getTitle() );

		return $job;
	}

	function __construct( $title, $params = array(), $id = 0 ) {
		parent::__construct( __CLASS__, $title, $params, $id );
	}

	/**
	 * Fetch all the translations and update them.
	 */
	function run() {
		$handle = new MessageHandle( $this->title );
		$translations = ApiQueryMessageTranslations::getTranslations( $handle );
		foreach ( $translations as $page => $data ) {
			$tTitle = Title::makeTitle( $this->title->getNamespace(), $page );
			$tHandle = new MessageHandle( $tTitle );
			TTMServer::onChange( $tHandle, $data[0], $tHandle->isFuzzy() );
		}

		return true;
	}
}
