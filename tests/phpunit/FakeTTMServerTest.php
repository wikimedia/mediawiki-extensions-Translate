<?php
declare( strict_types = 1 );

/**
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @covers \FakeTTMServer
 */
class FakeTTMServerTest extends MediaWikiIntegrationTestCase {
	public function testFakeTTMServer() {
		$server = new FakeTTMServer();
		$this->assertEquals(
			[],
			$server->query( 'en', 'fi', 'daa' ),
			'FakeTTMServer returns no suggestions for all queries'
		);

		$title = $this->createMock( Title::class );
		$handle = new MessageHandle( $title );

		$this->assertNull(
			$server->update( $handle, 'text' ),
			'FakeTTMServer update is no-op and always succeeds'
		);
	}
}
