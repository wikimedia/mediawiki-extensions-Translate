<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageProcessing;

use MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroups;
use MediaWiki\Extension\Translate\Utilities\Utilities;
use Wikimedia\Rdbms\IConnectionProvider;

/**
 * Offers functionality for reading and updating Translate group
 * related metadata
 *
 * @author Niklas Laxström
 * @author Santhosh Thottingal
 * @copyright Copyright © 2012-2013, Niklas Laxström, Santhosh Thottingal
 * @license GPL-2.0-or-later
 */
class MessageGroupMetadata {
	/** @var int Threshold for query batching */
	private const MAX_ITEMS_PER_QUERY = 2000;
	/** Map of (database group id => key => value) */
	private array $cache = [];
	private ?array $priorityCache = null;
	private IConnectionProvider $dbProvider;

	public function __construct( IConnectionProvider $dbProvider ) {
		$this->dbProvider = $dbProvider;
	}

	public function preloadGroups( array $groups, string $caller ): void {
		$dbGroupIds = array_map( [ $this, 'getGroupIdForDatabase' ], $groups );
		$missing = array_keys( array_diff_key( array_flip( $dbGroupIds ), $this->cache ) );
		if ( !$missing ) {
			return;
		}

		$functionName = __METHOD__ . " (for $caller)";

		$this->cache += array_fill_keys( $missing, null ); // cache negatives

		// TODO: Ideally, this should use the injected ILoadBalancer to make it mockable.
		$dbr = Utilities::getSafeReadDB();
		$chunks = array_chunk( $missing, self::MAX_ITEMS_PER_QUERY );
		foreach ( $chunks as $chunk ) {
			$res = $dbr->newSelectQueryBuilder()
				->select( [ 'tmd_group', 'tmd_key', 'tmd_value' ] )
				->from( 'translate_metadata' )
				->where( [ 'tmd_group' => array_map( 'strval', $chunk ) ] )
				->caller( $functionName )
				->fetchResultSet();
			foreach ( $res as $row ) {
				$this->cache[$row->tmd_group][$row->tmd_key] = $row->tmd_value;
			}
		}
	}

	/**
	 * Get a metadata value for the given group and key.
	 * @param string $group The group name
	 * @param string $key Metadata key
	 * @return string|bool
	 */
	public function get( string $group, string $key ) {
		$this->preloadGroups( [ $group ], __METHOD__ );
		return $this->cache[$this->getGroupIdForDatabase( $group )][$key] ?? false;
	}

	/**
	 * Get a metadata value for the given group and key.
	 * If it does not exist, return the default value.
	 */
	public function getWithDefaultValue( string $group, string $key, ?string $defaultValue ): ?string {
		$value = $this->get( $group, $key );
		return $value === false ? $defaultValue : $value;
	}

	/**
	 * Set a metadata value for the given group and metadata key. Updates the
	 * value if already existing.
	 * @param string $groupId The group id
	 * @param string $key Metadata key
	 * @param string|false $value Metadata value, false deletes from cache
	 */
	public function set( string $groupId, string $key, $value ): void {
		$dbw = $this->dbProvider->getPrimaryDatabase();
		$dbGroupId = $this->getGroupIdForDatabase( $groupId );
		$data = [ 'tmd_group' => $dbGroupId, 'tmd_key' => $key, 'tmd_value' => $value ];
		if ( $value === false ) {
			unset( $data['tmd_value'] );
			$dbw->newDeleteQueryBuilder()
				->deleteFrom( 'translate_metadata' )
				->where( $data )
				->caller( __METHOD__ )
				->execute();
			unset( $this->cache[$dbGroupId][$key] );
		} else {
			$dbw->newReplaceQueryBuilder()
				->replaceInto( 'translate_metadata' )
				->uniqueIndexFields( [ 'tmd_group', 'tmd_key' ] )
				->row( $data )
				->caller( __METHOD__ )
				->execute();
			$this->cache[$dbGroupId][$key] = $value;
		}

		$this->priorityCache = null;
	}

	/**
	 * Wrapper for getting subgroups.
	 * @return string[]|null
	 */
	public function getSubgroups( string $groupId ): ?array {
		$groups = $this->get( $groupId, 'subgroups' );
		if ( is_string( $groups ) ) {
			if ( str_contains( $groups, '|' ) ) {
				$groups = explode( '|', $groups );
			} else {
				$groups = array_map( 'trim', explode( ',', $groups ) );
			}

			foreach ( $groups as $index => $id ) {
				if ( trim( $id ) === '' ) {
					unset( $groups[$index] );
				}
			}
		} else {
			$groups = null;
		}

		return $groups;
	}

	/** Wrapper for setting subgroups. */
	public function setSubgroups( string $groupId, array $subgroupIds ): void {
		$subgroups = implode( '|', $subgroupIds );
		$this->set( $groupId, 'subgroups', $subgroups );
	}

