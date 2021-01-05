<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Utilities\Json;

use FormatJson;
use InvalidArgumentException;
use JsonSerializable;
use Wikimedia\Assert\Assert;

/**
 * Helper class to serialize/unserialize things to/from JSON.
 * This class, and related classes can be removed once Translate needs to support only
 * MW >= 1.36. We can then use the JsonCodec class present in core.
 *
 * Differences between JsonCodec in the core:
 * - unserialize only supports strings
 * - does not detect non serializable data
 *
 * See Change-Id: I5433090ae8e2b3f2a4590cc404baf838025546ce
 * @license GPL-2.0-or-later
 * @since 2020.12
 */
class JsonCodec {
	public const TYPE_ANNOTATION = '_type_';

	public function unserialize( ?string $json, string $expectedClass = null ) {
		Assert::precondition(
			!$expectedClass || is_subclass_of( $expectedClass, JsonUnserializable::class ),
			'$expectedClass parameter must be subclass of JsonUnserializable, got ' . $expectedClass
		);

		$jsonStatus = FormatJson::parse( $json, FormatJson::FORCE_ASSOC );
		if ( !$jsonStatus->isGood() ) {
			// TODO: in PHP 7.3, we can use JsonException
			throw new InvalidArgumentException( "Bad JSON: {$jsonStatus}" );
		}
		$json = $jsonStatus->getValue();

		if ( !$this->canMakeNewFromValue( $json ) ) {
			if ( $expectedClass ) {
				throw new InvalidArgumentException( 'JSON did not have ' . self::TYPE_ANNOTATION );
			}
			return $json;
		}

		$class = $json[self::TYPE_ANNOTATION];
		if ( !class_exists( $class ) || !is_subclass_of( $class, JsonUnserializable::class ) ) {
			throw new InvalidArgumentException( "Invalid target class {$class}" );
		}

		$obj = $class::newFromJsonArray( $json );
		if ( $expectedClass && !$obj instanceof $expectedClass ) {
			$actualClass = get_class( $obj );
			throw new InvalidArgumentException( "Expected {$expectedClass}, got {$actualClass}" );
		}
		return $obj;
	}

	public function serialize( $value ): string {
		if ( $value instanceof JsonSerializable ) {
			$json = $value->jsonSerialize();
		} else {
			$json = FormatJson::encode( $value, false, FormatJson::ALL_OK );
		}

		if ( !$json ) {
			throw new InvalidArgumentException(
				'Failed to encode JSON. Error ' . json_last_error_msg()
			);
		}

		return $json;
	}

	/** Is it likely possible to make a new instance from $json serialization? */
	private function canMakeNewFromValue( $json ): bool {
		$classAnnotation = self::TYPE_ANNOTATION;
		if ( is_array( $json ) ) {
			return array_key_exists( $classAnnotation, $json );
		}
		if ( is_object( $json ) ) {
			return $json->$classAnnotation;
		}
		return false;
	}
}

class_alias( JsonCodec::class, '\MediaWiki\Extensions\Translate\JsonCodec' );
