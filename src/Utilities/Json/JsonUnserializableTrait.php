<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Utilities\Json;

use FormatJson;

/**
 * Can be used by classes that want to serialize / deserialize
 * Remove once we need to support only MW >= 1.36
 * See Change-Id: I5433090ae8e2b3f2a4590cc404baf838025546ce
 *
 * @license GPL-2.0-or-later
 * @since 2020.12
 */
trait JsonUnserializableTrait {
	public function jsonSerialize() {
		return $this->annotateJsonForDeserialization(
			$this->toJsonArray()
		);
	}

	/** Annotate the $json array with class metadata. */
	private function annotateJsonForDeserialization( array $json ): string {
		$json[JsonCodec::TYPE_ANNOTATION] = get_class( $this );
		return FormatJson::encode( $json, false, FormatJson::ALL_OK );
	}

	/**
	 * Prepare this object for JSON serialization.
	 * The returned array will be passed to self::newFromJsonArray
	 * upon JSON deserialization.
	 */
	abstract protected function toJsonArray(): array;
}

class_alias( JsonUnserializableTrait::class, '\MediaWiki\Extensions\Translate\JsonUnserializableTrait' );
