<?php
/**
 * Handles storage / retrieval of data from message change files.
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
	 * @param string $cdbPath Path of the cdb file.
	 * @param string $groupId Group Id
	 * @return MessageSourceChange
	 */
	public static function getGroupChanges( $cdbPath, $groupId ) {
		$reader = self::getCdbReader( $cdbPath );
		if ( $reader === null ) {
			return MessageSourceChange::loadModifications( [] );
		}

		$groups = unserialize( $reader->get( '#keys' ), [ 'allowed_classes' => false ] );

		if ( !in_array( $groupId, $groups, true ) ) {
			throw new InvalidArgumentException( "Group Id - '$groupId' not found in cdb file." );
		}

		return MessageSourceChange::loadModifications(
			unserialize( $reader->get( $groupId ), [ 'allowed_classes' => false ] )
		);
	}

	/**
	 * Writes changes for a group. Has to read the changes first from the file,
	 * and then re-write them to the file.
	 * @param MessageSourceChange $changes
	 * @param string $groupId Group Id
	 * @param string $cdbPath Path of the cdb file.
	 * @return void
	 */
	public static function writeGroupChanges( MessageSourceChange $changes, $groupId, $cdbPath ) {
		$reader = self::getCdbReader( $cdbPath );
		if ( $reader === null ) {
			return MessageSourceChange::loadModifications( [] );
		}

		$groups = unserialize( $reader->get( '#keys' ), [ 'allowed_classes' => false ] );

		$allChanges = [];
		foreach ( $groups as $id ) {
			$allChanges[$id] = MessageSourceChange::loadModifications(
				unserialize( $reader->get( $id ), [ 'allowed_classes' => false ] )
			);
		}
		$allChanges[$groupId] = $changes;

		self::writeChanges( $allChanges, $cdbPath );
	}

	/**
	 * Validate and return a reader reference to the CDB file
	 * @param string $name
	 * @return \Cdb\Reader
	 */
	private static function getCdbReader( $cdbPath ) {
		// File not found, probably no changes.
		if ( !file_exists( $cdbPath ) ) {
			return null;
		}

		return \Cdb\Reader::open( $cdbPath );
	}

	/**
	 * Gets the last modified time for the CDB file.
	 *
	 * @param string $cdbPath
	 * @return int
	 */
	public static function getLastModifiedTime( $cdbPath ) {
		// File not found
		if ( !file_exists( $cdbPath ) ) {
			return null;
		}

		$stat = stat( $cdbPath );

		return $stat['mtime'];
	}

	/**
	 * Checks if the CDB file has been modified since the time given.
	 * @param string $cdbPath
	 * @param int $time
	 * @return bool
	 */
	public static function isLatestVersion( $cdbPath, $time ) {
		$lastModifiedTime = self::getLastModifiedTime( $cdbPath );

		if ( $lastModifiedTime === null ) {
			throw new InvalidArgumentException( "CDB file not found - " . $cdbPath );
		}

		if ( $lastModifiedTime <= $time ) {
			return true;
		}

		return false;
	}
}
