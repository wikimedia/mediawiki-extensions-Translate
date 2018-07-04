<?php
/**
 * Simple helper to log things to the console.
 *
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @file
 */

use Psr\Log\AbstractLogger;

class TranslateCliLogger extends AbstractLogger {
	public function __construct( callable $logger ) {
		$this->logger = $logger;
	}

	public function log( $level, $msg, array $context = [] ) {
		( $this->logger )( "LOG $level: $msg" );
	}
}
