<?php
declare( strict_types = 1 );

use MediaWiki\Extension\Translate\Utilities\Json\JsonUnserializable;
use MediaWiki\Extension\Translate\Utilities\Json\JsonUnserializableTrait;

/**
 * Testing class for JsonCodec unit tests.
 * Remove once we need to support only MW >= 1.36
 * See Change-Id: I5433090ae8e2b3f2a4590cc404baf838025546ce
 *
 * @license GPL-2.0-or-later
 */
class MockJsonUnserializableSuperClass implements JsonUnserializable, JsonSerializable {
	use JsonUnserializableTrait;

	/** @var string */
	private $superClassField;

	public function __construct( string $superClassFieldValue ) {
		$this->superClassField = $superClassFieldValue;
	}

	public function getSuperClassField(): string {
		return $this->superClassField;
	}

	/**
	 * @param string[] $json
	 * @return self
	 */
	public static function newFromJsonArray( array $json ) {
		return new self( $json['super_class_field'] );
	}

	/** @return string[] */
	protected function toJsonArray(): array {
		return [
			'super_class_field' => $this->getSuperClassField()
		];
	}
}
