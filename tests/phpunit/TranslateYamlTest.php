<?php
/**
 * Tests for yaml wrapper.
 *
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */

class TranslateYamlTest extends MediaWikiTestCase {
	/**
	 * TODO: test other drivers too.
	 * @requires function yaml_parse
	 * @dataProvider provideTestLoadString
	 */
	public function testLoadStringPhpyaml( $input, $expected, $comment ) {
		$this->setMwGlobals( [
			'wgTranslateYamlLibrary' => 'phpyaml',
		] );

		$output = TranslateYaml::loadString( $input );
		$this->assertEquals( $expected, $output, $comment );
	}

	public function provideTestLoadString() {
		$tests = [];
		$tests[] = [
			'a: b',
			[ 'a' => 'b' ],
			'Simple key-value'
		];

		$tests[] = [
			'a: !php/object "O:8:\"stdClass\":1:{s:1:\"a\";s:1:\"b\";}"',
			[ 'a' => 'O:8:"stdClass":1:{s:1:"a";s:1:"b";}' ],
			'PHP objects must not be unserialized'
		];

		return $tests;
	}
}
