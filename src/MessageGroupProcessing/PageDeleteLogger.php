<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use ManualLogEntry;
use MediaWiki\Status\Status;
use MediaWiki\Title\Title;
use MediaWiki\User\User;

/**
 * Helper class for logging translatable bundle and translation page deletions
 * @author Abijeet Patro
 * @author Niklas Laxström
 * @since 2022.05
 * @license GPL-2.0-or-later
 */
class PageDeleteLogger {
	private string $logType;
	private Title $baseSourceTitle;

	public function __construct( Title $baseSourceTitle, string $logType ) {
		$this->baseSourceTitle = $baseSourceTitle;
		$this->logType = $logType;
	}

	public function logBundleSuccess( User $performer, string $reason ): void {
		$entry = $this->getManualLogEntry( $this->logType, 'deletefok', $performer, $reason );
		$logid = $entry->insert();
		$entry->publish( $logid );
	}

	public function logPageSuccess( User $performer, string $reason ): void {
		$entry = $this->getManualLogEntry( $this->logType, 'deletelok', $performer, $reason );
		$logid = $entry->insert();
		$entry->publish( $logid );
	}

	public function logBundleError( User $performer, string $reason, Status $error ): void {
		$entry = $this->getManualLogEntry( $this->logType, 'deletefnok', $performer, $reason );
		$this->publishError( $entry, $error );
	}

	public function logPageError( User $performer, string $reason, Status $error ): void {
		$entry = $this->getManualLogEntry( $this->logType, 'deletelnok', $performer, $reason );
		$this->publishError( $entry, $error );
	}

	private function publishError( ManualLogEntry $entry, Status $error ): void {
		$entry->setParameters( [
			'target' => $this->baseSourceTitle->getPrefixedText(),
			'error' => $error->getErrorsArray(),
		] );
		$logid = $entry->insert();
		$entry->publish( $logid );
	}

	private function getManualLogEntry(
		string $logType,
		string $logKey,
		User $performer,
		string $reason
	): ManualLogEntry {
		$entry = new ManualLogEntry( $logType, $logKey );
		$entry->setPerformer( $performer );
		$entry->setTarget( $this->baseSourceTitle );
		$entry->setParameters( [
			'target' => $this->baseSourceTitle->getPrefixedText(),
		] );
		$entry->setComment( $reason );

		return $entry;
	}
}
