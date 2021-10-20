<?php

/** @group Database */
class TranslatablePageMessageGroupStoreTest extends MediaWikiIntegrationTestCase {
	use TranslatablePageTestTrait;

	/** @var TranslatablePageMessageGroupStore */
	protected $mgTranslateStore;

	protected function setUp(): void {
		parent::setUp();

		$this->setMwGlobals( [
			'wgEnablePageTranslation' => true
		] );

		$this->mgTranslateStore = new TranslatablePageMessageGroupStore(
			TranslateUtils::getSafeReadDB(),
			new MessageGroupWANCache(
				new WANObjectCache( [ 'cache' => new HashBagOStuff() ] )
			)
		);
	}

	public function testRecache() {
		$prevGroupCount = count( $this->mgTranslateStore->getGroups() );

		$this->createMarkedTranslatablePage( 'Upyog', 'Upyog', $this->getTestSysop()->getUser() );

		$countBeforeRecache = count( $this->mgTranslateStore->getGroups() );
		$this->assertEquals( $prevGroupCount, $countBeforeRecache,
			'new groups do not appear unless recache is called' );

		$this->mgTranslateStore->recache();

		$updatedCount = count( $this->mgTranslateStore->getGroups() );
		$this->assertEquals( ( $prevGroupCount + 1 ), $updatedCount,
			'new groups appear after recache is called' );
	}

	public function testGlobalFlag() {
		$this->createMarkedTranslatablePage( 'Upyon - 22', 'Upyog', $this->getTestSysop()->getUser() );
		$this->mgTranslateStore->recache();
		$prevCount = count( $this->mgTranslateStore->getGroups() );
		$this->assertGreaterThanOrEqual( 1, $prevCount, 'there is atleast 1 ' .
			'translatable page returned' );

		$this->setMwGlobals( [
			'wgEnablePageTranslation' => false
		] );

		$this->mgTranslateStore->recache();
		$this->assertCount( 0, $this->mgTranslateStore->getGroups(), 'no translatable pages returned' );
	}

	public function testCacheCalls() {
		$dummy = new DependencyWrapper( [], [] );

		/** @var MessageGroupWANCache $mockMgWANCache */
		$mockMgWANCache = $this->getMockBuilder( MessageGroupWANCache::class )
			->disableOriginalConstructor()
			->getMock();

		$translateStore = new TranslatablePageMessageGroupStore(
			TranslateUtils::getSafeReadDB(),
			$mockMgWANCache
		);

		$mockMgWANCache->expects( $this->once() )
			->method( 'getValue' )
			->with( 'recache' )
			->willReturn( $dummy );

		// should trigger a get call on cache
		$translateStore->recache();

		// should return the cached groups from process cache
		$this->assertEquals( [], $translateStore->getGroups() );

		$mockMgWANCache->expects( $this->once() )
			->method( 'delete' );

		// should trigger the delete method on cache
		$translateStore->clearCache();
	}
}
