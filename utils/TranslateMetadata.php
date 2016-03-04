<?php
/**
 * Contains class which offers functionality for reading and updating Translate group
 * related metadata
 *
 * @file
 * @author Niklas Laxström
 * @author Santhosh Thottingal
 * @copyright Copyright © 2012-2013, Niklas Laxström, Santhosh Thottingal
 * @license GPL-2.0+
 */

class TranslateMetadata {
	protected static $cache;

	/**
	 * Get a metadata value for the given group and key.
	 * @param $group string The group name
	 * @param $key string Metadata key
	 * @return String
	 */
	public static function get( $group, $key ) {
		if ( self::$cache === null ) {
			$dbr = wfGetDB( DB_SLAVE );
			$res = $dbr->select( 'translate_metadata', '*', array(), __METHOD__ );
			foreach ( $res as $row ) {
				self::$cache[$row->tmd_group][$row->tmd_key] = $row->tmd_value;
			}
		}

		if ( isset( self::$cache[$group][$key] ) ) {
			return self::$cache[$group][$key];
		}

		return false;
	}

	/**
	 * Set a metadata value for the given group and metadata key. Updates the
	 * value if already existing.
	 * @param $group string The group id
	 * @param $key string Metadata key
	 * @param $value string Metadata value
	 */
	public static function set( $group, $key, $value ) {
		$dbw = wfGetDB( DB_MASTER );
		$data = array( 'tmd_group' => $group, 'tmd_key' => $key, 'tmd_value' => $value );
		if ( $value === false ) {
			unset( $data['tmd_value'] );
			$dbw->delete( 'translate_metadata', $data );
		} else {
			$dbw->replace(
				'translate_metadata',
				array( array( 'tmd_group', 'tmd_key' ) ),
				$data,
				__METHOD__
			);
		}

		self::$cache = null;
	}

	/**
	 * Wrapper for getting subgroups.
	 * @param string $groupId
	 * @return array|String
	 * @since 2012-05-09
	 * return array|false
	 */
	public static function getSubgroups( $groupId ) {
		$groups = self::get( $groupId, 'subgroups' );
		if ( $groups !== false ) {
			if ( strpos( $groups, '|' ) !== false ) {
				$groups = explode( '|', $groups );
			} else {
				$groups = array_map( 'trim', explode( ',', $groups ) );
			}

			foreach ( $groups as $index => $id ) {
				if ( trim( $id ) === '' ) {
					unset( $groups[$index] );
				}
			}
		}

		return $groups;
	}

	/**
	 * Wrapper for setting subgroups.
	 * @param string $groupId
	 * @param array $subgroupIds
	 * @since 2012-05-09
	 */
	public static function setSubgroups( $groupId, $subgroupIds ) {
		$subgroups = implode( '|', $subgroupIds );
		self::set( $groupId, 'subgroups', $subgroups );
	}

	/**
	 * Wrapper for deleting one wiki aggregate group at once.
	 * @param string $groupId
	 * @since 2012-05-09
	 */
	public static function deleteGroup( $groupId ) {
		$dbw = wfGetDB( DB_MASTER );
		$conds = array( 'tmd_group' => $groupId );
		$dbw->delete( 'translate_metadata', $conds );
	}
}
