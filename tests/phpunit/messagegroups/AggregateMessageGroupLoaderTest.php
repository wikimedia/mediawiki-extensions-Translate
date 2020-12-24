<?php

/** @group Database */
class AggregateMessageGroupLoaderTest extends PHPUnit\Framework\TestCase {
	public function testCacheCalls() {
		/** @var MessageGroupWANCache $mockMgWANCache */
		$mockMgWANCache = $this->getMockBuilder( MessageGroupWANCache::class )
			->disableOriginalConstructor()
			->getMock();

		$aggregateLoader = new AggregateMessageGroupLoader(
			TranslateUtils::getSafeReadDB(),
			$mockMgWANCache
		);

		$mockMgWANCache->expects( $this->once() )
			->method( 'getValue' )
			->with( 'recache' )
			->willReturn( [] );

		// should trigger a get call on cache
		$aggregateLoader->recache();

		// should return the cached groups from process cache
		$this->assertEquals( [], $aggregateLoader->getGroups() );

		$mockMgWANCache->expects( $this->once() )
			->method( 'delete' );

		// should trigger the delete method on cache
		$aggregateLoader->clearCache();
	}
}
