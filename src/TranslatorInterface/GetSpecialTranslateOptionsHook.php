<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TranslatorInterface;

/**
 * This is a hook handler interface, see docs/Hooks.md in core.
 * Use the hook name "TranslateGetSpecialTranslateOptions" to register handlers implementing this interface.
 *
 * @stable to implement
 * @ingroup Hooks
 */
interface GetSpecialTranslateOptionsHook {
	/**
	 * Provides an opportunity for overriding task values
	 *
	 * @param array &$defaults Associative array of default values
	 * @param array &$nonDefaults Associative array of nondefault (override) values
	 * @return bool|void True or no return value to continue or false to abort
	 */
	public function onTranslateGetSpecialTranslateOptions( array &$defaults, array &$nonDefaults );
}
