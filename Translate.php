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

# === Basic configuration ===
# <source lang=php>
/**
 * Add a preference "Do not send me email newsletters" in the email preferences.
 */
$wgTranslateNewsletterPreference = false;

/**
 * Default values for list of languages to show translators as an aid when
 * translating. Each user can override this setting in their preferences.
 * Example:
 *  $wgTranslateLanguageFallbacks['fi'] = 'sv';
 *  $wgTranslateLanguageFallbacks['sv'] = array( 'da', 'no', 'nn' );
 */
$wgTranslateLanguageFallbacks = [];

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
 * Experimental support for an "Ask" help button.
 * Might change into a hook later on.
 * This is an array with keys page, url and params.
 * - page is a title of a local wiki page
 * - url is an URL to use as is (e.g. an issue tracker submission form)
 *   which will override the page if set
 * - params is an array of key-value pairs of request params
 * -- each param value can contain the variable %MESSAGE%
 *    which will be replaced with the full page name.
 * @since 2011-03-11
 */
$wgTranslateSupportUrl = false;

/**
 * Like $wgTranslateSupportUrl, but for a specific namespace.
 * Each $wgTranslateSupportUrl-like array needs to be the value
 * assigned to the numerical ID of a namespace of the wiki.
 * @since 2015.09
 */
$wgTranslateSupportUrlNamespace = [];

/**
 * When unprivileged users open a translation editor, they will
 * see a message stating that a special permission is needed for translating
 * messages. If this variable is defined, there is a button which will
 * take the user to that page to ask for permission.
 * The target needs to be reiterated with the second variable to have
 * the same result with sandbox enabled where users can't enter the sandbox.
 */
$wgTranslatePermissionUrl = 'Project:Translator';
$wgTranslateSecondaryPermissionUrl = 'Project:Translator';

# </source>
# === System setup related configuration ===
# <source lang=php>
/**
 * Set location of cache files. Defaults to $wgCacheDirectory.
 */
$wgTranslateCacheDirectory = false;

/**
 * Configures how the message index is stored.
 * The other backends need $wgCacheDirectory to be functional.
 */
$wgTranslateMessageIndex = [ 'DatabaseMessageIndex' ];
// $wgTranslateMessageIndex = array( 'SerializedMessageIndex' );
// $wgTranslateMessageIndex = array( 'CDBMessageIndex' );

/**
 * If you have lots of message groups, especially file based ones, and the
 * message index rebuilding gets slow, set this to true to delay the rebuilding
 * via JobQueue. This only makes sense if you have configured jobs to be
 * processed outside of requests via cron or similar.
 * @since 2012-05-03
 */
$wgTranslateDelayedMessageIndexRebuild = false;

# </source>
# === Unsorted ===
# <source lang=php>
/**
 * Pre-save transform for message pages. MediaWiki does this by default
 * (including for pages in the MediaWiki-namespace). By setting this to
 * false it allows users to put untransformed syntax such as "~~~~" into
 * a page without having them be transformed upon save.
 */
$wgTranslateUsePreSaveTransform = false;

/**
 * Default action for the RecentChanges filter, which makes it possible to filter
 * translations away or show them only.
 * Possible values: ('noaction', 'only', 'filter', 'site')
 */
$wgTranslateRcFilterDefault = 'filter';

/**
 * Set this to config like $wgTranslateTranslationServices if you want to run
 * SolrTTMServer tests.
 * @since 2013-01-04
 */
$wgTranslateTestTTMServer = null;

/**
 * List of user names that are allowed to alter their privileges and do other
 * things. Used for supporting integration testing.
 * @since 2013.10
 */
$wgTranslateTestUsers = [];

# </source>

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
