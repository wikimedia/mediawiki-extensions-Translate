<?php
/**
 * Tests for MediaWikiExtension
 * @author Niklas Laxström
 * @file
 * @copyright Copyright © 2012-2013, Niklas Laxström
 * @license GPL-2.0+
 */

/**
 * Tests that the special definition file is parsed properly.
 */
class MediaWikiExtensionTest extends MediaWikiTestCase {
	public function testParsing() {
		$defs = __DIR__ . '/data/mediawiki-extensions.txt';
		$path = '%GROUPROOT%/mediawiki-extensions/extensions';
		$foo = new PremadeMediawikiExtensionGroups( $defs, $path );
		$list = $deps = $autoload = array();
		$foo->register( $list, $deps, $autoload );

		$this->assertEquals( 1, count( $deps ), 'A dependency to definition file was added' );
		$this->assertEquals( 2, count( $list ), 'Two group were creates' );

		$this->assertArrayHasKey( 'ext-exampleextension', $list );
		$expected = TranslateYaml::load( __DIR__ . '/data/MediaWikiExtensionTest-conf1.yaml' );
		$this->assertEquals( $expected, $list['ext-exampleextension']->getConfiguration() );

		$this->assertArrayHasKey( 'ext-wikimediamessages', $list );
		$expected = TranslateYaml::load( __DIR__ . '/data/MediaWikiExtensionTest-conf2.yaml' );
		$this->assertEquals( $expected, $list['ext-wikimediamessages']->getConfiguration() );
	}
}
