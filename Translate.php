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
 * @copyright Copyright © 2006-2014, Niklas Laxström, Siebrand Mazeland
 * @license GPL-2.0+
 */

/**
 * Version number used in extension credits and in other places where needed.
 */
define( 'TRANSLATE_VERSION', '2014-06-26' );

/**
 * Extension credits properties.
 */
$GLOBALS['wgExtensionCredits']['specialpage'][] = array(
	'path' => __FILE__,
	'name' => 'Translate',
	'version' => TRANSLATE_VERSION,
	'author' => array(
		'Niklas Laxström',
		'Santhosh Thottingal',
		'Siebrand Mazeland'
	),
	'descriptionmsg' => 'translate-desc',
	'url' => 'https://www.mediawiki.org/wiki/Extension:Translate',
);

/**
 * @cond file_level_code
 * Setup class autoloading.
 */
$dir = __DIR__;
require_once "$dir/Autoload.php";
/** @endcond */

/**
 * Registering various resources
 * @cond file_level_code
 */

$GLOBALS['wgMessagesDirs']['PageTranslation'] = __DIR__ . "/i18n/pagetranslation";
$GLOBALS['wgMessagesDirs']['Translate'] = __DIR__ . "/i18n/core";
$GLOBALS['wgMessagesDirs']['TranslateGroupDescriptions'] = __DIR__ . "/i18n/groupdescriptions";
$GLOBALS['wgMessagesDirs']['TranslateSearch'] = __DIR__ . "/i18n/search";
$GLOBALS['wgMessagesDirs']['TranslateSandbox'] = __DIR__ . "/i18n/sandbox";

// Register extension messages and other localisation.
$GLOBALS['wgExtensionMessagesFiles']['Translate'] = "$dir/Translate.i18n.php";
$GLOBALS['wgExtensionMessagesFiles']['PageTranslation'] = "$dir/PageTranslation.i18n.php";
$GLOBALS['wgExtensionMessagesFiles']['TranslateGroupDescriptions'] =
	"$dir/TranslateGroupDescriptions.i18n.php";
$GLOBALS['wgExtensionMessagesFiles']['TranslateSearch'] = "$dir/TranslateSearch.i18n.php";
$GLOBALS['wgExtensionMessagesFiles']['TranslateSandbox'] = "$dir/TranslateSandbox.i18n.php";
$GLOBALS['wgExtensionMessagesFiles']['TranslateAlias'] = "$dir/Translate.alias.php";

// Register initialization code
$GLOBALS['wgExtensionFunctions'][] = 'TranslateHooks::setupTranslate';
$GLOBALS['wgHooks']['CanonicalNamespaces'][] = 'TranslateHooks::setupNamespaces';
$GLOBALS['wgHooks']['UnitTestsList'][] = 'TranslateHooks::setupUnitTests';
$GLOBALS['wgHooks']['LoadExtensionSchemaUpdates'][] = 'TranslateHooks::schemaUpdates';
$GLOBALS['wgHooks']['ParserTestTables'][] = 'TranslateHooks::parserTestTables';
$GLOBALS['wgHooks']['PageContentLanguage'][] = 'TranslateHooks::onPageContentLanguage';

// Register special pages into MediaWiki
$GLOBALS['wgSpecialPages']['Translate'] = 'SpecialTranslate';
$GLOBALS['wgSpecialPageGroups']['Translate'] = 'wiki';
$GLOBALS['wgSpecialPages']['Translations'] = 'SpecialTranslations';
$GLOBALS['wgSpecialPageGroups']['Translations'] = 'pages';
// Disabled by default
// $GLOBALS['wgSpecialPages']['Magic'] = 'SpecialMagic';
$GLOBALS['wgSpecialPageGroups']['Magic'] = 'wiki';
$GLOBALS['wgSpecialPages']['TranslationStats'] = 'SpecialTranslationStats';
$GLOBALS['wgSpecialPageGroups']['TranslationStats'] = 'wiki';
$GLOBALS['wgSpecialPages']['LanguageStats'] = 'SpecialLanguageStats';
$GLOBALS['wgSpecialPageGroups']['LanguageStats'] = 'wiki';
$GLOBALS['wgSpecialPages']['MessageGroupStats'] = 'SpecialMessageGroupStats';
$GLOBALS['wgSpecialPageGroups']['MessageGroupStats'] = 'wiki';
$GLOBALS['wgSpecialPages']['ImportTranslations'] = 'SpecialImportTranslations';
$GLOBALS['wgSpecialPageGroups']['ImportTranslations'] = 'wiki';
$GLOBALS['wgSpecialPages']['ManageMessageGroups'] = 'SpecialManageGroups';
$GLOBALS['wgSpecialPageGroups']['ManageMessageGroups'] = 'wiki';
$GLOBALS['wgSpecialPages']['SupportedLanguages'] = 'SpecialSupportedLanguages';
$GLOBALS['wgSpecialPageGroups']['SupportedLanguages'] = 'wiki';

