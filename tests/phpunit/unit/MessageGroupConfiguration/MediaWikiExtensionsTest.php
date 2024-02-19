<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupConfiguration;

use MediaWiki\Extension\Translate\Utilities\Yaml;
use MediaWikiUnitTestCase;

/**
 * Test for parsing the special definition file for mediawiki-extensions
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @covers \MediaWiki\Extension\Translate\MessageGroupConfiguration\PremadeMediaWikiExtensionGroups
 */
class MediaWikiExtensionsTest extends MediaWikiUnitTestCase {
	/** @requires function yaml_parse */
	public function testParsing(): void {
		$defs = __DIR__ . '/../../data/mediawiki-extensions.txt';
		$path = '%GROUPROOT%/mediawiki-extensions/extensions';
		$foo = new PremadeMediaWikiExtensionGroups( $defs, $path );
		$list = $deps = $autoload = [];
		$foo->register( $list, $deps, $autoload );

		$this->assertCount( 1, $deps, 'A dependency to definition file was added' );
		$this->assertCount( 5, $list, 'Right number of groups were created' );

		$this->assertArrayHasKey( 'ext-wikimediamessages', $list );
		$expected = Yaml::load( __DIR__ . '/../../data/MediaWikiExtensionTest-conf2.yaml' );
		$this->assertEquals( $expected, $list['ext-wikimediamessages']->getConfiguration() );

		$this->assertArrayHasKey( 'ext-examplejsonextension', $list );
		$expected = Yaml::load( __DIR__ . '/../../data/MediaWikiExtensionTest-conf3.yaml' );
		$this->assertEquals( $expected, $list['ext-examplejsonextension']->getConfiguration() );

		$this->assertArrayHasKey( 'ext-exampleextension2', $list );
		$expected = Yaml::load( __DIR__ . '/../../data/MediaWikiExtensionTest-conf4.yaml' );
		$this->assertEquals( $expected, $list['ext-exampleextension2']->getConfiguration() );

		$this->assertArrayHasKey( 'ext-languagesmodified', $list );
		$languages = $list['ext-languagesmodified']->getTranslatableLanguages();
		$this->assertArrayHasKey( 'foo', $languages, 'Included language is available' );
		$this->assertArrayNotHasKey( 'bar', $languages, 'Excluded language is not available' );
		$this->assertArrayHasKey( 'de', $languages, 'Default language is available' );

		$this->assertArrayHasKey( 'ext-languagesset', $list );
		$languages = $list['ext-languagesset']->getTranslatableLanguages();
		$this->assertArrayHasKey( 'foo', $languages, 'Set language is available' );
		$this->assertArrayNotHasKey( 'de', $languages, 'Unset language is not available' );
	}
}
