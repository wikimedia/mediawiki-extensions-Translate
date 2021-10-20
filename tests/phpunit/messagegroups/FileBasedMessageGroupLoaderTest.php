<?php

class FileBasedMessageGroupLoaderTest extends MediaWikiIntegrationTestCase {
	/** @var FileBasedMessageGroupLoader */
	protected $mgFileLoader;

	protected function setUp(): void {
		parent::setUp();

		$conf = [
			__DIR__ . '/../data/MessageLoaderGroups.yaml',
		];

		$this->setMwGlobals( [
			'wgTranslateGroupFiles' => $conf,
		] );

		$this->setTemporaryHook( 'TranslateInitGroupLoaders',
			'FileBasedMessageGroupLoader::registerLoader' );

		$this->mgFileLoader = new FileBasedMessageGroupLoader(
			new MessageGroupWANCache(
				new WANObjectCache( [ 'cache' => new HashBagOStuff() ] )
			)
		);
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

	public function testCacheCalls() {
		$dummy = new DependencyWrapper( [
			'groups' => [],
			'autoload' => []
		], [] );

		/** @var MessageGroupWANCache $mockMgWANCache */
		$mockMgWANCache = $this->getMockBuilder( MessageGroupWANCache::class )
			->disableOriginalConstructor()
			->getMock();

		$fileBasedLoader = new FileBasedMessageGroupLoader( $mockMgWANCache );

		$mockMgWANCache->expects( $this->once() )
			->method( 'getValue' )
			->with( 'recache' )
			->willReturn( $dummy );

		// should trigger a get call on cache
		$fileBasedLoader->recache();

		// should return the cached groups from process cache
		$this->assertEquals( [], $fileBasedLoader->getGroups() );

		$mockMgWANCache->expects( $this->once() )
			->method( 'delete' );

		// should trigger the delete method on cache
		$fileBasedLoader->clearCache();
	}
}
