<?php
/**
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */

/** @coversDefaultClass \MessageGroupCache */
class MessageGroupCacheTest extends MediaWikiIntegrationTestCase {
	public function testCacheRoundtrip() {
		$parseOutput = [
			'AUTHORS' => [ 'Bunny the king of the carrot land' ],
			'MESSAGES' => [
				'x-message' => 'fluffy',
				'b-message' => 'animal',
				'X-message' => 'with',
				'a-message' => 'ears',
			],
			'EXTRA' => [
				'food' => 'carrot'
			],
		];

		$group = $this->createMock( FileBasedMessageGroup::class );
		$group->method( 'getSourceFilePath' )->willReturn( __FILE__ );
		$group->method( 'parseExternal' )->willReturn( $parseOutput );

		$cache = new MessageGroupCache( $group, 'en', $this->getNewTempFile() );
		$cache->create();

		$expected = array_keys( $parseOutput['MESSAGES'] );
		$actual = $cache->getKeys();
		$this->assertSame( $expected, $actual, 'Cache should return correct keys in same order' );

		$expected = $parseOutput['MESSAGES']['b-message'];
		$actual = $cache->get( 'b-message' );
		$this->assertSame( $expected, $actual, 'Cache should return correct message content' );

		$expected = $parseOutput['AUTHORS'];
		$actual = $cache->getAuthors();
		$this->assertSame( $expected, $actual, 'Cache should return correct authors' );

		$expected = $parseOutput['EXTRA'];
		$actual = $cache->getExtra();
		$this->assertSame( $expected, $actual, 'Cache should return extra data' );
	}
}
