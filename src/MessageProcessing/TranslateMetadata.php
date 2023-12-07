<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageProcessing;

use MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroups;
use MediaWiki\Extension\Translate\Utilities\Utilities;
use MediaWiki\MediaWikiServices;

/**
 * Offers functionality for reading and updating Translate group
 * related metadata
 *
 * @author Niklas Laxström
 * @author Santhosh Thottingal
 * @copyright Copyright © 2012-2013, Niklas Laxström, Santhosh Thottingal
 * @license GPL-2.0-or-later
 */
class TranslateMetadata {
	/** Map of (database group id => key => value) */
	private static array $cache = [];
	private static ?array $priorityCache = null;

	/**
	 * @param string[] $groups List of translate groups
	 * @param string $caller
	 */
	public static function preloadGroups( array $groups, string $caller ): void {
		$dbGroupIds = array_map( [ self::class, 'getGroupIdForDatabase' ], $groups );
		$missing = array_keys( array_diff_key( array_flip( $dbGroupIds ), self::$cache ) );
		if ( !$missing ) {
			return;
		}

		$functionName = __METHOD__ . " (for $caller)";

		self::$cache += array_fill_keys( $missing, null ); // cache negatives

		$dbr = Utilities::getSafeReadDB();
		$conditions = count( $missing ) <= 500 ? [ 'tmd_group' => array_map( 'strval', $missing ) ] : [];
		$res = $dbr->select(
			'translate_metadata',
			[ 'tmd_group', 'tmd_key', 'tmd_value' ],
			$conditions,
			$functionName
		);
		foreach ( $res as $row ) {
			self::$cache[$row->tmd_group][$row->tmd_key] = $row->tmd_value;
		}
	}

	/**
	 * Get a metadata value for the given group and key.
	 * @param string $group The group name
	 * @param string $key Metadata key
	 * @return string|bool
	 */
	public static function get( string $group, string $key ) {
		self::preloadGroups( [ $group ], __METHOD__ );
		return self::$cache[self::getGroupIdForDatabase( $group )][$key] ?? false;
	}

	/**
	 * Get a metadata value for the given group and key.
	 * If it does not exist, return the default value.
	 */
	public static function getWithDefaultValue(
		string $group, string $key, string $defaultValue
	): string {
		$value = self::get( $group, $key );
		return $value === false ? $defaultValue : $value;
	}

	/**
	 * Set a metadata value for the given group and metadata key. Updates the
	 * value if already existing.
	 * @param string $groupId The group id
	 * @param string $key Metadata key
	 * @param string|false $value Metadata value, false deletes from cache
	 */
	public static function set( string $groupId, string $key, $value ): void {
		$dbw = MediaWikiServices::getInstance()
			->getDBLoadBalancer()
			->getConnection( DB_PRIMARY );
		$dbGroupId = self::getGroupIdForDatabase( $groupId );
		$data = [ 'tmd_group' => $dbGroupId, 'tmd_key' => $key, 'tmd_value' => $value ];
		if ( $value === false ) {
			unset( $data['tmd_value'] );
			$dbw->delete( 'translate_metadata', $data, __METHOD__ );
			unset( self::$cache[$dbGroupId][$key] );
		} else {
			$dbw->replace(
				'translate_metadata',
				[ [ 'tmd_group', 'tmd_key' ] ],
				$data,
				__METHOD__
			);
			self::$cache[$dbGroupId][$key] = $value;
		}

		self::$priorityCache = null;
	}

