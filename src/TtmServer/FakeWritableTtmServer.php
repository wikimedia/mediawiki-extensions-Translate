<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TtmServer;

use MediaWiki\Extension\Translate\MessageLoading\MessageHandle;

class FakeWritableTtmServer extends TtmServer implements WritableTtmServer {
	public function __construct() {
		parent::__construct( [] );
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

	public function isFrozen(): bool {
		return false;
	}

	public function setDoReIndex(): void {
	}
}
