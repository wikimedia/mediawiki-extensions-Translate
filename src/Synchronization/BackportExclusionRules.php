<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Synchronization;

use InvalidArgumentException;
use Wikimedia\StringUtils\StringUtils;

/**
 * Rules for translations that must not be backported. See $wgTranslateBackportExclusionRules.
 *
 * @since 2026.10
 * @license GPL-2.0-or-later
 */
class BackportExclusionRules {
	private const LIST_CRITERIA = [ 'branches', 'languages' ];
	private const CRITERIA = [ 'branches', 'languages', 'content' ];

	/** @var array<string,array> */
	private array $rules;

	/** @throws InvalidArgumentException if the rules are not valid */
	public function __construct( array $rules ) {
		foreach ( $rules as $name => $rule ) {
			$this->validateRule( $name, $rule );
		}
		$this->rules = $rules;
	}

	public function hasBranchRules(): bool {
		foreach ( $this->rules as $rule ) {
			if ( isset( $rule['branches'] ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Returns the content patterns of the rules that apply to the branch and language, keyed by rule
	 * name. A null pattern matches every translation.
	 *
	 * @return array<string,?string>
	 */
	public function getContentPatterns( ?string $branch, string $language ): array {
		$patterns = [];
		foreach ( $this->rules as $name => $rule ) {
			if ( isset( $rule['branches'] ) && !in_array( $branch, $rule['branches'], true ) ) {
				continue;
			}
			if ( isset( $rule['languages'] ) && !in_array( $language, $rule['languages'], true ) ) {
				continue;
			}
			$patterns[$name] = $rule['content'] ?? null;
		}
		return $patterns;
	}

	/**
	 * Returns the name of the first rule whose pattern matches the translation, or null.
	 * A pattern that fails to run counts as a match, so the translation is not backported.
	 *
	 * @param array<string,?string> $contentPatterns From getContentPatterns()
	 */
	public static function getMatchingRule( array $contentPatterns, string $translation ): ?string {
		foreach ( $contentPatterns as $name => $pattern ) {
			if ( $pattern === null || preg_match( $pattern, $translation ) !== 0 ) {
				return $name;
			}
		}
		return null;
	}

	private function validateRule( int|string $name, mixed $rule ): void {
		if ( !is_string( $name ) ) {
			throw new InvalidArgumentException( "Backport exclusion rule #$name must have a name as its array key" );
		}
		if ( !is_array( $rule ) ) {
			throw new InvalidArgumentException( "Backport exclusion rule '$name' must be an array" );
		}

		$unknownKeys = array_diff( array_keys( $rule ), self::CRITERIA );
		if ( $unknownKeys ) {
			throw new InvalidArgumentException(
				"Backport exclusion rule '$name' has unknown keys: " . implode( ', ', $unknownKeys )
			);
		}

		foreach ( self::LIST_CRITERIA as $key ) {
			if ( !array_key_exists( $key, $rule ) ) {
				continue;
			}
			$list = $rule[$key];
			if ( !is_array( $list ) || $list === [] || !array_is_list( $list ) ||
				array_filter( $list, 'is_string' ) !== $list
			) {
				throw new InvalidArgumentException(
					"Backport exclusion rule '$name': '$key' must be a non-empty list of strings"
				);
			}
		}

		if ( array_key_exists( 'content', $rule ) &&
			( !is_string( $rule['content'] ) || !StringUtils::isValidPCRERegex( $rule['content'] ) )
		) {
			throw new InvalidArgumentException(
				"Backport exclusion rule '$name': 'content' must be a valid regular expression"
			);
		}
	}
}