// Unlisted special page; does not need $wgSpecialPageGroups.
$GLOBALS['wgSpecialPages']['MyLanguage'] = 'SpecialMyLanguage';
$GLOBALS['wgSpecialPages']['AggregateGroups'] = 'SpecialAggregateGroups';
$GLOBALS['wgSpecialPageGroups']['AggregateGroups'] = 'wiki';
$GLOBALS['wgSpecialPages']['SearchTranslations'] = 'SpecialSearchTranslations';
$GLOBALS['wgSpecialPageGroups']['SearchTranslations'] = 'wiki';
$GLOBALS['wgSpecialPages']['ManageTranslatorSandbox'] = 'SpecialManageTranslatorSandbox';
$GLOBALS['wgSpecialPageGroups']['ManageTranslatorSandbox'] = 'users';
$GLOBALS['wgSpecialPages']['TranslationStash'] = 'SpecialTranslationStash';
$GLOBALS['wgSpecialPageGroups']['TranslationStash'] = 'wiki';

// API
$GLOBALS['wgAPIGeneratorModules']['messagecollection'] = 'ApiQueryMessageCollection';
$GLOBALS['wgAPIListModules']['messagecollection'] = 'ApiQueryMessageCollection';
$GLOBALS['wgAPIMetaModules']['languagestats'] = 'ApiQueryLanguageStats';
$GLOBALS['wgAPIMetaModules']['messagegroups'] = 'ApiQueryMessageGroups';
$GLOBALS['wgAPIMetaModules']['messagegroupstats'] = 'ApiQueryMessageGroupStats';
$GLOBALS['wgAPIMetaModules']['messagetranslations'] = 'ApiQueryMessageTranslations';
$GLOBALS['wgAPIModules']['aggregategroups'] = 'ApiAggregateGroups';
$GLOBALS['wgAPIModules']['groupreview'] = 'ApiGroupReview';
$GLOBALS['wgAPIModules']['hardmessages'] = 'ApiHardMessages';
$GLOBALS['wgAPIModules']['translatesandbox'] = 'ApiTranslateSandbox';
$GLOBALS['wgAPIModules']['translateuser'] = 'ApiTranslateUser';
$GLOBALS['wgAPIModules']['translationaids'] = 'ApiTranslationAids';
$GLOBALS['wgAPIModules']['translationreview'] = 'ApiTranslationReview';
$GLOBALS['wgAPIModules']['translationstash'] = 'ApiTranslationStash';
$GLOBALS['wgAPIModules']['ttmserver'] = 'ApiTTMServer';
$GLOBALS['wgHooks']['ApiTokensGetTokenTypes'][] = 'ApiTranslationReview::injectTokenFunction';
$GLOBALS['wgHooks']['ApiTokensGetTokenTypes'][] = 'ApiGroupReview::injectTokenFunction';
$GLOBALS['wgHooks']['ApiTokensGetTokenTypes'][] = 'ApiAggregateGroups::injectTokenFunction';
$GLOBALS['wgHooks']['ApiTokensGetTokenTypes'][] = 'ApiHardMessages::injectTokenFunction';
$GLOBALS['wgHooks']['ApiTokensGetTokenTypes'][] = 'ApiTranslateSandbox::injectTokenFunction';
$GLOBALS['wgHooks']['ApiTokensGetTokenTypes'][] = 'ApiTranslationStash::injectTokenFunction';

