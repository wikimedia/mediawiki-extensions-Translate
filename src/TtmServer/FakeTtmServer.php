<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TtmServer;

use MediaWiki\Extension\Translate\MessageLoading\MessageHandle;

/**
 * NO-OP version of TtmServer when it is disabled.
 * Keeps other code simpler when they can just do
 * TTMServer::primary()->update( ... );
 *
 * @author Niklas Laxström
 * @copyright Copyright © 2012-2013, Niklas Laxström
 * @license GPL-2.0-or-later
 * @ingroup TTMServer
 */
class FakeTtmServer extends TtmServer implements ReadableTtmServer, WritableTtmServer {
	public function __construct() {
		parent::__construct( [] );
	}

	public function query( string $sourceLanguage, string $targetLanguage, string $text ): array {
		return [];
	}

	public function isLocalSuggestion( array $suggestion ): bool {
		return false;
	}

	public function expandLocation( array $suggestion ): string {
		return '';
	}

	public function update( MessageHandle $handle, ?string $targetText ): bool {
		return true;
	}

	public function beginBootstrap(): void {
	}

	public function beginBatch(): void {
	}

	public function batchInsertDefinitions( array $batch ): void {
	}

	public function batchInsertTranslations( array $batch ): void {
	}

	public function endBatch(): void {
	}

	public function endBootstrap(): void {
	}

	public function setDoReIndex(): void {
	}
}
