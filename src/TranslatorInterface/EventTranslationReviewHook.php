<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TranslatorInterface;

use MediaWiki\Extension\Translate\MessageLoading\MessageHandle;

/**
 * This is a hook handler interface, see docs/Hooks.md in core.
 * Use the hook name "TranslateEventTranslationReview" to register handlers implementing this interface.
 *
 * @stable to implement
 * @ingroup Hooks
 */
interface EventTranslationReviewHook {
	/**
	 * Event triggered when a translation is proofread
	 *
	 * @param MessageHandle $handle
	 * @return bool|void True or no return value to continue or false to abort
	 */
	public function onTranslateEventTranslationReview( MessageHandle $handle );
}
