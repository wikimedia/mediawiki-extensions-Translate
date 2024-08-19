<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Utilities;

use MediaWikiIntegrationTestCase;

/**
 * Tests for yaml wrapper.
 *
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @covers \MediaWiki\Extension\Translate\Utilities\Yaml
 */
class YamlTest extends MediaWikiIntegrationTestCase {
	protected function setUp(): void {
		parent::setUp();
		$this->overrideConfigValue( 'TranslateYamlLibrary', 'phpyaml' );
	}

	/**
	 * TODO: test other drivers too.
	 * @requires function yaml_parse
	 * @dataProvider provideTestLoadString
	 */
	public function testLoadStringPhpyaml( string $input, array $expected, string $comment ): void {
		$output = Yaml::loadString( $input );
		$this->assertEquals( $expected, $output, $comment );
	}

	public static function provideTestLoadString() {
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

		global $wgTranslateYamlLibrary;
		if ( $wgTranslateYamlLibrary === 'phpyaml'
			&& version_compare( phpversion( 'yaml' ), '2.2.0', '>=' )
		) {
			// https://bugs.php.net/bug.php?id=79866
			$c = '2';
		} else {
			$c = '2.000000';
		}

		$expected =
			<<<YAML
			---
			a: "2."
			b: "22222222222222222222222222222222222222222222222222222222222222."
			c: $c
			d: "2.0"
			...

			YAML;

		$output = Yaml::dump( $input );
		$this->assertEquals( $expected, $output, "Floaty strings outputted as strings" );
		$parsed = Yaml::loadString( $output );
		$this->assertEquals( $input, $parsed, "Floaty strings roundtrip" );
	}
}
