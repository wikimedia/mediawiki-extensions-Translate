<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

use MediaWiki\Page\PageReference;
use Wikimedia\Rdbms\IReadableDatabase;
use Wikimedia\Rdbms\SelectQueryBuilder;

/**
 * Provides read access to the translate_sections table for a single page.
 *
 * Reads use the connection passed to the constructor. For write access use
 * {@see WritableTranslationUnitStore}.
 *
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @since 2021.05
 */
class TranslationUnitStore {
	protected const TABLE = 'translate_sections';

	/**
	 * @param IReadableDatabase $db
	 * @param int|PageReference $page Page ID, or a title to resolve on the supplied connection.
	 */
	public function __construct(
		protected readonly IReadableDatabase $db,
		private readonly int|PageReference $page,
	) {
	}

	/** @return array<string,TranslationUnit> Units keyed by ID, in stored order. */
	public function getUnits(): array {
		$res = $this->newSelectQueryBuilder()
			->select( [ 'trs_key', 'trs_text' ] )
			->orderBy( 'trs_order' )
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
		return $this->newSelectQueryBuilder()
			->select( 'trs_key' )
			->caller( __METHOD__ )
			->fetchFieldValues();
	}

	private function newSelectQueryBuilder(): SelectQueryBuilder {
		$builder = $this->db->newSelectQueryBuilder();
		if ( is_int( $this->page ) ) {
			return $builder->from( self::TABLE )
				->where( [ 'trs_page' => $this->page ] );
		}

		return $builder->from( 'page' )
			->join( self::TABLE, null, 'page_id = trs_page' )
			->where( [
				'page_namespace' => $this->page->getNamespace(),
				'page_title' => $this->page->getDBkey(),
			] );
	}
}
