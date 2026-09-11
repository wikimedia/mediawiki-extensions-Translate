<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupConfiguration;

use MediaWikiIntegrationTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @license GPL-2.0-or-later
 * @covers \MediaWiki\Extension\Translate\MessageGroupConfiguration\PremadeMediaWikiExtensionGroups
 */
class PremadeMediaWikiExtensionGroupsTest extends MediaWikiIntegrationTestCase {
	private string $definitionFile;

	protected function setUp(): void {
		parent::setUp();
		$this->definitionFile = __DIR__ . '/../data/mediawiki-extensions.txt';
	}

	public function testServiceFactoryIsUsed(): void {
		$groups = new PremadeMediaWikiExtensionGroups(
			$this->definitionFile,
			'%GROUPROOT%/mediawiki-extensions/extensions'
		);

		/** @var MessageGroupFactory&MockObject $factory */
		$factory = $this->createMock( MessageGroupFactory::class );
		$factory->expects( $this->atLeastOnce() )
			->method( 'createGroup' )
			->willReturnCallback( static function ( array $conf ) {
				return \MessageGroupBase::newFromConf( $conf['BASIC']['class'], $conf );
			} );

		$this->setService( 'Translate:MessageGroupFactory', $factory );

		$list = $deps = [];
		$groups->register( $list, $deps );

		$this->assertNotEmpty( $list );
	}
}
