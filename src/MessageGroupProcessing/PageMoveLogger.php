<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use ManualLogEntry;
use Status;
use Title;
use User;

/**
 * Helper class for logging translatable bundle moves
 * @author Abijeet Patro
 * @author Niklas Laxström
 * @since 2022.03
 * @license GPL-2.0-or-later
 */
class PageMoveLogger {
	/** @var string */
	private $logType;
	/** @var Title */
	private $baseSourceTitle;

	public function __construct( Title $baseSourceTitle, string $logType ) {
		$this->baseSourceTitle = $baseSourceTitle;
		$this->logType = $logType;
	}

	public function logSuccess( User $performer, Title $target ): void {
		$entry = new ManualLogEntry( $this->logType, 'moveok' );
		$entry->setPerformer( $performer );
		$entry->setTarget( $this->baseSourceTitle );
		$entry->setParameters( [ 'target' => $target->getPrefixedText() ] );
		$logid = $entry->insert();
		$entry->publish( $logid );
	}

	public function logError( User $performer, Title $source, Title $target, Status $error ): void {
		$entry = new ManualLogEntry( $this->logType, 'movenok' );
		$entry->setPerformer( $performer );
		$entry->setTarget( $source );
		$entry->setParameters( [
			'target' => $target->getPrefixedText(),
			'error' => $error->getErrorsArray(),
		] );
		$logid = $entry->insert();
		$entry->publish( $logid );
	}
}
