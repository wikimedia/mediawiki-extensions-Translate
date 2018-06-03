<?php
/**
 * Tests for yaml wrapper.
 *
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */

class TranslateYamlTest extends MediaWikiTestCase {
	protected function setUp() {
		parent::setUp();

		$this->setMwGlobals( [
			'wgTranslateYamlLibrary' => 'phpyaml',
		] );
	}

	/**
	 * TODO: test other drivers too.
	 * @requires function yaml_parse
	 * @dataProvider provideTestLoadString
	 */
	public function testLoadStringPhpyaml( $input, $expected, $comment ) {
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

	/**
	 * Tests workaround for https://bugs.php.net/bug.php?id=76309
	 * @requires function yaml_emit
	 */
	public function testBug76309() {
		$input = [
			'a' => '2.',
			'b' => '22222222222222222222222222222222222222222222222222222222222222.',
			'c' => 2.0,
			'd' => "2.0"
		];

		$expected = <<<YAML
---
a: "2."
b: "22222222222222222222222222222222222222222222222222222222222222."
c: 2.000000
d: "2.0"
...

YAML;

		$output = TranslateYaml::dump( $input );
		$this->assertEquals( $expected, $output, "Floaty strings outputted as strings" );
		$parsed = TranslateYaml::loadString( $output );
		$this->assertEquals( $input, $parsed, "Floaty strings roundtrip" );
	}
}
