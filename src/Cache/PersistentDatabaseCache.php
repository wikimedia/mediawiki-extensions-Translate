<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Cache;

use Iterator;
use MediaWiki\Json\JsonCodec;
use Wikimedia\Rdbms\IConnectionProvider;

/**
 * A persistent cache implementation using the database.
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 * @since 2020.12
 */
class PersistentDatabaseCache implements PersistentCache {
	private const VIRTUAL_DOMAIN = 'virtual-translate';
	private const TABLE_NAME = 'translate_cache';
	private IConnectionProvider $dbProvider;
	private JsonCodec $jsonCodec;

	public function __construct( IConnectionProvider $dbProvider, JsonCodec $jsonCodec ) {
		$this->dbProvider = $dbProvider;
		$this->jsonCodec = $jsonCodec;
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

		foreach ( $entries as $entry ) {
			$value = $this->jsonCodec->serialize( $entry->value() );
			$dbw->newInsertQueryBuilder()
				->insertInto( self::TABLE_NAME )
				->row( [
					'tc_key' => $entry->key(),
					'tc_value' => $value,
					'tc_exptime' => $dbw->timestampOrNull( $entry->exptime() ),
					'tc_tag' => $entry->tag()
				] )
				->onDuplicateKeyUpdate()
				->uniqueIndexFields( [ 'tc_key' ] )
				->set( [
					'tc_value' => $value,
					'tc_exptime' => $dbw->timestampOrNull( $entry->exptime() ),
					'tc_tag' => $entry->tag()
				] )
				->caller( __METHOD__ )
				->execute();
		}
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
