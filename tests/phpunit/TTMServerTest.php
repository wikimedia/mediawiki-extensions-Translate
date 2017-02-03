<?php
/**
 * Tests for TTMServer
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2012-2013, Niklas Laxström
 * @license GPL-2.0+
 */

class TTMServerTest extends MediaWikiTestCase {
	protected $config;

	protected function setUp() {
		global $wgTranslateTranslationServices;
		$this->config = $wgTranslateTranslationServices;
		parent::setUp();

		$wgTranslateTranslationServices = array();
		$wgTranslateTranslationServices['localtm'] = array(
			'url' => 'http://example.com/sandwiki/api.php',
			'displayname' => 'example.com',
			'cutoff' => 0.75,
			'type' => 'ttmserver',
		);

		$wgTranslateTranslationServices['apitm'] = array(
			'url' => 'http://example.com/w/api.php',
			'displayname' => 'example.com',
			'cutoff' => 0.75,
			'timeout-sync' => 4,
			'timeout-async' => 4,
			'type' => 'ttmserver',
			'class' => 'RemoteTTMServer',
		);
	}

	protected function tearDown() {
		global $wgTranslateTranslationServices;
		$wgTranslateTranslationServices = $this->config;
		parent::tearDown();
	}

	public function testConstruct() {
		$server = TTMServer::primary();
		$this->assertEquals(
			'FakeTTMServer',
			get_class( $server ),
			'Fake server given when default server is disabled'
		);
		global $wgTranslateTranslationServices,
			$wgTranslateTranslationDefaultService;
		$wgTranslateTranslationServices[$wgTranslateTranslationDefaultService] = array(
			'database' => false, // Passed to wfGetDB
			'cutoff' => 0.75,
			'type' => 'ttmserver',
			'public' => false,
		);
		$server = TTMServer::primary();
		$this->assertEquals(
			'DatabaseTTMServer',
			get_class( $server ),
			'Real server given when default server is enabled'
		);
		unset( $wgTranslateTranslationServices[$wgTranslateTranslationDefaultService] );
	}

	public function testFakeTTMServer() {
		$server = new FakeTTMServer();
		$this->assertEquals(
			array(),
			$server->query( 'en', 'fi', 'daa' ),
			'FakeTTMServer returns no suggestions for all queries'
		);

		$title = new Title();
		$handle = new MessageHandle( $title );

		$this->assertNull(
			$server->update( $handle, 'text' ),
			'FakeTTMServer returns null on update'
		);
	}

	public function testMirrorsConfig() {
		global $wgTranslateTranslationServices;
		$wgTranslateTranslationServices['primary'] = array(
			'class' => 'ElasticSearchTTMServer',
			'mirrors' => ['secondary']
		);
		$wgTranslateTranslationServices['secondary'] = array(
			'class' => 'ElasticSearchTTMServer',
			'mirrors' => ['primary', 'unknown']
		);
		$primary = TTMServer::factory( $wgTranslateTranslationServices['primary'] );
		$this->assertEquals( ['secondary'], $primary->getMirrors() );
		$secondary = TTMServer::factory( $wgTranslateTranslationServices['secondary'] );
		$this->setExpectedException( TTMServerException::class );
		$secondary->getMirrors();
	}
}
