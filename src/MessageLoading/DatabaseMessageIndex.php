<?php
declare ( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageLoading;

use MediaWiki\MediaWikiServices;
use Wikimedia\Rdbms\IConnectionProvider;

/**
 * Storage on the database itself.
 *
 * This is likely to be the slowest backend. However, it scales okay
 * and provides random access. It also doesn't need any special setup,
 * the database table is added with update.php together with other tables,
 * which is the reason this is the default backend. It also works well
 * on multi-server setup without needing for shared file storage.
 */
class DatabaseMessageIndex extends MessageIndexStore {
	private ?array $index = null;
	private IConnectionProvider $dbProvider;

	public function __construct() {
		$this->dbProvider = MediaWikiServices::getInstance()->getConnectionProvider();
	}

	public function retrieve( bool $readLatest = false ): array {
		if ( $this->index !== null && !$readLatest ) {
			return $this->index;
		}

		$dbr = $readLatest ? $this->dbProvider->getPrimaryDatabase() :
			$this->dbProvider->getReplicaDatabase();
		$res = $dbr->newSelectQueryBuilder()
			->select( '*' )
			->from( 'translate_messageindex' )
			->caller( __METHOD__ )
			->fetchResultSet();
		$this->index = [];
		foreach ( $res as $row ) {
			$this->index[$row->tmi_key] = $this->unserialize( $row->tmi_value );
		}

		return $this->index;
	}

	/** @inheritDoc */
	public function get( string $key ) {
		$dbr = $this->dbProvider->getReplicaDatabase();
		$value = $dbr->newSelectQueryBuilder()
			->select( 'tmi_value' )
			->from( 'translate_messageindex' )
			->where( [ 'tmi_key' => $key ] )
			->caller( __METHOD__ )
			->fetchField();

		return is_string( $value ) ? $this->unserialize( $value ) : null;
	}

	public function store( array $array, array $diff ): void {
		$updates = [];

		foreach ( [ $diff['add'], $diff['mod'] ] as $changes ) {
			foreach ( $changes as $key => $data ) {
				[ , $new ] = $data;
				$updates[] = [
					'tmi_key' => $key,
					'tmi_value' => $this->serialize( $new ),
				];
			}
		}

		$deletions = array_keys( $diff['del'] );

		$dbw = $this->dbProvider->getPrimaryDatabase();
		$dbw->startAtomic( __METHOD__ );

		if ( $updates !== [] ) {
			$dbw->newReplaceQueryBuilder()
				->replaceInto( 'translate_messageindex' )
				->uniqueIndexFields( [ 'tmi_key' ] )
				->rows( $updates )
				->caller( __METHOD__ )
				->execute();
		}

		if ( $deletions !== [] ) {
			$dbw->newDeleteQueryBuilder()
				->deleteFrom( 'translate_messageindex' )
				->where( [ 'tmi_key' => $deletions ] )
				->caller( __METHOD__ )
				->execute();
		}

		$dbw->endAtomic( __METHOD__ );

		$this->index = $array;
	}
}
