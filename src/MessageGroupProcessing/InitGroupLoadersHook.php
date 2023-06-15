<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

/**
 * This is a hook handler interface, see docs/Hooks.md in core.
 * Use the hook name "TranslateInitGroupLoaders" to register handlers implementing this interface.
 *
 * @stable to implement
 * @ingroup Hooks
 */
interface InitGroupLoadersHook {
	/**
	 * Hook to register new message group loaders that can then load MessageGroups for translation purpose
	 *
	 * @param array &$groupLoader List of message group loader class names that implement the MessageGroupLoader
	 * @param array $deps
	 * @return bool|void True or no return value to continue or false to abort
	 */
	public function onTranslateInitGroupLoaders( array &$groupLoader, array $deps );
}
