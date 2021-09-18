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
	/** @var IDatabase */
	private $db;
	/** @var int */
	private $pageId;

	public function __construct( IDatabase $db, int $pageId ) {
		$this->db = $db;
		$this->pageId = $pageId;
	}

	public function getUnits(): array {
		$res = $this->db->select(
			self::TABLE,
			[ 'trs_key', 'trs_text' ],
			[ 'trs_page' => $this->pageId ],
			__METHOD__
		);

		$units = [];
		foreach ( $res as $row ) {
			$units[$row->trs_key] = new TranslationUnit( $row->trs_text, $row->trs_key );
		}

		return $units;
	}

	/** @return string[] */
	public function getNames(): array {
		return $this->db->selectFieldValues(
			self::TABLE,
			'trs_key',
			[ 'trs_page' => $this->pageId ],
			__METHOD__
		);
	}

	public function delete(): void {
		$this->db->delete(
			self::TABLE,
			[ 'trs_page' => $this->pageId ],
			__METHOD__
		);
	}
}
