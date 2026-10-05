<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Cache;

use Iterator;
use MediaWiki\Json\JsonCodec;
use Wikimedia\Rdbms\IConnectionProvider;
use Wikimedia\Rdbms\IDatabase;
use Wikimedia\Rdbms\RawSQLValue;

/**
 * A persistent cache implementation using the database.
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 * @since 2020.12
 */
class PersistentDatabaseCache implements PersistentCache {
	private const VIRTUAL_DOMAIN = 'virtual-translate';
	private const TABLE_NAME = 'translate_cache';
	private const SET_BATCH_SIZE = 100;
	private const SET_BATCH_BYTES = 1_000_000;

	public function __construct(
		private readonly IConnectionProvider $dbProvider,
		private readonly JsonCodec $jsonCodec,
	) {
	}

	/** @return PersistentCacheEntry[] */
	public function get( string ...$keynames ): array {
		$dbr = $this->dbProvider->getReplicaDatabase( self::VIRTUAL_DOMAIN );
		$rows = $dbr->newSelectQueryBuilder()
			->select( [ 'tc_key', 'tc_value', 'tc_exptime', 'tc_tag' ] )
			->from( self::TABLE_NAME )
			->where( [ 'tc_key' => $keynames ] )
			->caller( __METHOD__ )
			->fetchResultSet();

		return $this->buildEntries( $rows );
	}

	/** @return PersistentCacheEntry[] */
	public function getByTag( string $tag ): array {
		$dbr = $this->dbProvider->getReplicaDatabase( self::VIRTUAL_DOMAIN );
		$rows = $dbr->newSelectQueryBuilder()
			->select( [ 'tc_key', 'tc_value', 'tc_exptime', 'tc_tag' ] )
			->from( self::TABLE_NAME )
			->where( [ 'tc_tag' => $tag ] )
			->caller( __METHOD__ )
			->fetchResultSet();

		return $this->buildEntries( $rows );
	}

	public function has( string $keyname ): bool {
		$dbr = $this->dbProvider->getReplicaDatabase( self::VIRTUAL_DOMAIN );
		$hasRow = $dbr->newSelectQueryBuilder()
			->select( 'tc_key' )
			->from( self::TABLE_NAME )
			->where( [ 'tc_key' => $keyname ] )
			->caller( __METHOD__ )
			->fetchRow();

		return (bool)$hasRow;
	}

	public function hasEntryWithTag( string $tag ): bool {
		$dbr = $this->dbProvider->getReplicaDatabase( self::VIRTUAL_DOMAIN );
		$hasRow = $dbr->newSelectQueryBuilder()
			->select( 'tc_key' )
			->from( self::TABLE_NAME )
			->where( [ 'tc_tag' => $tag ] )
			->caller( __METHOD__ )
			->fetchRow();

		return (bool)$hasRow;
	}

	public function set( PersistentCacheEntry ...$entries ): void {
		$dbw = $this->dbProvider->getPrimaryDatabase( self::VIRTUAL_DOMAIN );
		$rows = [];
		$batchBytes = 0;

		foreach ( $entries as $entry ) {
			$value = $this->jsonCodec->serialize( $entry->value() );
			$key = $entry->key();
			$expiry = $dbw->timestampOrNull( $entry->exptime() );
			$tag = $entry->tag();
			$row = [
				'tc_key' => $key,
				'tc_value' => $value,
				'tc_exptime' => $expiry,
				'tc_tag' => $tag
			];

			// A multi-row upsert cannot contain the same unique key more than once on
			// all supported databases. Preserve the existing last-write-wins behavior.
			if ( isset( $rows[$key] ) ) {
				$batchBytes -= self::getRowSize( $rows[$key] );
			}
			$rows[$key] = $row;
			$batchBytes += self::getRowSize( $row );

			if ( count( $rows ) >= self::SET_BATCH_SIZE || $batchBytes >= self::SET_BATCH_BYTES ) {
				$this->setRows( $dbw, array_values( $rows ) );
				$rows = [];
				$batchBytes = 0;
			}
		}

		if ( $rows !== [] ) {
			$this->setRows( $dbw, array_values( $rows ) );
		}
	}

	/**
	 * @param IDatabase $dbw
	 * @param array<int,array<string,mixed>> $rows
	 */
	private function setRows( IDatabase $dbw, array $rows ): void {
		$dbw->newInsertQueryBuilder()
			->insertInto( self::TABLE_NAME )
			->rows( $rows )
			->onDuplicateKeyUpdate()
			->uniqueIndexFields( [ 'tc_key' ] )
			->set( [
				'tc_value' => new RawSQLValue( $dbw->buildExcludedValue( 'tc_value' ) ),
				'tc_exptime' => new RawSQLValue( $dbw->buildExcludedValue( 'tc_exptime' ) ),
				'tc_tag' => new RawSQLValue( $dbw->buildExcludedValue( 'tc_tag' ) )
			] )
			->caller( __METHOD__ )
			->execute();
	}

	/** @param array{tc_key:string,tc_value:string,tc_exptime:?string,tc_tag:?string} $row */
	private static function getRowSize( array $row ): int {
		return strlen( $row['tc_key'] ) +
			strlen( $row['tc_value'] ) +
			strlen( $row['tc_exptime'] ?? '' ) +
			strlen( $row['tc_tag'] ?? '' );
	}

	public function setExpiry( string $keyname, int $expiryTime ): void {
		$dbw = $this->dbProvider->getPrimaryDatabase( self::VIRTUAL_DOMAIN );
		$dbw->newUpdateQueryBuilder()
			->update( self::TABLE_NAME )
			->set( [ 'tc_exptime' => $dbw->timestamp( $expiryTime ) ] )
			->where( [ 'tc_key' => $keyname ] )
			->caller( __METHOD__ )
			->execute();
	}

	public function delete( string ...$keynames ): void {
		$dbw = $this->dbProvider->getPrimaryDatabase( self::VIRTUAL_DOMAIN );
		$dbw->newDeleteQueryBuilder()
			->deleteFrom( self::TABLE_NAME )
			->where( [ 'tc_key' => $keynames ] )
			->caller( __METHOD__ )
			->execute();
	}

	public function deleteEntriesWithTag( string $tag ): void {
		$dbw = $this->dbProvider->getPrimaryDatabase( self::VIRTUAL_DOMAIN );
		$dbw->newDeleteQueryBuilder()
			->deleteFrom( self::TABLE_NAME )
			->where( [ 'tc_tag' => $tag ] )
			->caller( __METHOD__ )
			->execute();
	}

	public function clear(): void {
		$dbw = $this->dbProvider->getPrimaryDatabase( self::VIRTUAL_DOMAIN );
		$dbw->newDeleteQueryBuilder()
			->deleteFrom( self::TABLE_NAME )
			->where( '*' )
			->caller( __METHOD__ )
			->execute();
	}

	/** @return PersistentCacheEntry[] */
	private function buildEntries( Iterator $rows ): array {
		$entries = [];
		foreach ( $rows as $row ) {
			$entries[] = new PersistentCacheEntry(
				$row->tc_key,
				$this->jsonCodec->deserialize( $row->tc_value ),
				$row->tc_exptime ? (int)wfTimestamp( TS_UNIX, $row->tc_exptime ) : null,
				$row->tc_tag
			);
		}

		return $entries;
	}
}
