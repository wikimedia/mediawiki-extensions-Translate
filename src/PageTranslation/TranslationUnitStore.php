<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

use Wikimedia\Rdbms\IReadableDatabase;

/**
 * Provides read access to the translate_sections table for a single page.
 *
 * Reads use a replica connection. For write access use {@see WritableTranslationUnitStore}.
 *
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @since 2021.05
 */
class TranslationUnitStore {
	protected const TABLE = 'translate_sections';

	public function __construct(
		protected readonly IReadableDatabase $db,
		protected readonly int $pageId,
	) {
	}

	/** @return array<string,TranslationUnit> */
	public function getUnits(): array {
		$res = $this->db->newSelectQueryBuilder()
			->select( [ 'trs_key', 'trs_text' ] )
			->from( self::TABLE )
			->where( [ 'trs_page' => $this->pageId ] )
			->caller( __METHOD__ )
			->fetchResultSet();

		$units = [];
		foreach ( $res as $row ) {
			$units[$row->trs_key] = new TranslationUnit( $row->trs_text, $row->trs_key );
		}

		return $units;
	}

	/** @return string[] */
	public function getNames(): array {
		return $this->db->newSelectQueryBuilder()
			->select( 'trs_key' )
			->from( self::TABLE )
			->where( [ 'trs_page' => $this->pageId ] )
			->caller( __METHOD__ )
			->fetchFieldValues();
	}
}