	/** Wrapper for deleting one wiki aggregate group at once. */
	public function deleteGroup( string $groupId ): void {
		$dbw = $this->dbProvider->getPrimaryDatabase();

		$dbGroupId = $this->getGroupIdForDatabase( $groupId );
		$dbw->newDeleteQueryBuilder()
			->deleteFrom( 'translate_metadata' )
			->where( [ 'tmd_group' => $dbGroupId ] )
			->caller( __METHOD__ )
			->execute();
		$this->cache[ $dbGroupId ] = null;
		unset( $this->priorityCache[ $dbGroupId ] );
	}

	public function isExcluded( string $groupId, string $code ): bool {
		if ( $this->priorityCache === null ) {
			// TODO: Ideally, this should use the injected ILoadBalancer to make it mockable.
			$db = Utilities::getSafeReadDB();
			$res = $db->newSelectQueryBuilder()
				->select( [
					'group' => 'a.tmd_group',
					'langs' => 'b.tmd_value',
				] )
				->from( 'translate_metadata', 'a' )
				->leftJoin( 'translate_metadata', 'b', [
					'a.tmd_group = b.tmd_group',
					'b.tmd_key' => 'prioritylangs',
				] )
				->where( [
					'a.tmd_key' => 'priorityforce',
					'a.tmd_value' => 'on'
				] )
				->caller( __METHOD__ )
				->fetchResultSet();

			$this->priorityCache = [];
			foreach ( $res as $row ) {
				if ( isset( $row->langs ) ) {
					$this->priorityCache[ $row->group ] = array_flip( explode( ',', $row->langs ) );
				} else {
					$this->priorityCache[ $row->group ] = [];
				}
			}
		}

		$dbGroupId = $this->getGroupIdForDatabase( $groupId );
		$isDiscouraged = MessageGroups::getPriority( $groupId ) === 'discouraged';
		$hasLimitedLanguages = isset( $this->priorityCache[$dbGroupId] );
		$isLanguageIncluded = isset( $this->priorityCache[$dbGroupId][$code] );

		return $isDiscouraged || ( $hasLimitedLanguages && !$isLanguageIncluded );
	}

	/**
	 * Do a query optimized for page list in Special:PageTranslation
	 * @param string[] $groupIds
	 * @param string[] $keys Which metadata keys to load
	 * @return array<string,array<string,string>>
	 */
	public function loadBasicMetadataForTranslatablePages( array $groupIds, array $keys ): array {
		// TODO: Ideally, this should use the injected ILoadBalancer to make it mockable.
		$db = Utilities::getSafeReadDB();
		$dbGroupIdMap = [];

		foreach ( $groupIds as $groupId ) {
			$dbGroupIdMap[ $this->getGroupIdForDatabase( $groupId ) ] = $groupId;
		}

		$res = $db->newSelectQueryBuilder()
			->select( [ 'tmd_group', 'tmd_key', 'tmd_value' ] )
			->from( 'translate_metadata' )
			->where( [
				'tmd_group' => array_keys( $dbGroupIdMap ),
				'tmd_key' => $keys,
			] )
			->caller( __METHOD__ )
			->fetchResultSet();

		$ret = [];
		foreach ( $res as $row ) {
			$groupId = $row->tmd_group;
			// Remap the db group ids to group id in the response
			$ret[ $dbGroupIdMap[ $groupId ] ][ $row->tmd_key ] = $row->tmd_value;
		}

		return $ret;
	}

	public function moveMetadata(
		string $oldGroupId,
		string $newGroupId,
		array $metadataKeysToMove
	): void {
		$this->preloadGroups( [ $oldGroupId, $newGroupId ], __METHOD__ );
		foreach ( $metadataKeysToMove as $type ) {
			$value = $this->get( $oldGroupId, $type );
			if ( $value !== false ) {
				$this->set( $oldGroupId, $type, false );
				$this->set( $newGroupId, $type, $value );
			}
		}
	}

	/**
	 * @param string $groupId
	 * @param string[] $metadataKeys
	 */
	public function clearMetadata( string $groupId, array $metadataKeys ): void {
		// remove the entries from metadata table.
		foreach ( $metadataKeys as $type ) {
			$this->set( $groupId, $type, false );
		}
	}

	/** Get groups ids that have subgroups set up. */
	public function getGroupsWithSubgroups(): array {
		// TODO: Ideally, this should use the injected ILoadBalancer to make it mockable.
		$db = Utilities::getSafeReadDB();
		// There is no need to de-hash the group id from the database as
		// AggregateGroupsActionApi::generateAggregateGroupId already ensures that the length
		// is appropriate
		return $db->newSelectQueryBuilder()
			->select( 'tmd_group' )
			->from( 'translate_metadata' )
			->where( [ 'tmd_key' => 'subgroups' ] )
			->caller( __METHOD__ )
			->fetchFieldValues();
	}

	private function getGroupIdForDatabase( string $groupId ): string {
		// Check if length is more than 200 bytes
		if ( strlen( $groupId ) <= 200 ) {
			return $groupId;
		}

		$hash = hash( 'md5', $groupId );
		// We take 160 bytes of the original string and append the md5 hash (32 bytes)
		return mb_strcut( $groupId, 0, 160 ) . '||' . $hash;
	}
}
