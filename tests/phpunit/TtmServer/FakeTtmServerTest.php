<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TtmServer;

use MediaWiki\Extension\Translate\MessageLoading\MessageHandle;
use MediaWiki\Title\Title;
use MediaWikiIntegrationTestCase;

/**
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @covers \MediaWiki\Extension\Translate\TtmServer\FakeTtmServer
 */
class FakeTtmServerTest extends MediaWikiIntegrationTestCase {
	public function testFakeTTMServer(): void {
		$server = new FakeTtmServer();
		$this->assertEquals(
			[],
			$server->query( 'en', 'fi', 'daa' ),
			'FakeTTMServer returns no suggestions for all queries'
		);

		$title = $this->createMock( Title::class );
		$handle = new MessageHandle( $title );

		$this->assertTrue(
			$server->update( $handle, 'text' ),
			'FakeTTMServer update is no-op and always succeeds'
		);
	}
}
