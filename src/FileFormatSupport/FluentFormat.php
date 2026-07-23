<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\FileFormatSupport;

use MediaWiki\Extension\Translate\MessageLoading\Message;
use MediaWiki\Extension\Translate\MessageLoading\MessageCollection;

/**
 * FileFormat class that implements support for Mozilla's Fluent file format (.ftl).
 *
 * ## Key naming
 * - Regular messages are stored under their identifier as-is.
 * - Terms are stored with a "-" prefix: "-term-id".
 * - Message attributes are stored as "message-id.attr-id".
 *
 * `readFromVariable()` returns these keys verbatim so that the parser output
 * matches the source .ftl file exactly. Key mangling (MediaWiki title-safety
 * escaping and group prefixing) is a platform concern applied later, at the
 * wiki-import boundary in `read()`, not during raw parsing. This keeps a valid
 * Fluent identifier such as `key_13_` intact through parsing while still
 * producing a title-safe key when the message is imported onto the wiki.
 *
 * ## Parsing design choices and spec deviations
 *
 * **Lenient entry parser**: The block splitter accepts any line starting with
 * `{` at column 0 as a continuation of the current entry (block_placeable per
 * the EBNF). While a placeable is open (unbalanced `{`), following lines —
 * including blank lines and lines whose closing `}` sits at column 0 — are
 * consumed as part of that entry until the braces balance, so multi-line
 * placeables and select expressions are kept whole. A comment line (`#` at
 * column 0) ends an unclosed placeable per the Fluent junk-recovery rules.
 * Lines that are neither blank, indented, `{`-prefixed, nor part of an open
 * placeable terminate the current entry and are discarded as junk without
 * halting parsing.
 *
 * **Values stored as raw FTL text**: Placeable and selector syntax inside
 * `{ ... }` is not interpreted or validated. The stored value is the literal
 * text after `=`, with only common indentation stripped. This means a
 * translator sees and round-trips the full FTL expression verbatim.
 *
 * **Brace-balance guard**: To prevent structurally broken values from being
 * written to output files, any entry whose value has unbalanced `{` / `}`
 * is silently dropped at read time. This catches entries with unclosed or
 * unmatched braces (e.g. `key = { 1` or `key = 1 }`). It does not catch
 * balanced-but-invalid placeable contents such as `{$}` or `{1x}` — those
 * require a full placeable parser and are a known remaining gap.
 *
 * **Comments not stored**: File-level, group-level, and message-level comments
 * are not stored as translatable units. Author comments (`# Author: ...`) are
 * extracted separately.
 *
 * **Multiline indentation**: Common indentation of continuation lines is
 * stripped per the Fluent spec, so the stored value is the logical content
 * without leading indent.
 *
 * @see https://projectfluent.org/
 * @see https://github.com/projectfluent/fluent/blob/main/spec/fluent.ebnf
 * @author Siebrand Mazeland
 * @license GPL-2.0-or-later
 * @ingroup FileFormatSupport
 * @since 2026.07
 */
class FluentFormat extends SimpleFormat {

	public function getFileExtensions(): array {
		return [ '.ftl' ];
	}

	public function readFromVariable( string $data ): array {
		$data = str_replace( "\r\n", "\n", $data );

		preg_match_all( '/^#\s*Author:\s*(.+)$/m', $data, $m );
		$authors = array_map( 'trim', $m[1] );

		$messages = iterator_to_array( $this->parseEntries( $data ) );

		return [
			'MESSAGES' => $messages,
			'AUTHORS' => $authors,
		];
	}

	/**
	 * @inheritDoc
	 *
	 * readFromVariable() returns message keys verbatim (see the "Key naming"
	 * section in the class docblock). Mangling for MediaWiki title-safety and
	 * group prefixing is applied here, at the wiki-import boundary, so that the
	 * raw parser output stays faithful to the source .ftl file while the keys
	 * stored on the wiki remain valid page titles.
	 */
	public function read( string $languageCode ) {
		$parsed = parent::read( $languageCode );
		if ( is_array( $parsed ) && isset( $parsed['MESSAGES'] ) ) {
			$parsed['MESSAGES'] = $this->group->getMangler()->mangleArray( $parsed['MESSAGES'] );
		}

		return $parsed;
	}

