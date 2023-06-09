<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TranslatorInterface;

/**
 * This is a hook handler interface, see docs/Hooks.md in core.
 * Use the hook name "TranslateBeforeAddModules" to register handlers implementing this interface.
 *
 * @stable to implement
 * @ingroup Hooks
 */
interface BeforeAddModulesHook {
	/**
	 * Provides an opportunity to load extra modules
	 *
	 * @param string[] &$modules List of resource loader module names
	 * @return bool|void True or no return value to continue or false to abort
	 */
	public function onTranslateBeforeAddModules( array &$modules );
}
