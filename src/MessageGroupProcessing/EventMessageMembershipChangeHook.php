<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use MediaWiki\Extension\Translate\MessageLoading\MessageHandle;

/**
 * This is a hook handler interface, see docs/Hooks.md in core.
 * Use the hook name "TranslateEventMessageMembershipChange" to register handlers implementing this interface.
 *
 * @stable to implement
 * @ingroup Hooks
 */
interface EventMessageMembershipChangeHook {
	/**
	 * When group gets new messages or loses messages
	 *
	 * @param MessageHandle $handle
	 * @param string[] $old Previous groups
	 * @param string[] $new Current groups
	 * @return bool|void True or no return value to continue or false to abort
	 */
	public function onTranslateEventMessageMembershipChange( MessageHandle $handle, array $old, array $new );
}