	/** @return iterable<string, string> */
	private function parseEntries( string $data ): iterable {
		// Split into entry blocks: a new block starts at a line beginning with
		// an identifier ([a-zA-Z]), a term (-[a-zA-Z]), or a comment (#).
		// Blank lines between entries are not part of any entry.
		$lines = explode( "\n", $data );
		$blocks = [];
		$current = null;
		// Net number of unclosed '{' in the current block. While it is > 0 we are
		// inside a placeable expression that may span several lines.
		$depth = 0;

		foreach ( $lines as $line ) {
			if ( $current !== null && $depth > 0 ) {
				// Inside an unclosed placeable: blank lines and content lines are
				// part of the expression until the braces balance again (e.g. a
				// multi-line "{ ... }" whose closing brace sits at column 0). A
				// comment line ('#' at column 0) ends the entry per the Fluent
				// junk-recovery rules; the entry is then dropped because its value
				// stays brace-unbalanced.
				if ( !str_starts_with( $line, '#' ) ) {
					$current .= "\n" . $line;
					$depth += $this->netBraceDepth( $line );
					continue;
				}
				$blocks[] = $current;
				$current = null;
				$depth = 0;
				// Fall through so the comment line starts its own block.
			}

			if ( preg_match( '/^[a-zA-Z\-#]/', $line ) ) {
				if ( $current !== null ) {
					$blocks[] = $current;
				}
				$current = $line;
				$depth = $this->netBraceDepth( $line );
			} elseif ( $current !== null && ( $line === '' || preg_match( '/^[\s{]/', $line ) ) ) {
				// Blank lines, indented lines, and lines starting with '{' (block_placeable
				// per the Fluent EBNF) are continuations of the current entry.
				$current .= "\n" . $line;
				$depth += $this->netBraceDepth( $line );
			} elseif ( $line !== '' ) {
				// Junk line: close current entry, skip the junk
				if ( $current !== null ) {
					$blocks[] = $current;
					$current = null;
					$depth = 0;
				}
			}
		}
		if ( $current !== null ) {
			$blocks[] = $current;
		}

		foreach ( $blocks as $block ) {
			// Skip pure comment blocks
			if ( str_starts_with( $block, '#' ) ) {
				continue;
			}

			$isTerm = str_starts_with( $block, '-' );
			// Strip leading '-' for terms to reuse the same identifier regex
			$parseBlock = $isTerm ? substr( $block, 1 ) : $block;

			// Capture everything after '='. Horizontal whitespace between '=' and the
			// value on the first line is insignificant and is consumed here; whitespace on
			// the continuation lines is significant for block-value indentation and is kept.
			if ( !preg_match( '/^([a-zA-Z][a-zA-Z0-9_-]*)\s*=[^\S\n]*(.*)$/s', $parseBlock, $m ) ) {
				continue;
			}

			$id = ( $isTerm ? '-' : '' ) . $m[1];
			$rest = $m[2];

			// Split rest into value part and attribute lines
			$restLines = explode( "\n", $rest );

			// Collect the value lines (before the first attribute)
			$valueLines = [];
			$attrStart = null;
			foreach ( $restLines as $i => $rline ) {
				if ( preg_match( '/^\s*\.([a-zA-Z][a-zA-Z0-9_-]*)\s*=\s*(.*)$/', $rline ) ) {
					$attrStart = $i;
					break;
				}
				$valueLines[] = $rline;
			}

			$value = $this->extractValue( $valueLines );
			// Terms must have a value; attributes-only terms are junk per the Fluent spec.
			if ( $isTerm && $value === '' ) {
				continue;
			}
			if ( $value !== '' && $this->isBraceBalanced( $value ) ) {
				yield $id => $value;
			}

			// Parse attributes
			if ( $attrStart !== null ) {
				$attrLines = array_slice( $restLines, $attrStart );
				foreach ( $this->parseAttributes( $attrLines ) as [ $attrName, $attrValue ] ) {
					if ( $this->isBraceBalanced( $attrValue ) ) {
						yield "$id.$attrName" => $attrValue;
					}
				}
			}
		}
	}

	/**
	 * Extract the logical value from raw lines after "=", stripping common indent.
	 * $lines[0] is the inline part (may be empty); subsequent elements are continuations.
	 *
	 * @param string[] $lines
	 */
	private function extractValue( array $lines ): string {
		// Inline value on the same line as "=": leading and trailing whitespace stripped
		$inline = trim( $lines[0] ?? '' );
		$continuations = array_slice( $lines, 1 );

		if ( $continuations === [] ) {
			return $inline;
		}

		// If there is an inline value, it forms the first logical line;
		// continuation lines are appended. If there is no inline value,
		// the value is entirely in the continuation lines.
		if ( $inline !== '' ) {
			// The inline line has no indent to strip; strip common indent of continuations only
			$indent = $this->commonIndent( $continuations );
			$result = [ $inline ];
			foreach ( $continuations as $line ) {
				$result[] = $line === '' ? '' : substr( $line, $indent );
			}
			return rtrim( implode( "\n", $result ) );
		}

		// Block value: strip common indent, drop leading/trailing blank lines
		$indent = $this->commonIndent( $continuations );
		$stripped = array_map(
			static fn ( string $l ) => $l === '' ? '' : substr( $l, $indent ),
			$continuations
		);

		// Drop leading blank lines
		while ( $stripped !== [] && $stripped[0] === '' ) {
			array_shift( $stripped );
		}

		return rtrim( implode( "\n", $stripped ) );
	}

