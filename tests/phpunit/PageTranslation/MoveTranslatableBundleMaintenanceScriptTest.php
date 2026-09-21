<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

use MediaWikiIntegrationTestCase;
use Wikimedia\TestingAccessWrapper;

/**
 * @license GPL-2.0-or-later
 * @covers \MediaWiki\Extension\Translate\PageTranslation\MoveTranslatableBundleMaintenanceScript
 */
class MoveTranslatableBundleMaintenanceScriptTest extends MediaWikiIntegrationTestCase {
	/**
	 * Builds the script with a fake console.
	 *
	 * ::readconsole is static, so PHPUnit cannot mock it. A subclass supplies the value
	 * instead. A real call would wait for user input and hang the test.
	 *
	 * @param string|false $consoleValue The value that ::readconsole returns.
	 */
	private function newScript( string|false $consoleValue ): MoveTranslatableBundleMaintenanceScript {
		$script = new class() extends MoveTranslatableBundleMaintenanceScript {
			public static string|false $consoleValue = false;

			/** @inheritDoc */
			public static function readconsole( $prompt = '> ' ) {
				return static::$consoleValue;
			}
		};
		$script::$consoleValue = $consoleValue;
		return $script;
	}

	/** Regression test for T438711: a closed stdin must not cause a TypeError. */
	public function testGetConfirmationWithoutConsole(): void {
		$script = $this->newScript( false );
		$this->expectOutputRegex( '/Use --really/' );
		$this->assertFalse(
			TestingAccessWrapper::newFromObject( $script )
				->getConfirmation()
		);
	}

	/** @dataProvider provideConsoleInput */
	public function testGetConfirmationFromConsole( string $input, bool $expected ): void {
		$script = $this->newScript( $input );
		$this->assertSame(
			$expected,
			TestingAccessWrapper::newFromObject( $script )
				->getConfirmation()
		);
	}

	public static function provideConsoleInput(): array {
		return [
			'exact word' => [ 'MOVE', true ],
			'lower case' => [ 'move', true ],
			'mixed case' => [ 'Move', true ],
			'empty input' => [ '', false ],
			'other word' => [ 'really', false ],
		];
	}

	public function testReallyOptionSkipsThePrompt(): void {
		$script = $this->newScript( false );
		$script->setOption( 'really', 1 );
		$this->assertTrue(
			TestingAccessWrapper::newFromObject( $script )
				->getConfirmation()
		);
	}
}