// Register hooks.
$GLOBALS['wgHooks']['EditPage::showEditForm:initial'][] = 'TranslateEditAddons::addTools';
$GLOBALS['wgHooks']['AlternateEdit'][] = 'TranslateEditAddons::intro';
$GLOBALS['wgHooks']['EditPageBeforeEditButtons'][] = 'TranslateEditAddons::buttonHack';
$GLOBALS['wgHooks']['LanguageGetTranslatedLanguageNames'][] =
	'TranslateHooks::translateMessageDocumentationLanguage';
$GLOBALS['wgHooks']['TranslateSupportedLanguages'][] =
	'TranslateHooks::translateMessageDocumentationLanguage';
$GLOBALS['wgHooks']['ArticlePrepareTextForEdit'][] = 'TranslateEditAddons::disablePreSaveTransform';
// Prevent translations creating bogus categories
$GLOBALS['wgHooks']['LinksUpdate'][] = 'TranslateHooks::preventCategorization';
// Fuzzy tags for speed.
$GLOBALS['wgHooks']['PageContentSaveComplete'][] = 'TranslateEditAddons::onSave';

$GLOBALS['wgHooks']['Translate:newTranslation'][] = 'TranslateEditAddons::updateTransverTag';

$GLOBALS['wgHooks']['SkinTemplateNavigation::SpecialPage'][] = 'SpecialTranslate::tabify';
$GLOBALS['wgHooks']['SkinTemplateNavigation::SpecialPage'][] = 'SpecialManageGroups::tabify';

// Custom preferences
$GLOBALS['wgDefaultUserOptions']['translate'] = 0;
$GLOBALS['wgDefaultUserOptions']['translate-editlangs'] = 'default';
$GLOBALS['wgDefaultUserOptions']['translate-recent-groups'] = '';
$GLOBALS['wgHooks']['GetPreferences'][] = 'TranslatePreferences::onGetPreferences';
$GLOBALS['wgHooks']['GetPreferences'][] = 'TranslatePreferences::translationAssistLanguages';

// Recent changes filters
$GLOBALS['wgHooks']['SpecialRecentChangesQuery'][] = 'TranslateRcFilter::translationFilter';
$GLOBALS['wgHooks']['SpecialRecentChangesPanel'][] = 'TranslateRcFilter::translationFilterForm';
$GLOBALS['wgHooks']['SkinTemplateToolboxEnd'][] = 'TranslateToolbox::toolboxAllTranslations';
$GLOBALS['wgHooks']['AbortEmailNotification'][] = 'TranslateHooks::onAbortEmailNotificationReview';

// Translation memory related
$GLOBALS['wgHooks']['ArticleDeleteComplete'][] = 'TTMServer::onDelete';
$GLOBALS['wgHooks']['TranslateEventMessageMembershipChange'][] = 'TTMServer::onGroupChange';

// Translation display related
$GLOBALS['wgHooks']['ArticleContentOnDiff'][] = 'TranslateEditAddons::displayOnDiff';

// Search profile
$GLOBALS['wgHooks']['SpecialSearchProfiles'][] = 'TranslateHooks::searchProfile';
$GLOBALS['wgHooks']['SpecialSearchProfileForm'][] = 'TranslateHooks::searchProfileForm';
$GLOBALS['wgHooks']['SpecialSearchSetupEngine'][] = 'TranslateHooks::searchProfileSetupEngine';

$GLOBALS['wgHooks']['LinkBegin'][] = 'TranslateHooks::linkfix';

// Stats table manipulation
$GLOBALS['wgHooks']['Translate:MessageGroupStats:isIncluded'][] =
	'TranslateHooks::hideDiscouragedFromStats';
$GLOBALS['wgHooks']['Translate:MessageGroupStats:isIncluded'][] =
	'TranslateHooks::hideRestrictedFromStats';

$GLOBALS['wgHooks']['MakeGlobalVariablesScript'][] = 'TranslateHooks::addConfig';

