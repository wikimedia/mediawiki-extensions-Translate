<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupConfiguration;

use MediaWiki\Config\ServiceOptions;
use MediaWikiIntegrationTestCase;
use Wikimedia\Rdbms\IReadableDatabase;

/** @covers \MediaWiki\Extension\Translate\MessageGroupConfiguration\FileBasedMessageGroupFactory */
class FileBasedMessageGroupFactoryTest extends MediaWikiIntegrationTestCase {
	private FileBasedMessageGroupFactory $factory;

	protected function setUp(): void {
		$config = [
			'TranslateGroupFiles' => [
				__DIR__ . '/../data/MessageLoaderGroups.yaml',
			]
		];

		$this->factory = new FileBasedMessageGroupFactory(
			new MessageGroupConfigurationParser(),
			'en',
			new ServiceOptions( FileBasedMessageGroupFactory::SERVICE_OPTIONS, $config )
		);
	}

	public function testGetGroups() {
		$groupData = $this->factory->getData( $this->getMockBuilder( IReadableDatabase::class )->getMock() );
		$fileBasedGroups = $this->factory->createGroups( $groupData );
		$this->assertCount(
			1,
			$fileBasedGroups,
			'the configured file based ' .
			'message group is returned'
		);
		$this->assertEquals(
			'message-loader-group',
			current( $fileBasedGroups )->getId(),
			' the correct configured group is returned.'
		);
	}
}
