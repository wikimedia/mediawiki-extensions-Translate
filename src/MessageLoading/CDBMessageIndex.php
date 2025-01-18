<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageLoading;

use Cdb\Reader;
use Cdb\Writer;
use MediaWiki\Extension\Translate\Utilities\Utilities;

/**
 * Storage on CDB files.
 *
 * This is improved version of SerializedMessageIndex. It uses CDB files
 * for storage, which means it provides random access. The CDB files are
 * about double the size of serialized files (~7M for 50000 keys).
 *
 * Loading the whole index is slower than serialized, but about the same
 * as for database. Suitable for single-server setups where
 * SerializedMessageIndex is too slow for loading the whole index.
 */
class CDBMessageIndex extends MessageIndexStore {
	private ?array $index = null;
	private ?Reader $reader = null;
	private const FILENAME = 'translate_messageindex.cdb';

	public function retrieve( bool $readLatest = false ): array {
		$reader = $this->getReader();
		if ( $this->index !== null ) {
			return $this->index;
		}

		$this->index = [];
		foreach ( $this->getKeys() as $key ) {
			$this->index[$key] = $this->unserialize( $reader->get( $key ) );
		}

		return $this->index;
	}

	public function getKeys(): array {
		$reader = $this->getReader();
		$keys = [];
		$key = $reader->firstkey();
		while ( $key !== false ) {
			$keys[] = $key;
			$key = $reader->nextkey();
		}

		return $keys;
	}

	/** @inheritDoc */
	public function get( string $key ) {
		$reader = $this->getReader();
		// We might have the full cache loaded
		if ( $this->index !== null ) {
			return $this->index[$key] ?? null;
		}

		$value = $reader->get( $key );
		return is_string( $value ) ? $this->unserialize( $value ) : null;
	}

	/** @inheritDoc */
	public function store( array $array, array $diff ): void {
		$this->reader = null;

		$file = Utilities::cacheFile( self::FILENAME );
		$cache = Writer::open( $file );

		foreach ( $array as $key => $value ) {
			$value = $this->serialize( $value );
			$cache->set( $key, $value );
		}

		$cache->close();

		$this->index = $array;
	}

	private function getReader(): Reader {
		if ( $this->reader ) {
			return $this->reader;
		}

		$file = Utilities::cacheFile( self::FILENAME );
		if ( !file_exists( $file ) ) {
			// Create an empty index
			$this->store( [], [] );
		}

		$this->reader = Reader::open( $file );
		return $this->reader;
	}
}