// Sandbox
$GLOBALS['wgDefaultUserOptions']['translate-sandbox'] = '';
$GLOBALS['wgHooks']['GetPreferences'][] = 'TranslateSandbox::onGetPreferences';
$GLOBALS['wgHooks']['UserGetRights'][] = 'TranslateSandbox::enforcePermissions';
$GLOBALS['wgHooks']['ApiCheckCanExecute'][] = 'TranslateSandbox::onApiCheckCanExecute';

// Internal event listeners
$GLOBALS['wgHooks']['TranslateEventTranslationEdit'][] = 'MessageGroupStats::clear';
$GLOBALS['wgHooks']['TranslateEventTranslationReview'][] = 'MessageGroupStats::clear';
$GLOBALS['wgHooks']['TranslateEventTranslationEdit'][] = 'MessageGroupStatesUpdaterJob::onChange';
$GLOBALS['wgHooks']['TranslateEventTranslationReview'][] = 'MessageGroupStatesUpdaterJob::onChange';

$GLOBALS['wgHooks']['AdminLinks'][] = 'TranslateHooks::onAdminLinks';

// New rights
$GLOBALS['wgAvailableRights'][] = 'translate';
$GLOBALS['wgAvailableRights'][] = 'translate-import';
$GLOBALS['wgAvailableRights'][] = 'translate-manage';
$GLOBALS['wgAvailableRights'][] = 'translate-messagereview';
$GLOBALS['wgAvailableRights'][] = 'translate-groupreview';
$GLOBALS['wgAvailableRights'][] = 'translate-sandboxmanage';

// New rights group
$GLOBALS['wgGroupPermissions']['translate-proofr']['translate-messagereview'] = true;
$GLOBALS['wgAddGroups']['translate-proofr'] = array( 'translate-proofr' );

// Logs. More logs are defined in TranslateHooks::setupTranslate
$GLOBALS['wgLogTypes'][] = 'translationreview';
$GLOBALS['wgLogActionsHandlers']['translationreview/message'] = 'TranslateLogFormatter';
$GLOBALS['wgLogActionsHandlers']['translationreview/group'] = 'TranslateLogFormatter';

$GLOBALS['wgLogTypes'][] = 'translatorsandbox';
$GLOBALS['wgLogActionsHandlers']['translatorsandbox/promoted'] = 'TranslateLogFormatter';
$GLOBALS['wgLogActionsHandlers']['translatorsandbox/rejected'] = 'TranslateLogFormatter';
$GLOBALS['wgLogActionsHandlers']['newusers/tsbpromoted'] = 'LogFormatter';

// New jobs
$GLOBALS['wgJobClasses']['MessageIndexRebuildJob'] = 'MessageIndexRebuildJob';
$GLOBALS['wgJobClasses']['MessageUpdateJob'] = 'MessageUpdateJob';
$GLOBALS['wgJobClasses']['MessageGroupStatesUpdaterJob'] = 'MessageGroupStatesUpdaterJob';
$GLOBALS['wgJobClasses']['TTMServerMessageUpdateJob'] = 'TTMServerMessageUpdateJob';
$GLOBALS['wgJobClasses']['TranslateSandboxEmailJob'] = 'TranslateSandboxEmailJob';

require "$dir/Resources.php";
/** @endcond */


# == Configuration variables ==

# === Basic configuration ===
# <source lang=php>
/**
 * Language code for message documentation. Suggested values are qqq or info.
 * If set to false (default), message documentation feature is disabled.
 */
$GLOBALS['wgTranslateDocumentationLanguageCode'] = false;

/**
 * Name of the bot which will invalidate translations and do maintenance
 * for page translation feature. Also used for importing messages from external
 * sources.
 */
$GLOBALS['wgTranslateFuzzyBotName'] = 'FuzzyBot';

/**
 * Add a preference "Do not send me email newsletters" in the email preferences.
 */
$GLOBALS['wgTranslateNewsletterPreference'] = false;

/**
 * Default values for list of languages to show translators as an aid when
 * translating. Each user can override this setting in their preferences.
 * Example:
 *  $wgTranslateLanguageFallbacks['fi'] = 'sv';
 *  $wgTranslateLanguageFallbacks['sv'] = array( 'da', 'no', 'nn' );
 */
$GLOBALS['wgTranslateLanguageFallbacks'] = array();

