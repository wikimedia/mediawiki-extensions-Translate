<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Utilities\Json;

use InvalidArgumentException;
use JsonSerializable;
use MediaWikiUnitTestCase;
use MockJsonUnserializableSubClass;
use MockJsonUnserializableSuperClass;
use stdClass;
use Wikimedia\Assert\PreconditionException;

/**
 * Remove once we need to support only MW >= 1.36
 * See Change-Id: I5433090ae8e2b3f2a4590cc404baf838025546ce
 *
 * @covers \MediaWiki\Extension\Translate\Utilities\Json\JsonCodec
 * @covers \MediaWiki\Extension\Translate\Utilities\Json\JsonUnserializableTrait
 */
class JsonCodecTest extends MediaWikiUnitTestCase {

	private function getCodec(): JsonCodec {
		return new JsonCodec();
	}

	public function provideSimpleTypes() {
		yield 'Integer' => [ 1, json_encode( 1 ) ];
		yield 'Boolean' => [ true, json_encode( true ) ];
		yield 'Null' => [ null, json_encode( null ) ];
		yield 'Array' => [ [ 1, 2, 3 ], json_encode( [ 1, 2, 3 ] ) ];
		yield 'Assoc array' => [ [ 'a' => 'b' ], json_encode( [ 'a' => 'b' ] ) ];
		$object = new stdClass();
		$object->c = 'd';
		yield 'Object' => [ (array)$object, json_encode( $object ) ];
	}

	/**
	 * @dataProvider provideSimpleTypes
	 * @param mixed $value
	 */
	public function testSimpleTypesUnserialize( $value, string $serialization ) {
		$this->assertSame( $value, $this->getCodec()->unserialize( $serialization ) );
	}

	public function testInvalidJsonDataForClassExpectation() {
		$this->expectException( InvalidArgumentException::class );
		$this->getCodec()->unserialize( 'bad string', MockJsonUnserializableSuperClass::class );
	}

	public function testExpectedClassMustBeUnserializable() {
		$this->expectException( PreconditionException::class );
		$this->getCodec()->unserialize( '{}', self::class );
	}

	public function testUnexpectedClassUnserialized() {
		$this->expectException( InvalidArgumentException::class );
		$superClassInstance = new MockJsonUnserializableSuperClass( 'Godzilla' );
		$this->getCodec()->unserialize(
			$superClassInstance->jsonSerialize(),
			MockJsonUnserializableSubClass::class
		);
	}

	public function testExpectedClassUnserialized() {
		$subClassInstance = new MockJsonUnserializableSubClass( 'Godzilla', 'But we are ready!' );
		$this->assertNotNull( $this->getCodec()->unserialize(
			$subClassInstance->jsonSerialize(),
			MockJsonUnserializableSuperClass::class
		) );
		$this->assertNotNull( $this->getCodec()->unserialize(
			$subClassInstance->jsonSerialize(),
			MockJsonUnserializableSubClass::class
		) );
	}

	public function testRoundTripSuperClass() {
		$superClassInstance = new MockJsonUnserializableSuperClass( 'Super Value' );
		$json = $superClassInstance->jsonSerialize();
		$superClassUnserialized = $this->getCodec()->unserialize( $json );
		$this->assertInstanceOf( MockJsonUnserializableSuperClass::class, $superClassInstance );
		$this->assertSame( $superClassInstance->getSuperClassField(), $superClassUnserialized->getSuperClassField() );
	}

	public function testRoundTripSubClass() {
		$subClassInstance = new MockJsonUnserializableSubClass( 'Super Value', 'Sub Value' );
		$json = $subClassInstance->jsonSerialize();
		$superClassUnserialized = $this->getCodec()->unserialize( $json );
		$this->assertInstanceOf( MockJsonUnserializableSubClass::class, $subClassInstance );
		$this->assertSame( $subClassInstance->getSuperClassField(), $superClassUnserialized->getSuperClassField() );
		$this->assertSame( $subClassInstance->getSubClassField(), $superClassUnserialized->getSubClassField() );
	}

	public function provideSerializeThrowsOnFailure() {
		yield 'crash in serialization, gzipped data' => [
			"\x1f\x8b\x08\x00\x00\x00\x00\x00\x00\x03\xcb\x48\xcd\xc9\xc9\x57\x28\xcf\x2f'
			. '\xca\x49\x01\x00\x85\x11\x4a\x0d\x0b\x00\x00\x00"
		];
	}

	/**
	 * @dataProvider provideSerializeThrowsOnFailure
	 * @covers \MediaWiki\Extension\Translate\Utilities\Json\JsonCodec::serialize
	 * @param mixed $value
	 */
	public function testSerializeThrowsOnFailure( $value ) {
		$this->expectException( InvalidArgumentException::class );
		$this->getCodec()->serialize( $value );
	}

	public function provideSerializeSuccess() {
		$serializableInstance = new class() implements JsonSerializable {
			public function jsonSerialize() {
				return json_encode( [ 'c' => 'd' ] );
			}
		};
		yield 'array' => [ [ 'a' => 'b' ], '{"a":"b"}' ];
		yield 'JsonSerializable' => [ $serializableInstance, '{"c":"d"}' ];
	}

	/**
	 * @dataProvider provideSerializeSuccess
	 * @covers \MediaWiki\Extension\Translate\Utilities\Json\JsonCodec::serialize
	 * @param mixed $value
	 */
	public function testSerializeSuccess( $value, string $expected ) {
		$this->assertSame( $expected, $this->getCodec()->serialize( $value ) );
	}
}
