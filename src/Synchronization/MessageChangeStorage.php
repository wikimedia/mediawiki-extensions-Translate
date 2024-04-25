<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Synchronization;

use Cdb\Reader;
use Cdb\Writer;
use InvalidArgumentException;
use MediaWiki\Extension\Translate\MessageSync\MessageSourceChange;
use MediaWiki\Extension\Translate\Utilities\Utilities;

/**
 * Handles storage / retrieval of data from message change files.
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */
class MessageChangeStorage {
	public const DEFAULT_NAME = 'default';

	/**
	 * Writes change array as a serialized file.
	 * @param MessageSourceChange[] $changes Array of changes as returned by processGroup
	 * indexed by message group id.
	 * @param string $file Which file to use.
	 */
	public static function writeChanges( array $changes, string $file ): void {
		$cache = Writer::open( $file );
		$keys = array_keys( $changes );
		$cache->set( '#keys', Utilities::serialize( $keys ) );

		foreach ( $changes as $key => $change ) {
			$value = Utilities::serialize( $change->getAllModifications() );
			$cache->set( $key, $value );
		}
		$cache->close();
	}

	/** Validate a file name. */
	public static function isValidCdbName( string $fileName ): bool {
		return (bool)preg_match( '/^[a-z_-]{1,100}$/i', $fileName );
	}

	/** Get a full path to file in a known location. */
	public static function getCdbPath( string $fileName ): string {
		return Utilities::cacheFile( "messagechanges.$fileName.cdb" );
	}

	public static function getGroupChanges( string $cdbPath, string $groupId ): MessageSourceChange {
		$reader = self::getCdbReader( $cdbPath );
		if ( $reader === null ) {
			return MessageSourceChange::loadModifications( [] );
		}

		$groups = Utilities::deserialize( $reader->get( '#keys' ) );

		if ( !in_array( $groupId, $groups, true ) ) {
			throw new InvalidArgumentException( "Group Id - '$groupId' not found in cdb file " .
				"(path: $cdbPath)." );
		}

		return MessageSourceChange::loadModifications(
			Utilities::deserialize( $reader->get( $groupId ) )
		);
	}

	/**
	 * Writes changes for a group. Has to read the changes first from the file
	 * and then re-write them to the file.
	 */
	public static function writeGroupChanges(
		MessageSourceChange $changes,
		string $groupId,
		string $cdbPath
	): void {
		$reader = self::getCdbReader( $cdbPath );
		if ( $reader === null ) {
			return;
		}

		$groups = Utilities::deserialize( $reader->get( '#keys' ) );

		$allChanges = [];
		foreach ( $groups as $id ) {
			$allChanges[$id] = MessageSourceChange::loadModifications(
				Utilities::deserialize( $reader->get( $id ) )
			);
		}
		$allChanges[$groupId] = $changes;

		self::writeChanges( $allChanges, $cdbPath );
	}

	/** Validate and return a reader reference to the CDB file */
	private static function getCdbReader( string $cdbPath ): ?Reader {
		// File not found, probably no changes.
		if ( !file_exists( $cdbPath ) ) {
			return null;
		}

		return Reader::open( $cdbPath );
	}

	/**
	 * Gets the last modified time for the CDB file.
	 * @return int|null time of last modification (Unix timestamp)
	 */
	public static function getLastModifiedTime( string $cdbPath ): ?int {
		// File not found
		if ( !file_exists( $cdbPath ) ) {
			return null;
		}

		$stat = stat( $cdbPath );

		return $stat['mtime'];
	}

	/** Checks if the CDB file has been modified since the time given. */
	public static function isModifiedSince( string $cdbPath, int $unixTimestamp ): bool {
		$lastModifiedTime = self::getLastModifiedTime( $cdbPath );

		if ( $lastModifiedTime === null ) {
			throw new InvalidArgumentException( "CDB file not found - $cdbPath" );
		}

		return $lastModifiedTime <= $unixTimestamp;
	}
}
