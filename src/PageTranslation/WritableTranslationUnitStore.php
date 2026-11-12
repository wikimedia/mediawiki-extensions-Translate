<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

use Wikimedia\Rdbms\IDatabase;

/**
 * Provides read and write access to the translate_sections table for a single page.
 *
 * Reads use the primary connection passed to the constructor. For read-only access
 * from a replica use {@see TranslationUnitStore}.
 *
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @since 2026.09
 */
class WritableTranslationUnitStore extends TranslationUnitStore {
	public function __construct(
		private readonly IDatabase $dbw,
		int $pageId
	) {
		parent::__construct( $dbw, $pageId );
	}

	/**
	 * Replace all stored translation units for the page with the given ones.
	 * The order of the units in the array determines their stored trs_order.
	 *
	 * @param TranslationUnit[] $units
	 */
	public function store( array $units ): void {
		$inserts = [];
		foreach ( array_values( $units ) as $index => $unit ) {
			$inserts[] = [
				'trs_page' => $this->pageId,
				'trs_key' => $unit->id,
				'trs_text' => $unit->getText(),
				'trs_order' => $index,
			];
		}

		$this->delete();

		if ( $inserts ) {
			$this->dbw->newInsertQueryBuilder()
				->insertInto( self::TABLE )
				->rows( $inserts )
				->caller( __METHOD__ )
				->execute();
		}
	}

	public function delete(): void {
		$this->dbw->newDeleteQueryBuilder()
			->deleteFrom( self::TABLE )
			->where( [ 'trs_page' => $this->pageId ] )
			->caller( __METHOD__ )
			->execute();
	}
}
