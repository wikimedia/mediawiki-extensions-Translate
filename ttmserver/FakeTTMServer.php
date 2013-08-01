<?php
/**
 * TTMServer - The Translate extension translation memory interface
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2012-2013, Niklas Laxström
 * @license GPL-2.0+
 * @ingroup TTMServer
 */

/**
 * NO-OP version of TTMServer when it is disabled.
 * Keeps other code simpler when they can just do
 * TTMServer::primary()->update( ... );
 * @since 2012-01-28
 * @ingroup TTMServer
 */
class FakeTTMServer implements ReadableTTMServer, WritableTTMServer {
	public function query( $sourceLanguage, $targetLanguage, $text ) {
		return array();
	}

	public function isLocalSuggestion( array $suggestion ) {
		false;
	}

	public function expandLocation( array $suggestion ) {
		return '';
	}

	public function update( MessageHandle $handle, $targetText ) {
	}

	public function beginBootstrap() {
	}

	public function beginBatch() {
	}

	public function batchInsertDefinitions( array $batch ) {
	}

	public function batchInsertTranslations( array $batch ) {
	}

	public function endBatch() {
	}

	public function endBootstrap() {
	}
}
