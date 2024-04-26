<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageLoading;

/** Storage on hash. For testing. */
class HashMessageIndex extends MessageIndexStore {
	private array $index = [];

	public function retrieve( bool $readLatest = false ): array {
		return $this->index;
	}

	/** @inheritDoc */
	public function get( string $key ) {
		return $this->index[$key] ?? null;
	}

	/** @inheritDoc */
	public function store( array $array, array $diff ): void {
		$this->index = $array;
	}
}
