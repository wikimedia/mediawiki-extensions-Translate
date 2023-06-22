<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Utilities;

/**
 * This is a hook handler interface, see docs/Hooks.md in core.
 * Use the hook name "TranslateSupportedLanguages" to register handlers implementing this interface.
 *
 * @stable to implement
 * @ingroup Hooks
 */
interface SupportedLanguagesHook {
	/**
	 * Allows removing languages from language selectors. For adding $wgExtraLanguage names is recommended.
	 *
	 * @param string[] &$list List of languages indexed by language code
	 * @param string|null $language Language code of the language of which language names are in
	 * @return bool|void True or no return value to continue or false to abort
	 */
	public function onTranslateSupportedLanguages( array &$list, ?string $language );
}
