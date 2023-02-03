<?php
declare( strict_types = 1 );

use MediaWiki\Extension\Translate\Services;

/**
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @covers \ElasticSearchTTMServer
 */
class ElasticSearchTTMServerTest extends MediaWikiIntegrationTestCase {
	private array $config = [];

	protected function setUp(): void {
		parent::setUp();

		$this->config = [
			'primary' => [
				'class' => ElasticSearchTTMServer::class,
				'type' => 'ttmserver',
				'mirrors' => [ 'secondary' ],
			],
			'secondary' => [
				'class' => ElasticSearchTTMServer::class,
				'type' => 'ttmserver',
				'mirrors' => [ 'primary', 'unknown' ],
			],
		];

		$this->setMwGlobals( [
			'wgTranslateTranslationServices' => $this->config,
			'wgTranslateTranslationDefaultService' => 'primary',
		] );
	}

	public function testMirrorsConfig() {
		$ttmServerFactory = Services::getInstance()->getTtmServerFactory();
		$primary = $ttmServerFactory->create( 'primary' );
		$this->assertEquals( [ 'secondary' ], $primary->getMirrors() );
		$secondary = $ttmServerFactory->create( 'secondary' );
		$this->expectException( TTMServerException::class );
		$secondary->getMirrors();
	}
}
