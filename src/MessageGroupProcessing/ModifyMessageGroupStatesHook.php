<?php
declare( strict_types = 1 );

// phpcs:disable MediaWiki.NamingConventions.LowerCamelFunctionsName.FunctionName

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

/**
 * This is a hook handler interface, see docs/Hooks.md in core.
 * Use the hook name "Translate:modifyMessageGroupStates" to register handlers implementing this interface.
 *
 * @stable to implement
 * @ingroup Hooks
 */
interface ModifyMessageGroupStatesHook {
	/**
	 * Allow hooks to change workflow states depending on the group's ID
	 *
	 * @param string $groupId ID of the current message group
	 * @param array &$conf Workflow states, can be modified
	 * @return bool|void True or no return value to continue or false to abort
	 */
	public function onTranslate_modifyMessageGroupStates( string $groupId, array &$conf );
}
