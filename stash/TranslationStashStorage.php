<?php
/**
 * Storage class for stashed translations.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0+
 */

/**
 * Storage class for stashed translations. This one uses sql tables as storage.
 * @since 2013.06
 */
class TranslationStashStorage {
	protected $db;
	protected $dbTable;

	public function __construct( DatabaseBase $db, $table = 'translate_stash' ) {
		$this->db = $db;
		$this->dbTable = $table;
	}

	/**
	 * Adds a new translation to the stash. If the same key already exists, the
	 * previous translation and metadata will be replaced with the new one.
	 *
	 * @param StashedTranslation $item
	 */
	public function addTranslation( StashedTranslation $item ) {
		$row = array(
			'ts_user' => $item->getUser()->getId(),
			'ts_title' => $item->getTitle()->getDBkey(),
			'ts_namespace' => $item->getTitle()->getNamespace(),
			'ts_value' => $item->getValue(),
			'ts_metadata' => serialize( $item->getMetadata() ),
		);

		$indexes = array(
			array( 'ts_user', 'ts_namespace', 'ts_title' )
		);

		$this->db->replace( $this->dbTable, $indexes, $row, __METHOD__ );
	}

	/**
	 * Gets all stashed translations for the given user.
	 * @param User $user
	 * @return StashedTranslation[]
	 */
	public function getTranslations( User $user ) {
		$conds = array( 'ts_user' => $user->getId() );
		$fields = array( 'ts_namespace', 'ts_title', 'ts_value', 'ts_metadata' );

		$res = $this->db->select( $this->dbTable, $fields, $conds, __METHOD__ );

		$objects = array();
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

	/**
	 * Delete all stashed translations for the given user.
	 * @param User $user
	 * @since 2013.10
	 */
	public function deleteTranslations( User $user ) {
		$conds = array( 'ts_user' => $user->getId() );
		$this->db->delete( $this->dbTable, $conds, __METHOD__ );
	}
}
