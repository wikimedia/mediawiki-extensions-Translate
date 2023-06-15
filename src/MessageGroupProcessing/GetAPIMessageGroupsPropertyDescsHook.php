<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

/**
 * This is a hook handler interface, see docs/Hooks.md in core.
 * Use the hook name "TranslateGetAPIMessageGroupsPropertyDescs" to register handlers implementing this interface.
 *
 * @stable to implement
 * @ingroup Hooks
 */
interface GetAPIMessageGroupsPropertyDescsHook {
	/**
	 * Allows extra properties to be added to captured by action=query&meta=messagegroups&mgprop=foo|bar|bat module
	 *
	 * @param array &$properties An associative array of properties, name => description (which is ignored)
	 * @return bool|void True or no return value to continue or false to abort
	 */
	public function onTranslateGetAPIMessageGroupsPropertyDescs( array &$properties );
}
