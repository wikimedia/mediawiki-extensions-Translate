<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupConfiguration;

use AggregateMessageGroup;
use FileBasedMessageGroup;
use InvalidArgumentException;
use MediaWikiExtensionMessageGroup;
use MediaWikiUnitTestCase;
use MessagePrefixMessageGroup;

/**
 * @author Siebrand Mazeland
 * @license GPL-2.0-or-later
 * @covers \MediaWiki\Extension\Translate\MessageGroupConfiguration\MessageGroupTypeRegistry
 */
class MessageGroupTypeRegistryTest extends MediaWikiUnitTestCase {
	private MessageGroupTypeRegistry $registry;

	protected function setUp(): void {
		parent::setUp();
		$this->registry = new MessageGroupTypeRegistry();
	}

	/** @dataProvider provideBuiltInTypes */
	public function testGetClassReturnsExpectedImplementation(
		string $type,
		string $expectedClass
	): void {
		$this->assertSame( $expectedClass, $this->registry->getClass( $type ) );
	}

	/** @dataProvider provideBuiltInTypes */
	public function testGetSpecContainsExpectedClass(
		string $type,
		string $expectedClass
	): void {
		$spec = $this->registry->getSpec( $type );
		$this->assertArrayHasKey( 'class', $spec );
		$this->assertSame( $expectedClass, $spec['class'] );
	}

	/** @dataProvider provideBuiltInTypes */
	public function testGetSpecIsValidConstructionSpec( string $type ): void {
		$spec = $this->registry->getSpec( $type );
		$this->assertIsArray( $spec );
		$this->assertArrayHasKey( 'class', $spec );
		$this->assertIsString( $spec['class'] );
		$this->assertNotEmpty( $spec['class'] );
	}

	public static function provideBuiltInTypes(): array {
		return [
			'file' => [ 'file', FileBasedMessageGroup::class ],
			'aggregate' => [ 'aggregate', AggregateMessageGroup::class ],
			'mediawiki-extension' => [ 'mediawiki-extension', MediaWikiExtensionMessageGroup::class ],
			'message-prefix' => [ 'message-prefix', MessagePrefixMessageGroup::class ],
		];
	}

	public function testGetClassThrowsForUnknownType(): void {
		$this->expectException( InvalidArgumentException::class );
		$this->registry->getClass( 'unknown-type' );
	}

	public function testGetSpecThrowsForUnknownType(): void {
		$this->expectException( InvalidArgumentException::class );
		$this->registry->getSpec( 'unknown-type' );
	}
}
