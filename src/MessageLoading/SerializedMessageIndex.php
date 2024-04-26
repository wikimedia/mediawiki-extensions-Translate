<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageLoading;

use MediaWiki\Extension\Translate\Utilities\Utilities;

/**
 * Storage on serialized file.
 *
 * This serializes the whole array. Because this format can preserve
 * the values which are stored as references inside the array, this is
 * the most space efficient storage method and fastest when you want
 * the full index.
 *
 * Unfortunately when the size of index grows to about 50000 items, even
 * though it is only 3,5M on disk, it takes 35M when loaded into memory
 * and the loading can take more than 0,5 seconds. Because usually we
 * need to look up only few keys, it is better to use another backend
 * which provides random access - this backend doesn't support that.
 */
class SerializedMessageIndex extends MessageIndexStore {
	private ?array $index = null;
	private const FILENAME = 'translate_messageindex.ser';

	public function retrieve( bool $readLatest = false ): array {
		if ( $this->index !== null ) {
			return $this->index;
		}

		$file = Utilities::cacheFile( self::FILENAME );
		if ( file_exists( $file ) ) {
			$this->index = unserialize( file_get_contents( $file ) );
		} else {
			$this->index = [];
		}

		return $this->index;
	}

	public function store( array $array, array $diff ): void {
		$file = Utilities::cacheFile( self::FILENAME );
		file_put_contents( $file, serialize( $array ) );
		$this->index = $array;
	}
}
