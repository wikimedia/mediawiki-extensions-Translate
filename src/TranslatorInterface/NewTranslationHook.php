<?php
declare( strict_types = 1 );

// phpcs:disable MediaWiki.NamingConventions.LowerCamelFunctionsName.FunctionName

namespace MediaWiki\Extension\Translate\TranslatorInterface;

use MediaWiki\Extension\Translate\MessageLoading\MessageHandle;
use MediaWiki\User\User;

/**
 * This is a hook handler interface, see docs/Hooks.md in core.
 * Use the hook name "Translate:newTranslation" to register handlers implementing this interface.
 *
 * @stable to implement
 * @ingroup Hooks
 */
interface NewTranslationHook {
	/**
	 * Event triggered when non-fuzzy translation has been made
	 *
	 * @param MessageHandle $handle
	 * @param int $revisionId
	 * @param string $text Content of the new translation
	 * @param User $user User who created or changed the translation
	 * @return bool|void True or no return value to continue or false to abort
	 */
	public function onTranslate_newTranslation( MessageHandle $handle, int $revisionId, string $text, User $user );
}
