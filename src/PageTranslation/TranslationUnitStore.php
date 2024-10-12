<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

use Wikimedia\Rdbms\IDatabase;

/**
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @since 2021.05
 */
class TranslationUnitStore implements TranslationUnitReader {
	private const TABLE = 'translate_sections';
	private IDatabase $db;
	private int $pageId;

	public function __construct( IDatabase $db, int $pageId ) {
		$this->db = $db;
		$this->pageId = $pageId;
	}

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

	public function delete(): void {
		$this->db->newDeleteQueryBuilder()
			->deleteFrom( self::TABLE )
			->where( [ 'trs_page' => $this->pageId ] )
			->caller( __METHOD__ )
			->execute();
	}
}
