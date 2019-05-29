<?php
if ( !defined( 'MEDIAWIKI' ) ) {
	die();
}
/**
 * An extension to ease the translation of Mediawiki and other projects.
 *
 * @file
 * @ingroup Extensions
 *
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @copyright Copyright © 2006-2019, Niklas Laxström, Siebrand Mazeland
 * @license GPL-2.0-or-later
 */

/**
 * Version number used in extension credits and in other places where needed.
 */
define( 'TRANSLATE_VERSION', '2019-04-24' );

// Load stuff already converted to extension registration.
wfLoadExtension( 'Translate', __DIR__ . '/extension-wip.json' );

/**
 * Registering various resources
 * @cond file_level_code
 */

$wgMessagesDirs['PageTranslation'] = __DIR__ . '/i18n/pagetranslation';
$wgMessagesDirs['Translate'] = __DIR__ . '/i18n/core';
$wgMessagesDirs['TranslateSearch'] = __DIR__ . '/i18n/search';
$wgMessagesDirs['TranslateSandbox'] = __DIR__ . '/i18n/sandbox';
$wgMessagesDirs['TranslateApi'] = __DIR__ . '/i18n/api';
$wgExtensionMessagesFiles['TranslateAlias'] = __DIR__ . '/Translate.alias.php';
$wgExtensionMessagesFiles['TranslateMagic'] = __DIR__ . '/Translate.i18n.magic.php';

/** @endcond */

# == Configuration variables ==

/**
 * Define various web services that provide translation suggestions.
 *
 * Translation memories are documented in our main documentation.
 * @see https://www.mediawiki.org/wiki/Help:Extension:Translate/Translation_memories
 *
 * For Apertium, you should get an API key.
 * @see http://wiki.apertium.org/wiki/Apertium_web_service
 *
 * Yandex translation helper also provides langlimit option to limit total
 * number of suggestions (set to 0 to get all possible translations)
 * and langorder array to sort languages. Yandex translate engine is based on
 * wordnet, generated from search index, so number of indexed websites should be
 * a good heuristic to define the default language order.
 *
 * The machine translation services are provided with the following information:
 * - server ip address
 * - versions of MediaWiki and Translate extension
 * - clients ip address encrypted with $wgProxyKey
 * - source text to translate
 * - private API key if provided
 */
$wgTranslateTranslationDefaultService = 'TTMServer';
$wgTranslateTranslationServices = [];
$wgTranslateTranslationServices['TTMServer'] = [
	'database' => false, // Passed to wfGetDB
	'cutoff' => 0.75,
	'type' => 'ttmserver',
	'public' => false,
];
$wgTranslateTranslationServices['Microsoft'] = [
	'url' => 'https://api.cognitive.microsofttranslator.com',
	'key' => null,
	'timeout' => 3,
	'type' => 'microsoft',
];
$wgTranslateTranslationServices['Apertium'] = [
	'url' => 'http://apy.projectjj.com/translate',
	'pairs' => 'http://apy.projectjj.com/listPairs',
	'key' => null,
	'timeout' => 3,
	'type' => 'apertium',
];
$wgTranslateTranslationServices['Yandex'] = [
	'url' => 'https://translate.yandex.net/api/v1.5/tr.json/translate',
	'key' => null,
	'pairs' => 'https://translate.yandex.net/api/v1.5/tr.json/getLangs',
	'timeout' => 3,
	'langorder' => [ 'en', 'ru', 'uk', 'de', 'fr', 'pl', 'it', 'es', 'tr' ],
	'langlimit' => 1,
	'type' => 'yandex',
];

/**
 * Helper function for adding namespace for message groups.
 *
 * It defines constants for the namespace (and talk namespace) and sets up
 * restrictions and some other configuration.
 * @param int $id Namespace number
 * @param string $name Name of the namespace
 * @param string|null $constant (optional) name of namespace constant, defaults to
 *   NS_ followed by upper case version of $name, e.g., NS_MEDIAWIKI
 */
function wfAddNamespace( $id, $name, $constant = null ) {
	global $wgExtraNamespaces, $wgContentNamespaces, $wgTranslateMessageNamespaces,
		$wgNamespaceProtection, $wgNamespacesWithSubpages, $wgNamespacesToBeSearchedDefault;

	if ( is_null( $constant ) ) {
		$constant = strtoupper( "NS_$name" );
	}

	define( $constant, $id );
	define( $constant . '_TALK', $id + 1 );

	$wgExtraNamespaces[$id] = $name;
	$wgExtraNamespaces[$id + 1] = $name . '_talk';

	$wgContentNamespaces[] = $id;
	$wgTranslateMessageNamespaces[] = $id;

	$wgNamespacesWithSubpages[$id] = true;
	$wgNamespacesWithSubpages[$id + 1] = true;

	$wgNamespaceProtection[$id] = [ 'translate' ];

	$wgNamespacesToBeSearchedDefault[$id] = true;
}

/** @defgroup TranslateSpecialPage Special pages of Translate extension */
