<?php
/**
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */

/**
 * @coversDefaultClass \MessageGroupCache
 */
class MessageGroupCacheTest extends MediaWikiIntegrationTestCase {
	public function testKeyOrder() {
		$messages = [
			'x-message' => '',
			'b-message' => '',
			'X-message' => '',
			'a-message' => '',
		];

		$group = $this->createMock( FileBasedMessageGroup::class );
		$group->method( 'getId' )->willReturn( 'test-group-id' );
		$group->method( 'getSourceFilePath' )->willReturn( __FILE__ );
		$group->method( 'load' )->willReturn( $messages );

		$cache = new MessageGroupCache( $group, 'en', $this->getNewTempFile() );
		$cache->create();

		$expected = array_keys( $messages );
		$actual = $cache->getKeys();
		$this->assertSame( $expected, $actual, 'Cache should return correct keys in same order' );
	}
}
