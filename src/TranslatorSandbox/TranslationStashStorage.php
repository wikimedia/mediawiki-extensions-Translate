<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TranslatorSandbox;

use Title;
use User;
use Wikimedia\Rdbms\IDatabase;

/**
 * Storage class for stashed translations. This one uses sql tables as storage.
 *
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @since 2013.06 (namespaced in 2020.11)
 */
class TranslationStashStorage implements TranslationStashReader, TranslationStashWriter {
	/** @var IDatabase */
	protected $db;
	/** @var string */
	protected $dbTable;

	public function __construct( IDatabase $db, string $table = 'translate_stash' ) {
		$this->db = $db;
		$this->dbTable = $table;
	}

	public function getTranslations( User $user ): array {
		$conds = [ 'ts_user' => $user->getId() ];
		$fields = [ 'ts_namespace', 'ts_title', 'ts_value', 'ts_metadata' ];

		$res = $this->db->select( $this->dbTable, $fields, $conds, __METHOD__ );

		$objects = [];
		foreach ( $res as $row ) {
			$objects[] = new StashedTranslation(
				$user,
				Title::makeTitle( $row->ts_namespace, $row->ts_title ),
				$row->ts_value,
				unserialize( $row->ts_metadata )
			);
		}

		return $objects;
	}

	public function addTranslation( StashedTranslation $item ): void {
		$row = [
			'ts_user' => $item->getUser()->getId(),
			'ts_title' => $item->getTitle()->getDBkey(),
			'ts_namespace' => $item->getTitle()->getNamespace(),
			'ts_value' => $item->getValue(),
			'ts_metadata' => serialize( $item->getMetadata() ),
		];

		$indexes = [
			[ 'ts_user', 'ts_namespace', 'ts_title' ],
		];

		$this->db->replace( $this->dbTable, $indexes, $row, __METHOD__ );
	}

	public function deleteTranslations( User $user ): void {
		$conds = [ 'ts_user' => $user->getId() ];
		$this->db->delete( $this->dbTable, $conds, __METHOD__ );
	}
}
