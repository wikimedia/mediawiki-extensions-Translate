<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TtmServer;

use MessageHandle;

/**
 * Interface for TTMServer that can be updated.
 * @ingroup TTMServer
 */
interface WritableTtmServer {
	/**
	 * Shovels the new translation into translation memory.
	 * Use this for single updates (=after message edit).
	 * If no text is provided, entry will be removed from the translation
	 * memory.
	 *
	 * @param MessageHandle $handle
	 * @param string|null $targetText Use null to only delete.
	 */
	public function update( MessageHandle $handle, ?string $targetText ): bool;

	/**
	 * Called when starting to fill the translation memory.
	 * Set up necessary variables and remove old content
	 * from the server.
	 */
	public function beginBootstrap(): void;

	/** Called before every batch (MessageGroup). */
	public function beginBatch(): void;

	/** Called multiple times per batch if necessary. */
	public function batchInsertDefinitions( array $batch ): void;

	/** Called multiple times per batch if necessary. */
	public function batchInsertTranslations( array $batch ): void;

	/** Called after every batch (MessageGroup). */
	public function endBatch(): void;

	/** Do any cleanup, optimizing etc. */
	public function endBootstrap(): void;

	/**
	 * Get the list of services to duplicate writes to make them "mirrors"
	 * of this service.
	 * @return string[]
	 */
	public function getMirrors(): array;

	/** Instruct the service to fully wipe the index and start from scratch. */
	public function setDoReIndex(): void;
}
