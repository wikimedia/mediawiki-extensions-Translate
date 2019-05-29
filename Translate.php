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