/**
 * Text that will be shown in translations if the translation is outdated.
 * Must be something that does not conflict with actual content.
 */
if ( !defined( 'TRANSLATE_FUZZY' ) ) {
	define( 'TRANSLATE_FUZZY', '!!FUZZY!!' );
}

/**
 * Define various web services that provide translation suggestions.
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
$GLOBALS['wgTranslateTranslationServices'] = array();
$GLOBALS['wgTranslateTranslationServices']['TTMServer'] = array(
	'database' => false, // Passed to wfGetDB
	'cutoff' => 0.75,
	'type' => 'ttmserver',
	'public' => false,
);
$GLOBALS['wgTranslateTranslationServices']['Microsoft'] = array(
	'url' => 'http://api.microsofttranslator.com/V2/Http.svc/Translate',
	'key' => null,
	'timeout' => 3,
	'type' => 'microsoft',
);
$GLOBALS['wgTranslateTranslationServices']['Apertium'] = array(
	'url' => 'http://api.apertium.org/json/translate',
	'pairs' => 'http://api.apertium.org/json/listPairs',
	'key' => null,
	'timeout' => 3,
	'type' => 'apertium',
);
$GLOBALS['wgTranslateTranslationServices']['Yandex'] = array(
	'url' => 'https://translate.yandex.net/api/v1.5/tr.json/translate',
	'key' => null,
	'pairs' => 'https://translate.yandex.net/api/v1.5/tr.json/getLangs',
	'timeout' => 3,
	'langorder' => array( 'en', 'ru', 'uk', 'de', 'fr', 'pl', 'it', 'es', 'tr' ),
	'langlimit' => 1,
	'type' => 'yandex',
);
/* Example configuration for remote TTMServer
$wgTranslateTranslationServices['example'] = array(
	'url' => 'http://example.com/w/api.php',
	'viewurl' => '//example.com/wiki/',
	'displayname' => 'example.com',
	'cutoff' => 0.75,
	'timeout-sync' => 4,
	'timeout-async' => 4,
	'type' => 'ttmserver',
	'class' => 'RemoteTTMServer',
);
*/

/**
 * List of tasks in Special:Translate. If you are only using page translation
 * feature, you might want to disable 'optional' task. Example:
 *  unset($wgTranslateTasks['optional']);
 */
$GLOBALS['wgTranslateTasks'] = array(
	'view' => 'ViewMessagesTask',
	'untranslated' => 'ViewUntranslatedTask',
	'optional' => 'ViewOptionalTask',
	'acceptqueue' => 'AcceptQueueMessagesTask',
	'reviewall' => 'ReviewAllMessagesTask',
	'export-as-po' => 'ExportasPoMessagesTask',
	'export-to-file' => 'ExportToFileMessagesTask',
	'custom' => 'CustomFilteredMessagesTask',
);

/**
 * Experimental support for Ask help button.
 * Might change into hook later on.
 * This is an array with keys page and params.
 * - page is a title of a local wiki page
 * - params is an array of key-value pairs of request params
 * -- param value can contain variable %MESSAGE% which will be replaced with
 *    full page name.
 * @since 2011-03-11
 */
$GLOBALS['wgTranslateSupportUrl'] = false;

/**
 * When unprivileged users open a translation editor, they will
 * see a message stating that a special permission is needed for translating
 * messages. If this variable is defined, there is a button which will
 * take the user to that page to ask for permission.
 * The target needs to be reiterated with the second variable to have
 * the same result with sandbox enabled where users can't enter the sandbox.
 */
$GLOBALS['wgTranslatePermissionUrl'] = 'Project:Translator';
$GLOBALS['wgTranslateSecondaryPermissionUrl'] = 'Project:Translator';

# </source>
# === Page translation feature ===
# <source lang=php>
/**
 * Enable page translation feature.
 *
 * Page translation feature allows structured translation of wiki pages
 * with simple markup and automatic tracking of changes.
 *
 * @defgroup PageTranslation Page Translation
 * @see http://translatewiki.net/wiki/Translating:Page_translation_feature
 */
$GLOBALS['wgEnablePageTranslation'] = true;

/**
 * Number for the Translations namespace. Change this if it conflicts with
 * other namespace in your wiki.
 */
