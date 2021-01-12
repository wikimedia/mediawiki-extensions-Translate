<?php
declare( strict_types = 1 );

/**
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @covers \ElasticSearchTTMServer
 */
class ElasticSearchTTMServerTest extends MediaWikiIntegrationTestCase {
	public function setUp(): void {
		parent::setUp();

		$this->config = [
			'primary' => [
				'class' => ElasticSearchTTMServer::class,
				'mirrors' => [ 'secondary' ],
			],
			'secondary' => [
				'class' => ElasticSearchTTMServer::class,
				'mirrors' => [ 'primary', 'unknown' ],
			],
		];

		$this->setMwGlobals( [
			'wgTranslateTranslationServices' => $this->config,
			'wgTranslateTranslationDefaultService' => 'primary',
		] );
	}

	public function testMirrorsConfig() {
		$primary = TTMServer::factory( $this->config['primary'] );
		$this->assertEquals( [ 'secondary' ], $primary->getMirrors() );
		$secondary = TTMServer::factory( $this->config['secondary'] );
		$this->expectException( TTMServerException::class );
		$secondary->getMirrors();
	}
}
