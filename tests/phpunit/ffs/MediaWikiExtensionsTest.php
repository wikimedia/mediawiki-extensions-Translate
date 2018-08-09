<?php
/**
 * Test for parsing the special definition file for mediawiki-extensions
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */
class MediaWikiExtensionsTest extends PHPUnit\Framework\TestCase {
	/**
	 * @requires function yaml_parse
	 * @covers PremadeMediawikiExtensionGroups
	 */
	public function testParsing() {
		$defs = __DIR__ . '/../data/mediawiki-extensions.txt';
		$path = '%GROUPROOT%/mediawiki-extensions/extensions';
		$foo = new PremadeMediawikiExtensionGroups( $defs, $path );
		$list = $deps = $autoload = [];
		$foo->register( $list, $deps, $autoload );

		$this->assertEquals( 1, count( $deps ), 'A dependency to definition file was added' );
		$this->assertEquals( 5, count( $list ), 'Right number of groups were created' );

		$this->assertArrayHasKey( 'ext-wikimediamessages', $list );
		$expected = TranslateYaml::load( __DIR__ . '/../data/MediaWikiExtensionTest-conf2.yaml' );
		$this->assertEquals( $expected, $list['ext-wikimediamessages']->getConfiguration() );

		$this->assertArrayHasKey( 'ext-examplejsonextension', $list );
		$expected = TranslateYaml::load( __DIR__ . '/../data/MediaWikiExtensionTest-conf3.yaml' );
		$this->assertEquals( $expected, $list['ext-examplejsonextension']->getConfiguration() );

		$this->assertArrayHasKey( 'ext-exampleextension2', $list );
		$expected = TranslateYaml::load( __DIR__ . '/../data/MediaWikiExtensionTest-conf4.yaml' );
		$this->assertEquals( $expected, $list['ext-exampleextension2']->getConfiguration() );

		$this->assertArrayHasKey( 'ext-languagesmodified', $list );
		$languages = $list['ext-languagesmodified']->getTranslatableLanguages();
		$this->assertArrayHasKey( 'foo', $languages, 'Whitelisted language is available' );
		$this->assertArrayNotHasKey( 'bar', $languages, 'Blacklisted language is not available' );
		$this->assertArrayHasKey( 'de', $languages, 'Default language is available' );

		$this->assertArrayHasKey( 'ext-languagesset', $list );
		$languages = $list['ext-languagesset']->getTranslatableLanguages();
		$this->assertArrayHasKey( 'foo', $languages, 'Set language is available' );
		$this->assertArrayNotHasKey( 'de', $languages, 'Unset language is not available' );
	}
}
