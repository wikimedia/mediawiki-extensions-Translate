<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupConfiguration;

use AggregateMessageGroup;
use FileBasedMessageGroup;
use InvalidArgumentException;
use MediaWikiIntegrationTestCase;
use MessageGroupBase;

/**
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @license GPL-2.0-or-later
 * @covers \MediaWiki\Extension\Translate\MessageGroupConfiguration\MessageGroupFactory
 */
class MessageGroupFactoryTest extends MediaWikiIntegrationTestCase {
	private MessageGroupFactory $factory;

	protected function setUp(): void {
		parent::setUp();
		$this->factory = new MessageGroupFactory( new MessageGroupTypeRegistry() );
	}

	private function baseConf( array $basic ): array {
		return [ 'BASIC' => $basic + [ 'id' => 'test', 'namespace' => NS_MEDIAWIKI ] ];
	}

	public function testTypeFileProducesFileBasedMessageGroup(): void {
		$group = $this->factory->createGroup( $this->baseConf( [ 'type' => 'file' ] ) );
		$this->assertInstanceOf( FileBasedMessageGroup::class, $group );
	}

	public function testTypeAggregateProducesAggregateMessageGroup(): void {
		$group = $this->factory->createGroup( $this->baseConf( [ 'type' => 'aggregate' ] ) );
		$this->assertInstanceOf( AggregateMessageGroup::class, $group );
	}

	public function testLegacyBareClassProducesCorrectImplementation(): void {
		$group = $this->factory->createGroup( $this->baseConf( [ 'class' => 'FileBasedMessageGroup' ] ) );
		$this->assertInstanceOf( FileBasedMessageGroup::class, $group );
	}

	public function testFqcnClassProducesCorrectImplementation(): void {
		$group = $this->factory->createGroup( $this->baseConf( [ 'class' => FileBasedMessageGroup::class ] ) );
		$this->assertInstanceOf( FileBasedMessageGroup::class, $group );
	}

	public function testConfigurationIsInitialised(): void {
		$conf = $this->baseConf( [ 'type' => 'file', 'label' => 'Test' ] );
		$group = $this->factory->createGroup( $conf );
		$this->assertSame( $conf, $group->getConfiguration() );
		$this->assertSame( NS_MEDIAWIKI, $group->getNamespace() );
	}

	public function testBothSelectorsThrows(): void {
		$this->expectException( InvalidArgumentException::class );
		$this->factory->createGroup(
			$this->baseConf( [ 'type' => 'file', 'class' => FileBasedMessageGroup::class ] )
		);
	}

	public function testNoSelectorThrows(): void {
		$this->expectException( InvalidArgumentException::class );
		$this->factory->createGroup( $this->baseConf( [] ) );
	}

	public function testUnknownTypeThrows(): void {
		$this->expectException( InvalidArgumentException::class );
		$this->factory->createGroup( $this->baseConf( [ 'type' => 'no-such-type' ] ) );
	}

	public function testNonExistentClassThrows(): void {
		$this->expectException( InvalidArgumentException::class );
		$this->factory->createGroup( $this->baseConf( [ 'class' => 'NoSuchClass' ] ) );
	}

	public function testNonMessageGroupBaseClassThrows(): void {
		$this->expectException( InvalidArgumentException::class );
		$this->factory->createGroup( $this->baseConf( [ 'class' => \stdClass::class ] ) );
	}

	// --- getImplementationClass() tests ---

	public function testResolveClassReturnsClassForType(): void {
		$this->assertSame(
			FileBasedMessageGroup::class,
			$this->factory->resolveClass( $this->baseConf( [ 'type' => 'file' ] ) )
		);
	}

	// --- MessageGroupBase::factory() BC tests ---

	public function testStaticFactoryWithFqcn(): void {
		$conf = $this->baseConf( [ 'class' => FileBasedMessageGroup::class ] );
		$group = MessageGroupBase::factory( $conf );
		$this->assertInstanceOf( FileBasedMessageGroup::class, $group );
	}

	public function testStaticFactoryWithBareClass(): void {
		$conf = $this->baseConf( [ 'class' => 'FileBasedMessageGroup' ] );
		$group = MessageGroupBase::factory( $conf );
		$this->assertInstanceOf( FileBasedMessageGroup::class, $group );
	}

	public function testStaticFactoryProducesSameResultAsCanonicalFactory(): void {
		$conf = $this->baseConf( [ 'class' => FileBasedMessageGroup::class ] );
		$viaStatic = MessageGroupBase::factory( $conf );
		$viaFactory = $this->factory->createGroup( $conf );

		$this->assertSame( get_class( $viaStatic ), get_class( $viaFactory ) );
		$this->assertSame( $viaStatic->getConfiguration(), $viaFactory->getConfiguration() );
	}
}
