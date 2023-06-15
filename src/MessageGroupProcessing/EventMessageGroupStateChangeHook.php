<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use MessageGroup;

/**
 * This is a hook handler interface, see docs/Hooks.md in core.
 * Use the hook name "TranslateEventMessageGroupStateChange" to register handlers implementing this interface.
 *
 * @stable to implement
 * @ingroup Hooks
 */
interface EventMessageGroupStateChangeHook {
	/**
	 * Event triggered when a message group workflow state is changed in a language
	 *
	 * @param MessageGroup $group Message group instance
	 * @param string $code Language code
	 * @param string|false $oldState
	 * @param string $newState
	 * @return bool|void True or no return value to continue or false to abort
	 */
	public function onTranslateEventMessageGroupStateChange(
		MessageGroup $group,
		string $code,
		$oldState,
		string $newState
	);
}
