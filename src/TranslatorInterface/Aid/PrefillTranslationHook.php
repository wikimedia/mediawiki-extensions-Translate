<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TranslatorInterface\Aid;

use MediaWiki\Extension\Translate\MessageLoading\MessageHandle;

/**
 * This is a hook handler interface, see docs/Hooks.md in core.
 * Use the hook name "TranslatePrefillTranslation" to register handlers implementing this interface.
 *
 * @stable to implement
 * @ingroup Hooks
 */
interface PrefillTranslationHook {
	/**
	 * Provides an opportunity for a new translation to start not from as a carte blanche (the default)
	 * but from some prefilled string
	 *
	 * @param string|null &$translation The translation string as it stands, or null for new translations
	 * @param MessageHandle $handle The current MessageHandle object
	 * @return bool|void True or no return value to continue or false to abort
	 */
	public function onTranslatePrefillTranslation( ?string &$translation, MessageHandle $handle );
}
