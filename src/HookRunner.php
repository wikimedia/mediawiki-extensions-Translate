<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate;

use MediaWiki\Extension\Translate\TranslatorSandbox\UserPromotedHook;
use MediaWiki\HookContainer\HookContainer;
use MediaWiki\User\UserIdentity;

/**
 * Hook runner for the Translate extension.
 *
 * Some legacy style hooks have not been converted to interfaces yet.
 *
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @since 2023.03
 */
class HookRunner implements UserPromotedHook {
	private HookContainer $hookContainer;

	public function __construct( HookContainer $hookContainer ) {
		$this->hookContainer = $hookContainer;
	}

	public function onTranslateTranslatorSandboxUserPromoted( UserIdentity $user ): void {
		$this->hookContainer->run( 'Translate:TranslatorSandbox:UserPromoted', [ $user ], [ 'abortable' => false ] );
	}
}
