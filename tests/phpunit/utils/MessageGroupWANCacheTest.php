<?php
class MessageGroupWANCacheTest extends MediaWikiTestCase {
	protected $mgCache;

	protected function setUp() {
		parent::setUp();
		$this->mgCache = new MessageGroupWANCache(
			new WANObjectCache( [ 'cache' => wfGetCache( 'hash' ) ] )
		);
	}

	public function testCacheKeyConfiguration() {
		$this->expectException( \InvalidArgumentException::class );
		$this->expectExceptionMessage( 'Invalid cache key' );

		$this->mgCache->configure( [
			'regenerator' => function () {
				return 'hello';
			}
		] );
	}

	public function testCacheRegeneratorConfig() {
		$this->expectException( \InvalidArgumentException::class );
		$this->expectExceptionMessage( 'Invalid regenerator' );

		$this->mgCache->configure( [
			'key' => 'test',
			'regenerator' => 'hello-world'
		] );
	}

	public function testNoConfigureCall() {
		$this->expectException( \InvalidArgumentException::class );
		$this->expectExceptionMessage( 'called the configure function' );

		$this->mgCache->setValue( [ 'abc' ] );
	}

	public function testDefaultConfig() {
		$cacheData = [ 'dummy', 'data' ];
		$this->mgCache->configure( [
			'key' => 'mg-wan-test',
			'regenerator' => function () use ( $cacheData ) {
				return $cacheData;
			}
		] );

		$mgCacheData = $this->mgCache->getValue();
		$this->assertEquals( $cacheData, $mgCacheData, 'correctly returns the data ' .
			'returned in regenerator function.' );
	}

	public function testTouchCallbackConfig() {
		$this->expectException( \InvalidArgumentException::class );
		$this->expectExceptionMessage( 'touchedCallback is not callable' );

		$this->mgCache->configure( [
			'key' => 'mg-wan-test',
			'regenerator' => function () {
				return 'hello';
			},
			'touchedCallback' => 'blah'
		] );
	}

	public function testTouchCallbackIsCalled() {
		$dummyData = [ 'dummy' ];

		$mockMgLoader = $this->createMock( MockDbCacheMessageGroupLoader::class );
		$this->mgCache->configure( [
			'key' => 'mg-wan-test',
			'regenerator' => [ $mockMgLoader, 'getGroups' ],
			'touchedCallback' => [ $mockMgLoader, 'isExpired' ]
		] );

		$mockMgLoader->expects( $this->once() )
			->method( 'getGroups' )
			->willReturn( $dummyData );

		$mockMgLoader->expects( $this->once() )
			->method( 'isExpired' )
			->with( $dummyData )
			->willReturn( false );

		// touchedCallback is not called the first time,
		// since the value was just obtained
		$this->mgCache->getValue();
		$this->mgCache->getValue();
	}
}
