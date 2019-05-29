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
# === Message group configuration ===
# <source lang=php>

/**
 * Two-dimensional array of languages that cannot be translated.
 * Input can be exact group name, first part before '-' or '*' for all.
 * Second dimension should be language code mapped to reason for disabling.
 * Reason is parsed as wikitext.
 *
 * Example:
 * $wgTranslateBlacklist = array(
 *     '*' => array( // All groups
 *         'en' => 'English is the source language.',
 *     ),
 *     'core' => array( // Exact group
 *         'mul' => 'Not a real language.',
 *     ),
 *     'ext' => array( // Wildcard-like group
 *         'mul' => 'Not a real language',
 *     ),
 * );
 */

$wgTranslateBlacklist = [];

/**
 * File containing checks that are to be skipped. See
 * https://gerrit.wikimedia.org/r/gitweb?p=translatewiki.git;a=blob;f=check-blacklist.php;hb=HEAD
 * for example.
 *
 * @since 2012-10-15
 */
$wgTranslateCheckBlacklist = false;

/**
 * Two-dimensional array of rules that blacklists certain authors from appearing
 * in the exports. This is useful for keeping bots and people doing maintenance
 * work in translations not to appear besides real translators everywhere.
 *
 * Rules are arrays, where first element is type: white or black. Whitelisting
 * always overrules blacklisting. Second element should be a valid pattern that
 * can be given a preg_match(). It will be matched against string of format
 * "group-id;language;author name", without quotes.
 * As an example by default we have rule that ignores all authors whose name
 * ends in a bot for all languages and all groups.
 */
$wgTranslateAuthorBlacklist = [];
$wgTranslateAuthorBlacklist[] = [ 'black', '/^.*;.*;.*Bot$/Ui' ];

/**
 * List of namespace that contain messages. No talk namespaces.
 * @see https://www.mediawiki.org/wiki/Help:Extension:Translate/Group_configuration
 */
$wgTranslateMessageNamespaces = [];

/**
 * CC = Custom classes.
 * Custom classes can register themselves here.
 * Key is always the group id, while the value is an message group object
 * or callable function.
 * @deprecated Use TranslatePostInitGroups hook instead.
 */
$wgTranslateCC = [];

/**
 * Location in the filesystem to which paths are relative in custom groups.
 */
$wgTranslateGroupRoot = '/var/www/externals';

/**
 * The newest and recommended way of adding custom groups is YAML files.
 * See examples under Translate/groups
 * Usage example:
 *  $wgTranslateGroupFiles[] = "$IP/extensions/Translate/groups/Shapado/Shapado.yml";
 */
$wgTranslateGroupFiles = [];

/**
 * List of possible message group review workflow states and properties
 * for each state.
 * The currently supported properties are:
 * * color: the color that is used for displaying the state in the tables.
 * * right: additional right that is needed to set the state.
 * Users who have the translate-groupreview right can set this in
 * Special:Translate.
 * The state is visible in Special:Translate, Special:MessageGroupStats and
 * Special:LanguageStats.
 * If the value is false, the workflow states feature is disabled.
 * State name can be up to 32 characters maximum.
 * Example:
 * $wgTranslateWorkflowStates = array(
 *      'new' => array( 'color' => 'FF0000' ), // red
 *      'needs_proofreading' => array( 'color' => '0000FF' ), // blue
 *      'ready' => array( 'color' => 'FFFF00' ), // yellow
 *      'published' => array(
 *          'color' => '00FF00', // green
 *          'right' => 'centralnotice-admin',
 *      ),
 * );
 */
$wgTranslateWorkflowStates = false;

/**
 * Map from deprecated group IDs to their current ID
 * Example value: array( 'core' => 'mediawiki-core' )
 */
$wgTranslateGroupAliases = [];

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
# ==== PHPlot ====
# <source lang=php>
/**
 * For Special:TranslationStats PHPlot is needed to produce graphs.
 * Set this the location of phplot.php.
 */
$wgTranslatePHPlot = false;

/**
 * The default font for PHPlot for drawing text. Only used if the automatic
 * best font selection fails. The automatic best font selector uses language
 * code to call fc-match program. If you have open_basedir restriction or
 * safe-mode, using the found font is likely to fail. In this case you need
 * to change the code to use hard-coded font, or copy fonts to location PHP
 * can access them, and make sure fc-match returns only those fonts.
 */
$wgTranslatePHPlotFont = '/usr/share/fonts/truetype/ttf-dejavu/DejaVuSans.ttf';

# </source>
# ==== Sandbox ====
# <source lang=php>

/**
 * Whether to allow users to sign up via a sandbox. Sandboxed users cannot do
 * much until approved and thus they can be get rid of easily. This only works
 * with MediaWiki 1.27 and newer, and only if registration is configured to not
 * use account creation providers which give REDIRECT or UI responses or
 * require any other field than the default username/password/email.
 * @since 2013.04
 */
$wgTranslateUseSandbox = false;

/**
 * To which group the translators are promoted. If left at false, they will just
 * be removed from sandbox and become normal users.
 * @since 2013.04
 */
$wgTranslateSandboxPromotedGroup = false;

/**
 * List of page names to always suggest for sandboxed users.
 * @since 2013.10
 */
$wgTranslateSandboxSuggestions = [];

/**
 * Maximum number of translations a user can make in the sandbox.
 * @since 2013.10
 */
$wgTranslateSandboxLimit = 20;

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