	/**
	 * Wrapper for getting subgroups.
	 * @return string[]|null
	 */
	public static function getSubgroups( string $groupId ): ?array {
		$groups = self::get( $groupId, 'subgroups' );
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
	public static function setSubgroups( string $groupId, array $subgroupIds ): void {
		$subgroups = implode( '|', $subgroupIds );
		self::set( $groupId, 'subgroups', $subgroups );
	}

	/** Wrapper for deleting one wiki aggregate group at once. */
	public static function deleteGroup( string $groupId ): void {
		$dbw = MediaWikiServices::getInstance()
			->getDBLoadBalancer()
			->getConnection( DB_PRIMARY );

		$dbGroupId = self::getGroupIdForDatabase( $groupId );
		$conditions = [ 'tmd_group' => $dbGroupId ];
		$dbw->delete( 'translate_metadata', $conditions, __METHOD__ );
		self::$cache[ $dbGroupId ] = null;
		unset( self::$priorityCache[ $dbGroupId ] );
	}

	public static function isExcluded( string $groupId, string $code ): bool {
		if ( self::$priorityCache === null ) {
			$db = Utilities::getSafeReadDB();
			$res = $db->select(
				[
					'a' => 'translate_metadata',
					'b' => 'translate_metadata'
				],
				[
					'group' => 'b.tmd_group',
					'langs' => 'b.tmd_value',
				],
				[],
				__METHOD__,
				[],
				[
					'b' => [
						'INNER JOIN',
						[
							'a.tmd_group = b.tmd_group',
							'a.tmd_key' => 'priorityforce',
							'a.tmd_value' => 'on',
							'b.tmd_key' => 'prioritylangs',
						]
					]
				]
			);

			self::$priorityCache = [];
			foreach ( $res as $row ) {
				self::$priorityCache[$row->group] =
					array_flip( explode( ',', $row->langs ) );
			}
		}

		$dbGroupId = self::getGroupIdForDatabase( $groupId );
		$isDiscouraged = MessageGroups::getPriority( $groupId ) === 'discouraged';
		$hasLimitedLanguages = isset( self::$priorityCache[$dbGroupId] );
		$isLanguageIncluded = isset( self::$priorityCache[$dbGroupId][$code] );

		return $isDiscouraged || ( $hasLimitedLanguages && !$isLanguageIncluded );
	}

	/**
	 * Do a query optimized for page list in Special:PageTranslation
	 * @param string[] $groupIds
	 * @param string[] $keys Which metadata keys to load
	 * @return array<string,array<string,string>>
	 */
	public static function loadBasicMetadataForTranslatablePages( array $groupIds, array $keys ): array {
		$db = Utilities::getSafeReadDB();
		$dbGroupIdMap = [];

		foreach ( $groupIds as $groupId ) {
			$dbGroupIdMap[ self::getGroupIdForDatabase( $groupId ) ] = $groupId;
		}

		$res = $db->select(
			'translate_metadata',
			[ 'tmd_group', 'tmd_key', 'tmd_value' ],
			[
				'tmd_group' => array_keys( $dbGroupIdMap ),
				'tmd_key' => $keys,
			],
			__METHOD__
		);

		$ret = [];
		foreach ( $res as $row ) {
			$groupId = $row->tmd_group;
			// Remap the db group ids to group id in the response
			$ret[ $dbGroupIdMap[ $groupId ] ][ $row->tmd_key ] = $row->tmd_value;
		}

		return $ret;
	}

	public static function moveMetadata(
		string $oldGroupId,
		string $newGroupId,
		array $metadataKeysToMove
	): void {
		self::preloadGroups( [ $oldGroupId, $newGroupId ], __METHOD__ );
		foreach ( $metadataKeysToMove as $type ) {
			$value = self::get( $oldGroupId, $type );
			if ( $value !== false ) {
				self::set( $oldGroupId, $type, false );
				self::set( $newGroupId, $type, $value );
			}
		}
	}

	/**
	 * @param string $groupId
	 * @param string[] $metadataKeys
	 */
	public static function clearMetadata( string $groupId, array $metadataKeys ): void {
		// remove the entries from metadata table.
		foreach ( $metadataKeys as $type ) {
			self::set( $groupId, $type, false );
		}
	}

	/** Get groups ids that have subgroups set up. */
	public static function getGroupsWithSubgroups(): array {
		$tables = [ 'translate_metadata' ];
		$field = 'tmd_group';
		$conditions = [ 'tmd_key' => 'subgroups' ];

		$db = Utilities::getSafeReadDB();
		// There is no need to de-hash the group id from the database as
		// AggregateGroupsActionApi::generateAggregateGroupId already ensures that the length
		// is appropriate
		return $db->selectFieldValues( $tables, $field, $conditions, __METHOD__ );
	}

	private static function getGroupIdForDatabase( string $groupId ): string {
		// Check if length is more than 200 bytes
		if ( strlen( $groupId ) <= 200 ) {
			return $groupId;
		}

		$hash = hash( 'md5', $groupId );
		// We take 160 bytes of the original string and append the md5 hash (32 bytes)
		return mb_strcut( $groupId, 0, 160 ) . '||' . $hash;
	}
}

class_alias( TranslateMetadata::class, 'TranslateMetadata' );
