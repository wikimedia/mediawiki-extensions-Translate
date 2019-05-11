<?php

class FileBasedMessageGroupLoaderTest extends MediaWikiTestCase {
	/**
	 * @var FileBasedMessageGroupLoader
	 */
	protected $mgFileLoader;

	protected function setUp() {
		parent::setUp();

		$conf = [
			__DIR__ . '/../data/MessageLoaderGroups.yaml',
		];

		$this->setMwGlobals( [
			'wgTranslateGroupFiles' => $conf,
			'wgTranslateTranslationServices' => [],
			'wgTranslateMessageNamespaces' => [ NS_MEDIAWIKI ],
		] );

		$this->setTemporaryHook( 'TranslateInitGroupLoaders',
			'FileBasedMessageGroupLoader::registerLoader' );

		$this->mgFileLoader = new FileBasedMessageGroupLoader();
		$this->mgFileLoader->setCache( new MessageGroupWANCache(
			new WANObjectCache( [ 'cache' => wfGetCache( 'hash' ) ] )
		) );
	}

	public function testGetGroups() {
		$fileBasedGroups = $this->mgFileLoader->getGroups();
		$this->assertCount( 1, $fileBasedGroups, 'the configured file based ' .
			'message group is returned' );
		$this->assertEquals( 'message-loader-group', current( $fileBasedGroups )->getId(),
			' the correct configured group is returned.' );
	}

	public function testRecache() {
		$prevGroupCount = count( $this->mgFileLoader->getGroups() );

		$this->setMwGlobals( [
			'wgTranslateGroupFiles' => [],
		] );
		$countBeforeRecache = count( $this->mgFileLoader->getGroups() );
		$this->assertEquals( $prevGroupCount, $countBeforeRecache,
			'removed groups still remain until recache is called' );

		$this->mgFileLoader->recache();

		$updatedCount = count( $this->mgFileLoader->getGroups() );
		$this->assertEquals( ( $prevGroupCount - 1 ), $updatedCount,
			'removed groups disappear after recache is called' );
	}
}
