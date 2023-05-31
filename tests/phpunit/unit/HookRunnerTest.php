<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Tests\Unit;

use MediaWiki\Extension\Translate\HookRunner;
use MediaWiki\Tests\HookContainer\HookRunnerTestBase;

/** @covers \MediaWiki\Extension\Translate\HookRunner */
class HookRunnerTest extends HookRunnerTestBase {

	public static function provideHookRunners() {
		yield HookRunner::class => [ HookRunner::class ];
	}
}
