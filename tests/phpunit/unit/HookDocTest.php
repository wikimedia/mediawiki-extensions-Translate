<?php
/**
 * Checks hook documentation is up to date.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2012-2013, Niklas Laxström
 * @license GPL-2.0-or-later
 */

namespace MediaWiki\Extension\Translate\Tests\Unit;

use MediaWikiUnitTestCase;

/** @coversNothing */
class HookDocTest extends MediaWikiUnitTestCase {

	public function testHookDocumentation() {
		$this->assertArrayEquals(
			$this->grepMwHookFireCalls(),
			$this->grepDocumentedJavaScriptHooks()
		);
	}

	private function grepMwHookFireCalls(): array {
		$hooks = [];
		foreach ( glob( __DIR__ . '/../../../resources/js/*.js' ) as $file ) {
			preg_match_all( '/\bmw\.hook\(\s*[\'"]([^\'"]+)[\'"]\s*\).fire\(/',
				file_get_contents( $file ),
				$matches
			);
			array_push( $hooks, ...$matches[1] );
		}
		return array_unique( $hooks );
	}

	private function grepDocumentedJavaScriptHooks(): array {
		$hooks = [];
		$fp = fopen( __DIR__ . '/../../../hooks.txt', 'r' );
		// phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition
		while ( ( $line = fgets( $fp ) ) !== false ) {
			if ( preg_match( '/^;([\w.]+): /', $line, $matches ) ) {
				$hooks[] = $matches[1];
			}
		}
		fclose( $fp );
		return $hooks;
	}

}
