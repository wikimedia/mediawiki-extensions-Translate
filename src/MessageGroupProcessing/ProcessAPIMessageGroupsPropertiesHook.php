<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use MessageGroup;

/**
 * This is a hook handler interface, see docs/Hooks.md in core.
 * Use the hook name "TranslateProcessAPIMessageGroupsProperties" to register handlers implementing this interface.
 *
 * @stable to implement
 * @ingroup Hooks
 */
interface ProcessAPIMessageGroupsPropertiesHook {
	/**
	 * Allows extra property requests to be acted upon, and the new properties returned
	 *
	 * @param array &$a Associative array of the properties of $group that will be returned
	 * @param array $props Associative array ($name => true) of properties the user has specifically requested
	 * @param array $params Parameter input by the user (unprefixed name => value)
	 * @param MessageGroup $g The group in question
	 * @return bool|void True or no return value to continue or false to abort
	 */
	public function onTranslateProcessAPIMessageGroupsProperties(
		array &$a,
		array $props,
		array $params,
		MessageGroup $g
	);
}
