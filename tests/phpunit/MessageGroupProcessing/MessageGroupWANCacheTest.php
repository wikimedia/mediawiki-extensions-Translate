<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use DependencyWrapper;
use HashBagOStuff;
use InvalidArgumentException;
use MediaWikiIntegrationTestCase;
use MockCacheMessageGroupLoader;
use WANObjectCache;

/** @covers \MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroupWANCache */
class MessageGroupWANCacheTest extends MediaWikiIntegrationTestCase {
	private MessageGroupWANCache $messageGroupWANCache;

	protected function setUp(): void {
		parent::setUp();
		$this->messageGroupWANCache = new MessageGroupWANCache(
			new WANObjectCache( [ 'cache' => new HashBagOStuff() ] )
		);
	}

	public function testCacheKeyConfiguration(): void {
		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessage( '$config[\'key\'] not set' );

		$this->messageGroupWANCache->configure( [
			'regenerator' => static function () {
				return 'hello';
			}
		] );
	}

	public function testCacheRegeneratorConfig(): void {
		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessage( '$config[\'regenerator\'] not set' );

		$this->messageGroupWANCache->configure( [
			'key' => 'test',
		] );
	}

	public function testNoConfigureCall(): void {
		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessage( 'configure function' );

		$this->messageGroupWANCache->setValue( [ 'abc' ] );
	}

	public function testDefaultConfig(): void {
		$cacheData = [ 'dummy', 'data' ];
		$this->messageGroupWANCache->configure( [
			'key' => 'mg-wan-test',
			'regenerator' => static function () use ( $cacheData ) {
				return $cacheData;
			}
		] );

		$mgCacheData = $this->messageGroupWANCache->getValue();
		$this->assertEquals( $cacheData, $mgCacheData, 'correctly returns the data ' .
			'returned in regenerator function.' );
	}

	public function testTouchCallbackConfig(): void {
		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessage( '$config[\'touchedCallback\'] is not callable' );

		$this->messageGroupWANCache->configure( [
			'key' => 'mg-wan-test',
			'regenerator' => static function () {
				return 'hello';
			},
			'touchedCallback' => 'blah'
		] );
	}

	public function testTouchCallbackIsCalled(): void {
		$wrapper = new DependencyWrapper( [ 'dummy' ] );

		$mockMgLoader = $this->createMock( MockCacheMessageGroupLoader::class );
		$this->messageGroupWANCache->configure( [
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
		$this->messageGroupWANCache->getValue();
		$this->messageGroupWANCache->getValue();
	}
}
