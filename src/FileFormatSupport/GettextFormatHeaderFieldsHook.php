<?php
declare( strict_types = 1 );

// phpcs:disable MediaWiki.NamingConventions.LowerCamelFunctionsName.FunctionName
namespace MediaWiki\Extension\Translate\FileFormatSupport;

use MessageGroup;

/**
 * This is a hook handler interface, see docs/Hooks.md in core.
 * Use the hook name "Translate:GettextFormat:headerFields" to register handlers implementing this interface.
 *
 * @stable to implement
 * @ingroup Hooks
 */
interface GettextFormatHeaderFieldsHook {
	/**
	 * Allows per group customization of headers in exported Gettext files per group.
	 * Certain X-headers and Plural-Forms cannot be customized.
	 *
	 * @param array<string,string> &$headers List of prefilled headers. You can remove, change or add new headers.
	 * @param MessageGroup $group
	 * @param string $languageCode
	 * @return void|bool True or no return value to continue or false to abort
	 */
	public function onTranslate_GettextFormat_headerFields(
		array &$headers,
		MessageGroup $group,
		string $languageCode
	);
}
