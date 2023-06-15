<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

/**
 * This is a hook handler interface, see docs/Hooks.md in core.
 * Use the hook name "TranslateGetAPIMessageGroupsParameterList" to register handlers implementing this interface.
 *
 * @stable to implement
 * @ingroup Hooks
 */
interface GetAPIMessageGroupsParameterListHook {
	/**
	 * Allows extra parameters to be added to the action=query&meta=messagegroups module
	 *
	 * @param array &$params An associative array of possible parameters (name => details;
	 *  see ApiQueryMessageGroups.php for correct spacing)
	 * @return bool|void True or no return value to continue or false to abort
	 */
	public function onTranslateGetAPIMessageGroupsParameterList( array &$params );
}
