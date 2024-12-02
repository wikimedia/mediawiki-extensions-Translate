<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\FileFormatSupport;

use FileBasedMessageGroup;
use MediaWiki\Content\TextContent;
use MediaWiki\Extension\Translate\MessageGroupConfiguration\MetaYamlSchemaExtender;
use MediaWiki\Extension\Translate\MessageLoading\Message;
use MediaWiki\Extension\Translate\MessageLoading\MessageCollection;
use MediaWiki\Extension\Translate\Utilities\Utilities;
use RuntimeException;

/**
 * JavaFormat class implements support for Java properties files.
 * This class reads and writes only utf-8 files. Java projects
 * need to run native2ascii on them before using them.
 *
 * This class adds a new item into FILES section of group configuration:
 * \c keySeparator which defaults to '='.
 * @ingroup FileFormatSupport
 */
class JavaFormat extends SimpleFormat implements MetaYamlSchemaExtender {

	private string $keySeparator;

	public function __construct( FileBasedMessageGroup $group ) {
		parent::__construct( $group );
		$this->keySeparator = $this->extra['keySeparator'] ?? '=';
	}

	public function supportsFuzzy(): string {
		return 'write';
	}

	public function getFileExtensions(): array {
		return [ '.properties' ];
	}

	/** @throws RuntimeException */
	public function readFromVariable( string $data ): array {
		$data = TextContent::normalizeLineEndings( $data );
		$lines = array_map( 'ltrim', explode( "\n", $data ) );
		$authors = $messages = [];
		$lineContinuation = false;

		$key = '';
		$value = '';
		foreach ( $lines as $line ) {
			if ( $lineContinuation ) {
				$lineContinuation = false;
				$valuecont = $line;
				$valuecont = str_replace( '\n', "\n", $valuecont );
				$value .= $valuecont;
			} else {
				if ( $line === '' ) {
					continue;
				}

				if ( $line[0] === '#' || $line[0] === '!' ) {
					$match = [];
					$ok = preg_match( '/#\s*Author:\s*(.*)/', $line, $match );

					if ( $ok ) {
						$authors[] = $match[1];
					}

					continue;
				}

				if ( !str_contains( $line, $this->keySeparator ) ) {
					throw new RuntimeException( "Line without separator '{$this->keySeparator}': $line." );
				}

				[ $key, $value ] = $this->readRow( $line, $this->keySeparator );
				if ( $key === '' ) {
					throw new RuntimeException( "Empty key in line $line." );
				}
			}

			// @todo This doesn't handle the pathological case of even number of trailing \
			if ( strlen( $value ) && $value[strlen( $value ) - 1] === "\\" ) {
				$value = substr( $value, 0, strlen( $value ) - 1 );
				$lineContinuation = true;
			} else {
				$messages[$key] = ltrim( $value );
			}
		}

		$messages = $this->group->getMangler()->mangleArray( $messages );

		return [
			'AUTHORS' => $authors,
			'MESSAGES' => $messages,
		];
	}

	protected function writeReal( MessageCollection $collection ): string {
		$header = $this->doHeader( $collection );
		$header .= $this->doAuthors( $collection );
		$header .= "\n";

		$output = '';
		$mangler = $this->group->getMangler();

		/** @var Message $message */
		foreach ( $collection as $key => $message ) {
			$value = $message->translation() ?? '';
			if ( $value === '' ) {
				continue;
			}

			$value = str_replace( TRANSLATE_FUZZY, '', $value );

			// Just to give an overview of translation quality.
			if ( $message->hasTag( 'fuzzy' ) ) {
				$output .= "# Fuzzy\n";
			}

			$key = $mangler->unmangle( $key );
			$output .= $this->writeRow( $key, $value );
		}

		if ( $output ) {
			return $header . $output;
		}

		return '';
	}

	/** Writes well-formed properties file row with key and value. */
	public function writeRow( string $key, string $value ): string {
		/* Keys containing the separator need escaping. Also escape comment
		 * characters, though strictly they would only need escaping when
		 * they are the first character. Plus the escape character itself. */
		$key = addcslashes( $key, "#!{$this->keySeparator}\\" );
		// Make sure we do not slip newlines trough... it would be fatal.
		$value = str_replace( "\n", '\\n', $value );

		return "$key{$this->keySeparator}$value\n";
	}

	/**
	 * Parses non-empty properties file row to key and value.
	 * @return string[]
	 */
	public function readRow( string $line, string $sep ): array {
		if ( !str_contains( $line, '\\' ) ) {
			/* Nothing appears to be escaped in this line.
			 * Just read the key and the value. */
			[ $key, $value ] = explode( $sep, $line, 2 );
		} else {
			/* There might be escaped separators in the key.
			 * Using slower method to find the separator. */

			/* Make the key default to empty instead of value, because
			 * empty key causes error on callers, while empty value
			 * wouldn't. */
			$key = '';
			$value = $line;

			/* Find the first unescaped separator. Example:
			 * First line is the string being read, second line is the
			 * value of $escaped after having read the above character.
			 *
			 * ki\ts\\s\=a = koira
			 * 0010010010000
			 *          ^ Not separator because $escaped was true
			 *             ^ Split the string into key and value here
			 */

			$len = strlen( $line );
			$escaped = false;
			for ( $i = 0; $i < $len; $i++ ) {
				$char = $line[$i];
				if ( $char === '\\' ) {
					$escaped = !$escaped;
				} elseif ( $escaped ) {
					$escaped = false;
				} elseif ( $char === $sep ) {
					$key = substr( $line, 0, $i );
					// Excluding the separator character from the value
					$value = substr( $line, $i + 1 );
					break;
				}
			}
		}

		/* We usually don't want to expand things like \t in values since
		 * translators cannot easily input those. But in keys we do.
		 * \n is exception we do handle in values. */
		$key = trim( $key );
		$key = stripcslashes( $key );
		$value = ltrim( $value );
		$value = str_replace( '\n', "\n", $value );

		return [ $key, $value ];
	}

	private function doHeader( MessageCollection $collection ): string {
		if ( isset( $this->extra['header'] ) ) {
			$output = $this->extra['header'];
		} else {
			global $wgSitename;

			$code = $collection->code;
			$name = Utilities::getLanguageName( $code );
			$native = Utilities::getLanguageName( $code, $code );
			$output = "# Messages for $name ($native)\n";
			$output .= "# Exported from $wgSitename\n";
		}

		return $output;
	}

	private function doAuthors( MessageCollection $collection ): string {
		$output = '';
		$authors = $collection->getAuthors();
		$authors = $this->filterAuthors( $authors, $collection->code );

		foreach ( $authors as $author ) {
			$output .= "# Author: $author\n";
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
							'keySeparator' => [
								'_type' => 'text',
							],
						]
					]
				]
			]
		];
	}
}

class_alias( JavaFormat::class, 'JavaFFS' );
