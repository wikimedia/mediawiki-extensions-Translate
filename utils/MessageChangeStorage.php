<?php
/**
 * Handles storage / retrival of data from message change files.
 *
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @since 2016.02
 * @file
 */

class MessageChangeStorage {
	const DEFAULT_NAME = 'default';

	/**
	 * Writes change array as a serialized file.
	 *
	 * @param MessageSourceChange[] $changes Array of changes as returned by processGroup
	 * indexed by message group id.
	 * @param string $file Which file to use.
	 */
	public static function writeChanges( array $changes, $file ) {
		$cache = \Cdb\Writer::open( $file );
		$keys = array_keys( $changes );
		$cache->set( '#keys', serialize( $keys ) );

		/**
		 * @var MessageSourceChange $change
		 */
		foreach ( $changes as $key => $change ) {
			$value = serialize( $change->getModifications() );
			$cache->set( $key, $value );
		}
		$cache->close();
	}

	/**
	 * Validate a name.
	 *
	 * @param string $name Which file to use.
	 * @return bool
	 */
	public static function isValidCdbName( $name ) {
		return preg_match( '/^[a-z_-]{1,100}$/i', $name );
	}

	/**
	 * Get a full path to file in a known location.
	 *
	 * @param string $name Which file to use.
	 * @return string
	 */
	public static function getCdbPath( $name ) {
		return TranslateUtils::cacheFile( "messagechanges.$name.cdb" );
	}

	/**
	 * Fetches changes for a group from the message change file.
	 * @param string $name Which file to use.
	 * @param string $groupId Group Id
	 * @return MessageSourceChange
	 */
	public static function getGroupChanges( $name, $groupId ) {
		$cdbPath = self::getCdbPath( $name );
		if ( !self::isValidCdbName( $name ) ) {
			throw new InvalidArgumentException( "Invalid CDB file name passed - '$name'. " );
		}

		// File not found, probably no changes.
		if ( !file_exists( $cdbPath ) ) {
			return MessageSourceChange::loadModifications( [] );
		}

		$reader = \Cdb\Reader::open( $cdbPath );
		$groups = unserialize( $reader->get( '#keys' ) );

		if ( !in_array( $groupId, $groups, true ) ) {
			throw new InvalidArgumentException( "Group Id - '$groupId' not found in cdb file." );
		}

		$group = MessageGroups::getGroup( $groupId );
		if ( $group === null ) {
			throw new InvalidArgumentException( "Group Id - '$groupId' not found in the system." );
		}

		return MessageSourceChange::loadModifications(
			unserialize( $reader->get( $groupId ) )
		);
	}

	/**
	 * Writes changes for a group. Has to read the changes first from the file,
	 * and then re-write them to the file.
	 * @param MessageSourceChange $changes
	 * @param string $groupId
	 * @param string $fileName
	 * @return void
	 */
	public static function writeGroupChanges( MessageSourceChange $changes, $groupId, $fileName ) {
		$reader = self::getCdbReader( $fileName );
		if ( $reader === null ) {
			return MessageSourceChange::loadModifications( [] );
		}

		$groups = unserialize( $reader->get( '#keys' ) );

		$allChanges = [];
		foreach ( $groups as $id ) {
			$allChanges[$id] = MessageSourceChange::loadModifications(
				unserialize( $reader->get( $id ) )
			);
		}
		$allChanges[$groupId] = $changes;

		$filePath = self::getCdbPath( $fileName );
		self::writeChanges( $allChanges, $filePath );
	}

	/**
	 * Validate and return a reader reference to the CDB file
	 * @param string $name
	 * @return \Cdb\Reader
	 */
	private function getCdbReader( $name ) {
		if ( !self::isValidCdbName( $name ) ) {
			throw new InvalidArgumentException( "Invalid CDB name passed - '$name'." );
		}

		$cdbPath = self::getCdbPath( $name );
		// File not found, probably no changes.
		if ( !file_exists( $cdbPath ) ) {
			return null;
		}

		return \Cdb\Reader::open( $cdbPath );
	}
}
