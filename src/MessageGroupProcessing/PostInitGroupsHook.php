<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use MessageGroup;

/**
 * This is a hook handler interface, see docs/Hooks.md in core.
 * Use the hook name "TranslatePostInitGroups" to register handlers implementing this interface.
 *
 * @stable to implement
 * @ingroup Hooks
 */
interface PostInitGroupsHook {
	/**
	 * Hook to register new message groups to Translate. Manual cache clear may be needed to have this hook executed.
	 *
	 * @param MessageGroup[] &$groups Map of message group id to message group instance
	 * @param array &$deps List of dependencies as supported by DependencyWrapper class from MediaWiki
	 * @param string[] &$autoload List of autoloaded classes. Key is the name of the class and value is filename
	 * @return bool|void True or no return value to continue or false to abort
	 */
	public function onTranslatePostInitGroups( array &$groups, array &$deps, array &$autoload );
}
