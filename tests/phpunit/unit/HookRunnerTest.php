<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Tests\Unit;

use MediaWiki\Extension\Translate\HookRunner;
use MediaWiki\Extension\Translate\PageTranslation\TranslateTitleEnum;
use MediaWiki\Tests\HookContainer\HookRunnerTestBase;
use ReflectionParameter;

/** @covers \MediaWiki\Extension\Translate\HookRunner */
class HookRunnerTest extends HookRunnerTestBase {

	public static function provideHookRunners() {
		yield HookRunner::class => [ HookRunner::class ];
	}

	protected function getMockedParamValue( ReflectionParameter $param ) {
		// Enum types cannot be mocked, so return a real value here.
		if ( $param->getType()?->getName() === TranslateTitleEnum::class ) {
			return TranslateTitleEnum::DEFAULT_CHECKED;
		}
		return parent::getMockedParamValue( $param );
	}
}
