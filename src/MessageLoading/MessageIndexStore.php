<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageLoading;

/**
 * @since 2024.05
 * @license GPL-2.0-or-later
 * @author Niklas Laxström
 */
abstract class MessageIndexStore {
	abstract public function retrieve( bool $readLatest = false ): array;

	/** @return mixed|null */
	public function get( string $key ) {
		// Default implementation
		$mi = $this->retrieve();
		return $mi[$key] ?? null;
	}

	abstract public function store( array $array, array $diff ): void;

	/** @return string[] */
	public function getKeys(): array {
		return array_keys( $this->retrieve() );
	}

	/**
	 * Hook called once the rebuild lock has been acquired, before the index is
	 * read and rebuilt. Backends that read from the primary database override
	 * this to flush any open transaction, so the rebuild does not operate on a
	 * stale pre-lock snapshot. The default is a no-op.
	 */
	public function prepareForRebuild(): void {
	}

	/** Release the rebuild lock once the stored index is visible to other processes. */
	public function releaseRebuildLock( callable $unlock ): void {
		$unlock();
	}

	/**
	 * These are probably slower than serialize and unserialize,
	 * but they are more space efficient because we only need
	 * strings and arrays.
	 * @param mixed $data
	 * @return mixed
	 */
	protected function serialize( $data ) {
		return is_array( $data ) ? implode( '|', $data ) : $data;
	}

	/**
	 * @param string $data
	 * @return string|string[]
	 */
	protected function unserialize( $data ) {
		$array = explode( '|', $data );
		return count( $array ) > 1 ? $array : $data;
	}
}
