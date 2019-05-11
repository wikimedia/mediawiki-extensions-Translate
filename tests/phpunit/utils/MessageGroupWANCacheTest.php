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
		$this->setExpectedException(
			\InvalidArgumentException::class,
			'Invalid cache key'
		);

		$this->mgCache->configure( [
			'regenerator' => function () {
				return 'hello';
			}
		] );
	}

	public function testCacheRegeneratorConfig() {
		$this->setExpectedException(
			\InvalidArgumentException::class,
			'Invalid regenerator'
		);

		$this->mgCache->configure( [
			'key' => 'test',
			'regenerator' => 'hello-world'
		] );
	}

	public function testNoConfigureCall() {
		$this->setExpectedException(
			\InvalidArgumentException::class,
			'configure function'
		);

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
		$this->setExpectedException(
			\InvalidArgumentException::class,
			'touchedCallback is not callable'
		);

		$this->mgCache->configure( [
			'key' => 'mg-wan-test',
			'regenerator' => function () {
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