	/** See class docblock for the brace-balance design rationale. */
	private function isBraceBalanced( string $value ): bool {
		$depth = 0;
		foreach ( str_split( $value ) as $char ) {
			if ( $char === '{' ) {
				$depth++;
			} elseif ( $char === '}' ) {
				if ( --$depth < 0 ) {
					return false;
				}
			}
		}
		return $depth === 0;
	}

	/** Net brace balance of a single line: count of '{' minus count of '}'. */
	private function netBraceDepth( string $line ): int {
		return substr_count( $line, '{' ) - substr_count( $line, '}' );
	}

	/**
	 * Compute the length of the common whitespace prefix across all non-blank lines.
	 *
	 * @param string[] $lines
	 */
	private function commonIndent( array $lines ): int {
		$min = PHP_INT_MAX;
		foreach ( $lines as $line ) {
			// Whitespace-only lines are blank lines per the Fluent spec and do not
			// contribute to the common indent.
			if ( trim( $line ) === '' ) {
				continue;
			}
			$len = strlen( $line ) - strlen( ltrim( $line ) );
			$min = min( $min, $len );
		}
		return $min === PHP_INT_MAX ? 0 : $min;
	}

	/**
	 * Parse ".attr-name = value" lines into [attr-name, value] pairs.
	 *
	 * @param string[] $lines
	 * @return iterable<array{0: string, 1: string}>
	 */
	private function parseAttributes( array $lines ): iterable {
		$attrName = null;
		$attrLines = [];

		$flush = function () use ( &$attrName, &$attrLines ): ?array {
			if ( $attrName === null ) {
				return null;
			}
			$value = $this->extractValue( $attrLines );
			$result = $value !== '' ? [ $attrName, $value ] : null;
			$attrName = null;
			$attrLines = [];
			return $result;
		};

		foreach ( $lines as $line ) {
			if ( preg_match( '/^\s*\.([a-zA-Z][a-zA-Z0-9_-]*)\s*=\s*(.*)$/', $line, $m ) ) {
				$pair = $flush();
				if ( $pair !== null ) {
					yield $pair;
				}
				$attrName = $m[1];
				$attrLines = [ $m[2] ];
			} elseif ( $attrName !== null ) {
				$attrLines[] = $line;
			}
		}

		$pair = $flush();
		if ( $pair !== null ) {
			yield $pair;
		}
	}

	protected function writeReal( MessageCollection $collection ): string {
		$mangler = $this->group->getMangler();
		$authors = $this->filterAuthors( $collection->getAuthors(), $collection->getLanguage() );
		$output = '';

		foreach ( $authors as $author ) {
			$output .= "# Author: $author\n";
		}
		if ( $authors ) {
			$output .= "\n";
		}

		// Group keys by their base message id so attributes are written together
		$byMessage = [];
		/** @var Message $m */
		foreach ( $collection as $key => $m ) {
			$value = $m->translation();
			if ( $value === null ) {
				continue;
			}
			$value = str_replace( TRANSLATE_FUZZY, '', $value );
			$unmangledKey = $mangler->unmangle( $key );

			// Split "message-id.attr-name" into base + attr
			if ( preg_match( '/^(.+)\.([a-zA-Z][a-zA-Z0-9_-]*)$/', $unmangledKey, $m2 ) ) {
				$byMessage[$m2[1]] ??= [ 'value' => null, 'attrs' => [] ];
				$byMessage[$m2[1]]['attrs'][$m2[2]] = $value;
			} else {
				$byMessage[$unmangledKey] ??= [ 'value' => null, 'attrs' => [] ];
				$byMessage[$unmangledKey]['value'] = $value;
			}
		}

		foreach ( $byMessage as $id => $parts ) {
			$msgValue = $parts['value'];
			$attrs = $parts['attrs'];
			// An empty value would be written as "id =", which is junk on the next read.
			$hasValue = $msgValue !== null && $msgValue !== '';

			// Terms must have a value: a term with only attributes is junk per the Fluent
			// spec and would be dropped when the file is read back.
			if ( !$hasValue && ( $attrs === [] || str_starts_with( $id, '-' ) ) ) {
				continue;
			}

			if ( $hasValue ) {
				$output .= $this->formatValue( $id, $msgValue );
			} else {
				// Message with only attributes and no value
				$output .= "$id =\n";
			}

			foreach ( $attrs as $attrName => $attrValue ) {
				$output .= $this->formatValue( "    .$attrName", $attrValue );
			}

			$output .= "\n";
		}

		// Remove the trailing extra newline added after the last message
		return rtrim( $output, "\n" ) . "\n";
	}

	private function formatValue( string $key, string $value ): string {
		if ( str_contains( $value, "\n" ) ) {
			$lines = explode( "\n", $value );
			$indented = implode( "\n    ", $lines );
			return "$key =\n    $indented\n";
		}
		return "$key = $value\n";
	}
}
