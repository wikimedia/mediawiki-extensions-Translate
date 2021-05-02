<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Cache;

use DateTime;
use InvalidArgumentException;

/**
 * Represents a single result from the persistent cache
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 * @since 2020.12
 */
class PersistentCacheEntry {
	private const MAX_KEY_LENGTH = 255;
	private const MAX_TAG_LENGTH = 255;

	/** @var string */
	private $key;
	/** @var mixed */
	private $value;
	/** @var int|null */
	private $exptime;
	/** @var string|null */
	private $tag;

	public function __construct(
		string $key,
		$value = null,
		int $exptime = null,
		string $tag = null
	) {
		if ( strlen( $key ) > self::MAX_KEY_LENGTH ) {
			throw new InvalidArgumentException(
				"The length of key: $key is greater than allowed " . self::MAX_KEY_LENGTH
			);
		}

		if ( $tag && strlen( $tag ) > self::MAX_TAG_LENGTH ) {
			throw new InvalidArgumentException(
				"The length of tag: $tag is greater than allowed " . self::MAX_TAG_LENGTH
			);
		}

		$this->key = $key;
		$this->value = $value;
		$this->exptime = $exptime;
		$this->tag = $tag;
	}

	public function key(): string {
		return $this->key;
	}

	/** @return mixed */
	public function value() {
		return $this->value;
	}

	public function exptime(): ?int {
		return $this->exptime;
	}

	public function tag(): ?string {
		return $this->tag;
	}

	public function hasExpired(): bool {
		if ( $this->exptime ) {
			return $this->exptime < ( new DateTime() )->getTimestamp();
		}

		return false;
	}
}
