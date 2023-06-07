<?php
declare( strict_types = 1 );

// phpcs:disable MediaWiki.NamingConventions.LowerCamelFunctionsName.FunctionName

namespace MediaWiki\Extension\Translate\TranslatorSandbox;

use MediaWiki\User\UserIdentity;

/**
 * This is a hook handler interface, see docs/Hooks.md in core.
 * Use the hook name "Translate:TranslatorSandbox:UserPromoted" to register handlers implementing this interface.
 *
 * @stable to implement
 * @ingroup Hooks
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @since 2023.03
 */
interface UserPromotedHook {
	/** Event generated when an account inside the translator sandbox is approved. */
	public function onTranslate_TranslatorSandbox_UserPromoted( UserIdentity $user ): void;
}