$GLOBALS['wgPageTranslationNamespace'] = 1198;

/*
 * Enables the experimental page migration tools.
 * @since 2014-05
 */
$GLOBALS['wgTranslatePageMigration'] = true;

/**
 * Whether selecting a new interface language via ULS on a translatable page
 * also redirects the user to its translation page in the same language.
 * The language of following translation pages visited will still be controlled
 * by Special:MyLanguage (hence links not passing through it are not affected).
 * @since 2013-03-10
 */
$GLOBALS['wgTranslatePageTranslationULS'] = false;

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

$GLOBALS['wgTranslateBlacklist'] = array();

/**
 * File containing checks that are to be skipped. See
 * https://gerrit.wikimedia.org/r/gitweb?p=translatewiki.git;a=blob;f=check-blacklist.php;hb=HEAD
 * for example.
 *
 * @since 2012-10-15
 */
$GLOBALS['wgTranslateCheckBlacklist'] = false;

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
$GLOBALS['wgTranslateAuthorBlacklist'] = array();
$GLOBALS['wgTranslateAuthorBlacklist'][] = array( 'black', '/^.*;.*;.*Bot$/Ui' );

/**
 * List of namespace that contain messages. No talk namespaces.
 * @see https://www.mediawiki.org/wiki/Help:Extension:Translate/Group_configuration
 */
$GLOBALS['wgTranslateMessageNamespaces'] = array();

/**
 * CC = Custom classes.
 * Custom classes can register themselves here.
 * Key is always the group id, while the value is an message group object
 * or callable function.
 * @deprecated Use TranslatePostInitGroups hook instead.
 */
$GLOBALS['wgTranslateCC'] = array();

/**
 * Location in the filesystem to which paths are relative in custom groups.
 */
$GLOBALS['wgTranslateGroupRoot'] = '/var/www/externals';

/**
 * The newest and recommended way of adding custom groups is YAML files.
 * See examples under Translate/groups
 * Usage example:
 *  $wgTranslateGroupFiles[] = "$IP/extensions/Translate/groups/Shapado/Shapado.yml";
 */
$GLOBALS['wgTranslateGroupFiles'] = array();

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
$GLOBALS['wgTranslateWorkflowStates'] = false;

# </source>
# === System setup related configuration ===
# <source lang=php>
/**
 * Set location of cache files. Defaults to $wgCacheDirectory.
 */
$GLOBALS['wgTranslateCacheDirectory'] = false;

/**
 * Configures how the message index is stored.
 * The other backends need $wgCacheDirectory to be functional.
 */
$GLOBALS['wgTranslateMessageIndex'] = array( 'DatabaseMessageIndex' );
// $wgTranslateMessageIndex = array( 'SerializedMessageIndex' );
// $wgTranslateMessageIndex = array( 'CDBMessageIndex' );

/**
 * If you have lots of message groups, especially file based ones, and the
 * message index rebuilding gets slow, set this to true to delay the rebuilding
 * via JobQueue. This only makes sense if you have configured jobs to be
 * processed outside of requests via cron or similar.
 * @since 2012-05-03
 */
$GLOBALS['wgTranslateDelayedMessageIndexRebuild'] = false;

# </source>
# ==== PHPlot ====
# <source lang=php>
/**
 * For Special:TranslationStats PHPlot is needed to produce graphs.
 * Set this the location of phplot.php.
 */
$GLOBALS['wgTranslatePHPlot'] = false;

/**
 * The default font for PHPlot for drawing text. Only used if the automatic
 * best font selection fails. The automatic best font selector uses language
 * code to call fc-match program. If you have open_basedir restriction or
 * safe-mode, using the found font is likely to fail. In this case you need
 * to change the code to use hard-coded font, or copy fonts to location PHP
 * can access them, and make sure fc-match returns only those fonts.
 */
$GLOBALS['wgTranslatePHPlotFont'] = '/usr/share/fonts/truetype/ttf-dejavu/DejaVuSans.ttf';

