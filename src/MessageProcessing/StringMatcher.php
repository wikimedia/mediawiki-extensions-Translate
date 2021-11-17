<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageProcessing;

use MetaYamlSchemaExtender;
use Title;

/**
 * The versatile default implementation of StringMangler interface.
 * It supports exact matches and patterns with any-wildcard (*).
 * All matching strings are prefixed with the same prefix.
 *
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */
class StringMatcher implements StringMangler, MetaYamlSchemaExtender {
	/** @var string Prefix for mangled message keys */
	protected $sPrefix = '';
	/** @var string[] Exact message keys */
	protected $aExact = [];
	/** @var int[] Patterns of type foo* */
	protected $aPrefix = [];
	/** @var string[] Patterns that contain wildcard anywhere else than in the end */
	protected $aRegex = [];

	public function __construct( string $prefix = '', array $patterns = [] ) {
		$this->sPrefix = $prefix;
		$this->init( $patterns );
	}

	/**
	 * Preprocesses the patterns.
	 *
	 * They are split into exact keys, prefix matches and pattern matches to
	 * speed up matching process.
	 *
	 * @param string[] $strings Key patterns.
	 */
	protected function init( array $strings ): void {
		foreach ( $strings as $string ) {
			$pos = strpos( $string, '*' );
			if ( $pos === false ) {
				$this->aExact[] = $string;
			} elseif ( $pos + 1 === strlen( $string ) ) {
				$prefix = substr( $string, 0, -1 );
				$this->aPrefix[$prefix] = strlen( $prefix );
			} else {
				$string = str_replace( '\\*', '.+', preg_quote( $string, '/' ) );
				$this->aRegex[] = "/^$string$/";
			}
		}
	}

	protected static function getValidKeyChars(): string {
		static $valid = null;
		if ( $valid === null ) {
			$valid = strtr(
				Title::legalChars(),
				[
					'=' => '', // equals sign, which is itself usef for escaping
					'&' => '', // ampersand, for entities
					'%' => '', // percent sign, which is used in URL encoding
				]
			);
		}

		return $valid;
	}

	/** @inheritDoc */
	public function setConf( array $conf ): void {
		$this->sPrefix = $conf['prefix'];
		$this->init( $conf['patterns'] );
	}

	/** @inheritDoc */
	public function matches( string $key ): bool {
		if ( in_array( $key, $this->aExact ) ) {
			return true;
		}

		foreach ( $this->aPrefix as $prefix => $len ) {
			if ( strncmp( $key, $prefix, $len ) === 0 ) {
				return true;
			}
		}

		foreach ( $this->aRegex as $regex ) {
			if ( preg_match( $regex, $key ) ) {
				return true;
			}
		}

		return false;
	}

	/** @inheritDoc */
	public function mangle( string $key ): string {
		if ( $this->matches( $key ) ) {
			$key = $this->sPrefix . $key;
		}

		$escaper = static function ( $match ) {
			$esc = '';
			foreach ( str_split( $match[0] ) as $c ) {
				$esc .= '=' . sprintf( '%02X', ord( $c ) );
			}
			return $esc;
		};

		// Apply a "quoted-printable"-like escaping
		$valid = self::getValidKeyChars();
		$key = preg_replace_callback( "/[^$valid]/", $escaper, $key );
		// Additional limitations in MediaWiki, see MediaWikiTitleCodec::splitTitleString
		$key = preg_replace_callback( '/(~~~|^[ _]|[ _]$|[ _]{2,}|^:)/', $escaper, $key );
		// TODO: length check + truncation
		// TODO: forbid path travels

		return $key;
	}

	/** @inheritDoc */
	public function mangleList( array $list ): array {
		return array_map( [ $this, 'mangle' ], $list );
	}

	/** @inheritDoc */
	public function mangleArray( array $array ): array {
		$out = [];
		foreach ( $array as $key => $value ) {
			$out[$this->mangle( (string)$key )] = $value;
		}

		return $out;
	}

	/** @inheritDoc */
	public function unmangle( string $key ): string {
		// Unescape the "quoted-printable"-like escaping,
		// which is applied in mangle
		$unescapedString = preg_replace_callback(
			'/=([A-F0-9]{2})/',
			static function ( $match ) {
				return chr( hexdec( $match[1] ) );
			},
			$key
		);

		if ( strncmp( $unescapedString, $this->sPrefix, strlen( $this->sPrefix ) ) === 0 ) {
			$unmangled = substr( $unescapedString, strlen( $this->sPrefix ) );

			// Check if this string should be mangled / un-mangled to begin with
			if ( $this->matches( $unmangled ) ) {
				return $unmangled;
			}
		}
		return $unescapedString;
	}

	/** @inheritDoc */
	public function unmangleList( array $list ): array {
		foreach ( $list as $index => $key ) {
			$list[$index] = $this->unmangle( $key );
		}

		return $list;
	}

	/** @inheritDoc */
	public function unmangleArray( array $array ): array {
		$out = [];
		foreach ( $array as $key => $value ) {
			$out[$this->unmangle( $key )] = $value;
		}

		return $out;
	}

	/** @inheritDoc */
	public static function getExtraSchema(): array {
		$schema = [
			'root' => [
				'_type' => 'array',
				'_children' => [
					'MANGLER' => [
						'_type' => 'array',
						'_children' => [
							'prefix' => [
								'_type' => 'text',
								'_not_empty' => true,
							],
							'patterns' => [
								'_type' => 'array',
								'_required' => true,
								'_ignore_extra_keys' => true,
								'_children' => [],
							],
						],
					],
				],
			],
		];

		return $schema;
	}
}
