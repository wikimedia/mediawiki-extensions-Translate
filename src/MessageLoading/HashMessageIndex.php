<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageLoading;

use MessageIndex;

/** Storage on hash. For testing. */
class HashMessageIndex extends MessageIndex {
	private array $index = [];

	public function retrieve( bool $readLatest = false ): array {
		return $this->index;
	}

	/** @inheritDoc */
	protected function get( $key ) {
		return $this->index[$key] ?? null;
	}

	/** @inheritDoc */
	protected function store( array $array, array $diff ): void {
		$this->index = $array;
	}
}