# </source>
# ==== YAML driver ====
# <source lang=php>
/**
 * Currently supported YAML drivers are spyc and syck and sycl-pecl.
 *
 * For syck we're shelling out to perl. So you need:
 *
 * * At least perl 5.8 (find out what you have by running "perl -v")
 * * Install these modules from CPAN
 *   * YAML::Syck
 *   * PHP::Serialization.
 *   * File::Slurp
 *
 * You should be able to do this with:
 *   for module in 'YAML::Syck' 'PHP::Serialization' 'File::Slurp'; do cpanp -i $module; done
 *
 * For the shell to work, you also need an en.UTF-8 locale installed on your system.
 * add a line "en.UTF-8" to your /etc/locale.gen or uncomment an existing one and run locale-gen
 * if you do not have it already.
 *
 * For syck-pecl you need libsyck and pecl install syck-beta
 */
$GLOBALS['wgTranslateYamlLibrary'] = 'spyc';

# </source>
# ==== Sandbox ====
# <source lang=php>

/**
 * Whether to allow users to sign up via a sandbox. Sandboxed users cannot do
 * much until approved and thus they can be get rid of easily. Only works with
 * MediaWiki 1.22 or newer.
 * @since 2013.04
 */
$GLOBALS['wgTranslateUseSandbox'] = false;

/**
 * To which group the translators are promoted. If left at false, they will just
 * be removed from sandbox and become normal users.
 * @since 2013.04
 */
$GLOBALS['wgTranslateSandboxPromotedGroup'] = false;

/**
 * List of page names to always suggest for sandboxed users.
 * @since 2013.10
 */
$GLOBALS['wgTranslateSandboxSuggestions'] = array();

/**
 * Maximum number of translations a user can make in the sandbox.
 * @since 2013.10
 */
$GLOBALS['wgTranslateSandboxLimit'] = 20;

# </source>
# === Unsorted ===
# <source lang=php>
/**
 * Pre-save transform for message pages. MediaWiki does this by default
 * (including for pages in the MediaWiki-namespace). By setting this to
 * false it allows users to put untransformed syntax such as "~~~~" into
 * a page without having them be transformed upon save.
 */
$GLOBALS['wgTranslateUsePreSaveTransform'] = false;

/**
 * Default action for the RecentChanges filter, which makes it possible to filter
 * translations away or show them only.
 * Possible values: ('noaction', 'only', 'filter', 'site')
 */
$GLOBALS['wgTranslateRcFilterDefault'] = 'filter';

/**
 * Set this to config like $wgTranslateTranslationServices if you want to run
 * SolrTTMServer tests.
 * @since 2013-01-04
 */
$GLOBALS['wgTranslateTestTTMServer'] = null;

/**
 * Whether to use the TUX interface by default. tux=1 and tux=0 in the url can
 * be used to switch between old and new. This variable will be removed after
 * transition time.
 */
$GLOBALS['wgTranslateUseTux'] = true;

/**
 * List of user names that are allowed to alter their privileges and do other
 * things. Used for supporting integration testing.
 * @since 2013.10
 */
$GLOBALS['wgTranslateTestUsers'] = array();

# </source>

/**
 * Helper function for adding namespace for message groups.
 *
 * It defines constants for the namespace (and talk namespace) and sets up
 * restrictions and some other configuration.
 * @param $id \int Namespace number
 * @param $name \string Name of the namespace
 */
function wfAddNamespace( $id, $name ) {
	global $wgExtraNamespaces, $wgContentNamespaces, $wgTranslateMessageNamespaces,
		$wgNamespaceProtection, $wgNamespacesWithSubpages, $wgNamespacesToBeSearchedDefault;

	$constant = strtoupper( "NS_$name" );

	define( $constant, $id );
	define( $constant . '_TALK', $id + 1 );

	$wgExtraNamespaces[$id] = $name;
	$wgExtraNamespaces[$id + 1] = $name . '_talk';

	$wgContentNamespaces[] = $id;
	$wgTranslateMessageNamespaces[] = $id;

	$wgNamespacesWithSubpages[$id] = true;
	$wgNamespacesWithSubpages[$id + 1] = true;

	$wgNamespaceProtection[$id] = array( 'translate' );

	$wgNamespacesToBeSearchedDefault[$id] = true;
}

/** @defgroup TranslateSpecialPage Special pages of Translate extension */
