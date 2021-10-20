<?php

class MessageGroupWANCacheTest extends MediaWikiIntegrationTestCase {
	protected $mgCache;

	protected function setUp(): void {
		parent::setUp();
		$this->mgCache = new MessageGroupWANCache(
			new WANObjectCache( [ 'cache' => new HashBagOStuff() ] )
		);
	}

	public function testCacheKeyConfiguration() {
		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessage( 'Invalid cache key' );

		$this->mgCache->configure( [
			'regenerator' => static function () {
				return 'hello';
			}
		] );
	}

	public function testCacheRegeneratorConfig() {
		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessage( 'Invalid regenerator' );

		$this->mgCache->configure( [
			'key' => 'test',
			'regenerator' => 'hello-world'
		] );
	}

	public function testNoConfigureCall() {
		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessage( 'configure function' );

		$this->mgCache->setValue( [ 'abc' ] );
	}

	public function testDefaultConfig() {
		$cacheData = [ 'dummy', 'data' ];
		$this->mgCache->configure( [
			'key' => 'mg-wan-test',
			'regenerator' => static function () use ( $cacheData ) {
				return $cacheData;
			}
		] );

		$mgCacheData = $this->mgCache->getValue();
		$this->assertEquals( $cacheData, $mgCacheData, 'correctly returns the data ' .
			'returned in regenerator function.' );
	}

	public function testTouchCallbackConfig() {
		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessage( 'touchedCallback is not callable' );

		$this->mgCache->configure( [
			'key' => 'mg-wan-test',
			'regenerator' => static function () {
				return 'hello';
			},
			'touchedCallback' => 'blah'
		] );
	}

	public function testTouchCallbackIsCalled() {
		$wrapper = new DependencyWrapper( [ 'dummy' ] );

		$mockMgLoader = $this->createMock( MockCacheMessageGroupLoader::class );
		$this->mgCache->configure( [
			'key' => 'mg-wan-test',
			'regenerator' => [ $mockMgLoader, 'getGroups' ],
			'touchedCallback' => [ $mockMgLoader, 'isExpired' ]
		] );

		$mockMgLoader->expects( $this->once() )
			->method( 'getGroups' )
			->willReturn( $wrapper );

		$mockMgLoader->expects( $this->once() )
			->method( 'isExpired' )
			->with( $wrapper )
			->willReturn( false );

		// touchedCallback is not called the first time,
		// since the value was just obtained
		$this->mgCache->getValue();
		$this->mgCache->getValue();
	}
}
