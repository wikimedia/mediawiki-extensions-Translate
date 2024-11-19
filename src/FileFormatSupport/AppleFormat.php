<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\FileFormatSupport;

use InvalidArgumentException;
use MediaWiki\Extension\Translate\MessageLoading\Message;
use MediaWiki\Extension\Translate\MessageLoading\MessageCollection;
use MediaWiki\Extension\Translate\Utilities\Utilities;
use RuntimeException;

/**
 * AppleFFS class implements support for Apple .strings files.
 * This class reads and writes only UTF-8 files.
 *
 * This class has not yet been battle-tested, so beware.
 *
 * @author Brion Vibber <bvibber@wikimedia.org>
 * @ingroup FileFormatSupport
 */
class AppleFormat extends SimpleFormat {
	public function supportsFuzzy(): string {
		return 'write';
	}

	public function getFileExtensions(): array {
		return [ '.strings' ];
	}

	/** @inheritDoc */
	public function readFromVariable( string $data ): array {
		$lines = explode( "\n", $data );
		$authors = $messages = [];
		$linecontinuation = false;

		foreach ( $lines as $line ) {
			$line = trim( $line );
			if ( $linecontinuation ) {
				if ( str_contains( $line, '*/' ) ) {
					$linecontinuation = false;
				}
			} else {
				if ( $line === '' ) {
					continue;
				}

				if ( substr( $line, 0, 2 ) === '//' ) {
					// Single-line comment
					$match = [];
					$ok = preg_match( '~//\s*Author:\s*(.*)~', $line, $match );
					if ( $ok ) {
						$authors[] = $match[1];
					}
					continue;
				}

				if ( substr( $line, 0, 2 ) === '/*' ) {
					if ( strpos( $line, '*/', 2 ) === false ) {
						$linecontinuation = true;
					}
					continue;
				}

				[ $key, $value ] = $this->readRow( $line );
				$messages[$key] = $value;
			}
		}

		$messages = $this->group->getMangler()->mangleArray( $messages );

		return [
			'AUTHORS' => $authors,
			'MESSAGES' => $messages,
		];
	}

	/**
	 * Parses non-empty strings file row to key and value.
	 * Can be overridden by child classes.
	 * @throws InvalidArgumentException
	 * @return array array( string $key, string $val )
	 */
	public function readRow( string $line ): array {
		$match = [];
		if ( preg_match( '/^"((?:\\\"|[^"])*)"\s*=\s*"((?:\\\"|[^"])*)"\s*;\s*$/', $line, $match ) ) {
			$key = self::unescapeString( $match[1] );
			$value = self::unescapeString( $match[2] );
			if ( $key === '' ) {
				throw new InvalidArgumentException( "Empty key in line $line" );
			}
			return [ $key, $value ];
		} else {
			throw new InvalidArgumentException( "Unrecognized line format: $line" );
		}
	}

	protected function writeReal( MessageCollection $collection ): string {
		$header = $this->doHeader( $collection );
		$header .= $this->doAuthors( $collection );
		$header .= "\n";

		$output = '';
		$mangler = $this->group->getMangler();

		$collection->filter( MessageCollection::FILTER_HAS_TRANSLATION, MessageCollection::INCLUDE_MATCHING );
		/** @var Message $m */
		foreach ( $collection as $key => $m ) {
			$value = $m->translation();
			if ( $value === null ) {
				throw new RuntimeException( "Expected translation to be present for $key, but found null." );
			}
			$value = str_replace( TRANSLATE_FUZZY, '', $value );

			if ( $value === '' ) {
				continue;
			}

			// Just to give an overview of translation quality.
			if ( $m->hasTag( 'fuzzy' ) ) {
				$output .= "// Fuzzy\n";
			}

			$key = $mangler->unmangle( $key );
			$output .= $this->writeRow( $key, $value );
		}

		if ( $output ) {
			$data = $header . $output;
		} else {
			$data = $header;
		}

		return $data;
	}

	/**
	 * Writes well-formed properties file row with key and value.
	 * Can be overridden by child classes.
	 */
	public function writeRow( string $key, string $value ): string {
		return self::quoteString( $key ) . ' = ' . self::quoteString( $value ) . ';' . "\n";
	}

	/** Quote and escape Obj-C-style strings for .strings format. */
	protected static function quoteString( string $str ): string {
		return '"' . self::escapeString( $str ) . '"';
	}

	/** Escape Obj-C-style strings; use backslash-escapes etc. */
	private static function escapeString( string $str ): string {
		return str_replace( "\n", '\\n', addcslashes( $str, '\\"' ) );
	}

	/**
	 * Unescape Obj-C-style strings; can include backslash-escapes
	 *
	 * @todo support \UXXXX
	 */
	protected static function unescapeString( string $str ): string {
		return stripcslashes( $str );
	}

	private function doHeader( MessageCollection $collection ): string {
		if ( isset( $this->extra['header'] ) ) {
			$output = $this->extra['header'];
		} else {
			global $wgSitename;

			$code = $collection->code;
			$name = Utilities::getLanguageName( $code );
			$native = Utilities::getLanguageName( $code, $code );
			$output = "// Messages for $name ($native)\n";
			$output .= "// Exported from $wgSitename\n";
		}

		return $output;
	}

	private function doAuthors( MessageCollection $collection ): string {
		$output = '';
		$authors = $collection->getAuthors();
		$authors = $this->filterAuthors( $authors, $collection->code );

		foreach ( $authors as $author ) {
			$output .= "// Author: $author\n";
		}

		return $output;
	}

	public static function getExtraSchema(): array {
		return [
			'root' => [
				'_type' => 'array',
				'_children' => [
					'FILES' => [
						'_type' => 'array',
						'_children' => [
							'header' => [
								'_type' => 'text',
							],
						]
					]
				]
			]
		];
	}
}

class_alias( AppleFormat::class, 'AppleFFS' );
