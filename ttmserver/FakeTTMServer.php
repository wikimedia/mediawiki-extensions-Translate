<?php
/**
 * TTMServer - The Translate extension translation memory interface
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2012-2013, Niklas Laxström
 * @license GPL-2.0-or-later
 * @ingroup TTMServer
 */

use MediaWiki\Extension\Translate\TtmServer\ReadableTtmServer;
use MediaWiki\Extension\Translate\TtmServer\WritableTtmServer;

/**
 * NO-OP version of TTMServer when it is disabled.
 * Keeps other code simpler when they can just do
 * TTMServer::primary()->update( ... );
 * @since 2012-01-28
 * @ingroup TTMServer
 */
class FakeTTMServer extends TTMServer implements ReadableTtmServer, WritableTtmServer {
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

	public function getMirrors(): array {
		return [];
	}

	public function isFrozen(): bool {
		return false;
	}

	public function setDoReIndex(): void {
	}
}
