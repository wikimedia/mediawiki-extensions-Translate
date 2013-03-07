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
 * @copyright Copyright © 2006-2013, Niklas Laxström, Siebrand Mazeland
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

/**
 * Version number used in extension credits and in other places where needed.
 */
define( 'TRANSLATE_VERSION', '2013-03-07' );

/**
 * Extension credits properties.
 */
$wgExtensionCredits['specialpage'][] = array(
	'path' => __FILE__,
	'name' => 'Translate',
	'version' => TRANSLATE_VERSION,
	'author' => array( 'Niklas Laxström', 'Siebrand Mazeland' ),
	'descriptionmsg' => 'translate-desc',
	'url' => 'https://www.mediawiki.org/wiki/Extension:Translate',
);

/**
 * @cond file_level_code
 * Setup class autoloading.
 */
$dir = dirname( __FILE__ );
require_once( "$dir/_autoload.php" );
/** @endcond */

/**
 * @cond file_level_code
 */

// Register extension messages and other localisation.
$wgExtensionMessagesFiles['Translate'] = "$dir/Translate.i18n.php";
$wgExtensionMessagesFiles['FirstSteps'] = "$dir/FirstSteps.i18n.php";
$wgExtensionMessagesFiles['PageTranslation'] = "$dir/PageTranslation.i18n.php";
$wgExtensionMessagesFiles['TranslateGroupDescriptions'] = "$dir/TranslateGroupDescriptions.i18n.php";
$wgExtensionMessagesFiles['TranslateSearch'] = "$dir/TranslateSearch.i18n.php";
$wgExtensionMessagesFiles['TranslateAlias'] = "$dir/Translate.alias.php";
$wgExtensionMessagesFiles['TranslateMagic'] = "$dir/Translate.magic.php";

// Register initialization code
$wgExtensionFunctions[] = 'TranslateHooks::setupTranslate';
$wgHooks['CanonicalNamespaces'][] = 'TranslateHooks::setupNamespaces';
$wgHooks['UnitTestsList'][] = 'TranslateHooks::setupUnitTests';
$wgHooks['LoadExtensionSchemaUpdates'][] = 'TranslateHooks::schemaUpdates';
$wgHooks['ParserTestTables'][] = 'TranslateHooks::parserTestTables';
$wgHooks['PageContentLanguage'][] = 'TranslateHooks::onPageContentLanguage';

// Register special pages into MediaWiki
$wgSpecialPages['Translate'] = 'SpecialTranslate';
$wgSpecialPageGroups['Translate'] = 'wiki';
$wgSpecialPages['Translations'] = 'SpecialTranslations';
$wgSpecialPageGroups['Translations'] = 'pages';
// Disabled by default
// $wgSpecialPages['Magic'] = 'SpecialMagic';
$wgSpecialPageGroups['Magic'] = 'wiki';
$wgSpecialPages['TranslationStats'] = 'SpecialTranslationStats';
$wgSpecialPageGroups['TranslationStats'] = 'wiki';
$wgSpecialPages['LanguageStats'] = 'SpecialLanguageStats';
$wgSpecialPageGroups['LanguageStats'] = 'wiki';
$wgSpecialPages['MessageGroupStats'] = 'SpecialMessageGroupStats';
$wgSpecialPageGroups['MessageGroupStats'] = 'wiki';
$wgSpecialPages['ImportTranslations'] = 'SpecialImportTranslations';
$wgSpecialPageGroups['ImportTranslations'] = 'wiki';
$wgSpecialPages['ManageMessageGroups'] = 'SpecialManageGroups';
$wgSpecialPageGroups['ManageMessageGroups'] = 'wiki';
// Unlisted special page; does not need $wgSpecialPageGroups.
$wgSpecialPages['FirstSteps'] = 'SpecialFirstSteps';
// Unlisted special page; does not need $wgSpecialPageGroups.
$wgSpecialPages['SupportedLanguages'] = 'SpecialSupportedLanguages';
// Unlisted special page; does not need $wgSpecialPageGroups.
$wgSpecialPages['MyLanguage'] = 'SpecialMyLanguage';
$wgSpecialPages['AggregateGroups'] = 'SpecialAggregateGroups';
$wgSpecialPageGroups['AggregateGroups'] = 'wiki';

$wgSpecialPages['SearchTranslations'] = 'SpecialSearchTranslations';

// API
$wgAPIListModules['messagecollection'] = 'ApiQueryMessageCollection';
$wgAPIMetaModules['languagestats'] = 'ApiQueryLanguageStats';
$wgAPIMetaModules['messagegroups'] = 'ApiQueryMessageGroups';
$wgAPIMetaModules['messagegroupstats'] = 'ApiQueryMessageGroupStats';
$wgAPIMetaModules['messagetranslations'] = 'ApiQueryMessageTranslations';
$wgAPIModules['translationreview'] = 'ApiTranslationReview';
$wgAPIModules['groupreview'] = 'ApiGroupReview';
$wgAPIModules['aggregategroups'] = 'ApiAggregateGroups';
$wgAPIModules['ttmserver'] = 'ApiTTMServer';
$wgAPIModules['translateuser'] = 'ApiTranslateUser';
$wgAPIModules['translationaids'] = 'ApiTranslationAids';
$wgAPIModules['hardmessages'] = 'ApiHardMessages';
$wgAPIGeneratorModules['messagecollection'] = 'ApiQueryMessageCollection';

// Before MW 1.20
$wgHooks['APIQueryInfoTokens'][] = 'ApiTranslationReview::injectTokenFunction';
$wgHooks['APIQueryInfoTokens'][] = 'ApiGroupReview::injectTokenFunction';
$wgHooks['APIQueryInfoTokens'][] = 'ApiAggregateGroups::injectTokenFunction';
// After MW 1.20
$wgHooks['ApiTokensGetTokenTypes'][] = 'ApiTranslationReview::injectTokenFunction';
$wgHooks['ApiTokensGetTokenTypes'][] = 'ApiGroupReview::injectTokenFunction';
$wgHooks['ApiTokensGetTokenTypes'][] = 'ApiAggregateGroups::injectTokenFunction';
$wgHooks['ApiHardMessages'][] = 'ApiHardMessages::injectTokenFunction';
// Register hooks.
$wgHooks['EditPage::showEditForm:initial'][] = 'TranslateEditAddons::addTools';
$wgHooks['SkinTemplateTabs'][] = 'TranslateEditAddons::addNavigationTabs';
// Same for Vector skin
$wgHooks['SkinTemplateNavigation'][] = 'TranslateEditAddons::addNavigationTabs';
$wgHooks['AlternateEdit'][] = 'TranslateEditAddons::intro';
$wgHooks['EditPageBeforeEditButtons'][] = 'TranslateEditAddons::buttonHack';
$wgHooks['EditPage::showEditForm:fields'][] = 'TranslateEditAddons::keepFields';
$wgHooks['SkinTemplateTabs'][] = 'TranslateEditAddons::tabs';
$wgHooks['LanguageGetTranslatedLanguageNames'][] = 'TranslateHooks::translateMessageDocumentationLanguage';
$wgHooks['ArticlePrepareTextForEdit'][] = 'TranslateEditAddons::disablePreSaveTransform';
// Prevent translations creating bogus categories
$wgHooks['LinksUpdate'][] = 'TranslateHooks::preventCategorization';
// Fuzzy tags for speed.
if ( !defined( 'MW_SUPPORTS_CONTENTHANDLER' ) ) {
	// BC 1.20
	$wgHooks['ArticleSaveComplete'][] = 'TranslateEditAddons::onSave';
} else {
	$wgHooks['PageContentSaveComplete'][] = 'TranslateEditAddons::onSave';
}

$wgHooks['Translate:newTranslation'][] = 'TranslateEditAddons::updateTransverTag';

$wgHooks['SkinTemplateNavigation::SpecialPage'][] = 'SpecialTranslate::tabify';
$wgHooks['SkinTemplateNavigation::SpecialPage'][] = 'SpecialManageGroups::tabify';

// Custom preferences
$wgDefaultUserOptions['translate'] = 0;
$wgDefaultUserOptions['translate-editlangs'] = 'default';
$wgDefaultUserOptions['translate-jsedit'] = 1;
$wgDefaultUserOptions['translate-recent-groups'] = '';
$wgHooks['GetPreferences'][] = 'TranslatePreferences::onGetPreferences';
$wgHooks['GetPreferences'][] = 'TranslatePreferences::translationAssistLanguages';
$wgHooks['GetPreferences'][] = 'TranslatePreferences::translationJsedit';

// Recent changes filters
$wgHooks['SpecialRecentChangesQuery'][] = 'TranslateRcFilter::translationFilter';
$wgHooks['SpecialRecentChangesPanel'][] = 'TranslateRcFilter::translationFilterForm';
$wgHooks['SkinTemplateToolboxEnd'][] = 'TranslateToolbox::toolboxAllTranslations';

// Translation memory related
$wgHooks['ArticleDeleteComplete'][] = 'TTMServer::onDelete';
$wgHooks['TranslateEventMessageMembershipChange'][] = 'TTMServer::onGroupChange';

// Translation display related
$wgHooks['ArticleContentOnDiff'][] = 'TranslateEditAddons::displayOnDiff';

// Search profile
$wgHooks['SpecialSearchProfiles'][] = 'TranslateHooks::searchProfile';
$wgHooks['SpecialSearchProfileForm'][] = 'TranslateHooks::searchProfileForm';
$wgHooks['SpecialSearchSetupEngine'][] = 'TranslateHooks::searchProfileSetupEngine';

$wgHooks['LinkBegin'][] = 'SpecialMyLanguage::linkfix';

// Stats table manipulation
$wgHooks['Translate:MessageGroupStats:isIncluded'][] = 'TranslateHooks::hideDiscouragedFromStats';
$wgHooks['Translate:MessageGroupStats:isIncluded'][] = 'TranslateHooks::hideRestrictedFromStats';

$wgHooks['MakeGlobalVariablesScript'][] = 'TranslateHooks::addConfig';

// Internal event listeners
$wgHooks['TranslateEventTranslationEdit'][] = 'MessageGroupStats::clear';
$wgHooks['TranslateEventTranslationReview'][] = 'MessageGroupStats::clear';
$wgHooks['TranslateEventTranslationEdit'][] = 'MessageGroupStatesUpdaterJob::onChange';
$wgHooks['TranslateEventTranslationReview'][] = 'MessageGroupStatesUpdaterJob::onChange';

// New rights
$wgAvailableRights[] = 'translate';
$wgAvailableRights[] = 'translate-import';
$wgAvailableRights[] = 'translate-manage';
$wgAvailableRights[] = 'translate-messagereview';
$wgAvailableRights[] = 'translate-groupreview';

// New rights group
$wgGroupPermissions['translate-proofr']['translate-messagereview'] = true;
$wgAddGroups['translate-proofr'] = array( 'translate-proofr' );

// Logs. More logs are defined in TranslateHooks::setupTranslate
$wgLogTypes[] = 'translationreview';
$wgLogActionsHandlers['translationreview/message'] = 'TranslateLogFormatter';
$wgLogActionsHandlers['translationreview/group'] = 'TranslateLogFormatter';

// New jobs
$wgJobClasses['MessageIndexRebuildJob'] = 'MessageIndexRebuildJob';
$wgJobClasses['MessageUpdateJob'] = 'MessageUpdateJob';
$wgJobClasses['MessageGroupStatesUpdaterJob'] = 'MessageGroupStatesUpdaterJob';
$wgJobClasses['TTMServerMessageUpdateJob'] = 'TTMServerMessageUpdateJob';

$resourcePaths = array(
	'localBasePath' => dirname( __FILE__ ),
	'remoteExtPath' => 'Translate'
);

// Client-side resource modules

$wgResourceModules['ext.translate.base'] = array(
	'scripts' => 'resources/js/ext.translate.base.js',
	'dependencies' => array(
		'mediawiki.util',
		'mediawiki.api',
	),
) + $resourcePaths;

$wgResourceModules['ext.translate'] = array(
	'styles' => 'resources/css/ext.translate.css',
	'position' => 'top',
) + $resourcePaths;

$wgResourceModules['ext.translate.hooks'] = array(
	'scripts' => 'resources/js/ext.translate.hooks.js',
	'position' => 'top',
) + $resourcePaths;

$wgResourceModules['ext.translate.helplink'] = array(
	'styles' => 'resources/css/ext.translate.helplink.css',
	'position' => 'top',
) + $resourcePaths;

// TODO: jquery.uls uses the same grid system. So don't duplicate
$wgResourceModules['ext.translate.grid'] = array(
	'styles' => 'resources/css/ext.translate.grid.css',
	'position' => 'top',
) + $resourcePaths;

$wgResourceModules['ext.translate.editor'] = array(
	'scripts' => array(
		'resources/js/ext.translate.editor.js',
		'resources/js/ext.translate.editor.helpers.js',
		'resources/js/ext.translate.proofread.js',
	),
	'styles' => array(
		'resources/css/ext.translate.editor.css',
		'resources/css/ext.translate.proofread.css',
	),
	'dependencies' => array(
		'ext.translate.base',
		'ext.translate.grid',
		'mediawiki.util',
		'mediawiki.Uri',
		'mediawiki.api',
		'mediawiki.api.parse',
		'mediawiki.user',
		'mediawiki.jqueryMsg',
		'jquery.makeCollapsible',
		'jquery.tipsy',
	),
	'messages' => array(
		'tux-status-translated',
		'tux-status-saving',
		'tux-status-unsaved',
		'tux-editor-placeholder',
		'tux-editor-paste-original-button-label',
		'tux-editor-save-button-label',
		'tux-editor-skip-button-label',
		'tux-editor-confirm-button-label',
		'tux-editor-shortcut-info',
		'tux-editor-edit-desc',
		'tux-editor-add-desc',
		'tux-editor-message-desc-more',
		'tux-editor-message-desc-less',
		'tux-editor-suggestions-title',
		'tux-editor-in-other-languages',
		'tux-editor-need-more-help',
		'tux-editor-ask-help',
		'tux-editor-tm-match',
		'tux-warnings-more',
		'tux-warnings-hide',
		'tux-editor-save-failed',
		'tux-editor-use-this-translation',
		'tux-editor-n-uses',
		'tux-editor-doc-editor-placeholder',
		'tux-editor-doc-editor-save',
		'tux-editor-doc-editor-cancel',
		'translate-edit-nopermission',
		'translate-edit-askpermission',
		'tux-editor-outdated-warning',
		'tux-editor-outdated-warning-diff-link',
		'tux-proofread-action-tooltip',
		'tux-proofread-edit-tooltip',
		'tux-editor-close-tooltip',
		'tux-editor-expand-tooltip',
		'tux-editor-collapse-tooltip',
		'tux-editor-loading',
	),
	'position' => 'top',
) + $resourcePaths;

$wgResourceModules['ext.translate.groupselector'] = array(
	'styles' => 'resources/css/ext.translate.groupselector.css',
	'scripts' => 'resources/js/ext.translate.groupselector.js',
	'position' => 'top',
	'dependencies' => array(
		'ext.translate.grid',
		'ext.translate.statsbar',
		'mediawiki.jqueryMsg',
	),
	'messages' => array(
		'translate-msggroupselector-projects',
		'translate-msggroupselector-search-placeholder',
		'translate-msggroupselector-search-all',
		'translate-msggroupselector-search-recent',
		'translate-msggroupselector-view-subprojects',
	),
) + $resourcePaths;

$wgResourceModules['ext.translate.messagetable'] = array(
	'scripts' => 'resources/js/ext.translate.messagetable.js',
	'styles' => 'resources/css/ext.translate.messagetable.css',
	'position' => 'top',
	'dependencies' => array(
		'mediawiki.util',
		'jquery.appear',
		'mediawiki.jqueryMsg',
	),
	'messages' => array(
		'translate-messagereview-submit',
		'translate-messagereview-progress',
		'translate-messagereview-failure',
		'translate-messagereview-done',
		'api-error-badtoken',
		'api-error-emptypage',
		'api-error-fuzzymessage',
		'api-error-invalidrevision',
		'api-error-owntranslation',
		'api-error-unknownmessage',
		'api-error-unknownerror',
		'tpt-unknown-page',
		'tux-edit',
		'tux-status-fuzzy',
		'tux-status-optional',
		'tux-status-translated',
		'tux-status-proofread',
		'translate-edit-title',
		'tux-messagetable-more-messages',
		'tux-messagetable-loading-messages',
		'tux-message-filter-result',
		'tux-message-filter-advanced-button',
		'tux-empty-list-all',
		'tux-empty-list-all-guide',
		'tux-empty-list-translated',
		'tux-empty-list-translated-guide',
		'tux-empty-list-translated-action',
		'tux-empty-list-other',
		'tux-empty-list-other-guide',
		'tux-empty-list-other-action',
		'tux-empty-list-other-link',
		'translate-language-disabled',
	),
) + $resourcePaths;

$wgResourceModules['ext.translate.statsbar'] = array(
	'styles' => 'resources/css/ext.translate.statsbar.css',
	'scripts' => 'resources/js/ext.translate.statsbar.js',
	'position' => 'top',
) + $resourcePaths;

$wgResourceModules['ext.translate.tabgroup'] = array(
	'styles' => 'resources/css/ext.translate.tabgroup.css',
	'position' => 'top',
) + $resourcePaths;

$wgResourceModules['ext.translate.quickedit'] = array(
	'scripts' => 'resources/js/ext.translate.quickedit.js',
	'styles' => 'resources/css/ext.translate.quickedit.css',
	'messages' => array( 'translate-js-nonext', 'translate-js-save-failed' ),
	'dependencies' => array(
		'ext.translate.hooks',
		'jquery.form',
		'jquery.ui.dialog',
		'jquery.autoresize',
		'mediawiki.util',
	),
) + $resourcePaths;

$wgResourceModules['ext.translate.selecttoinput'] = array(
	'scripts' => 'resources/js/ext.translate.selecttoinput.js',
) + $resourcePaths;

$wgResourceModules['ext.translate.special.importtranslations'] = array(
	'scripts' => 'resources/js/ext.translate.special.importtranslations.js',
	'dependencies' => array(
		'jquery.ui.autocomplete',
	),
) + $resourcePaths;

$wgResourceModules['ext.translate.messagewebimporter'] = array(
	'styles' => 'resources/css/ext.translate.messagewebimporter.css',
	'position' => 'top',
) + $resourcePaths;

$wgResourceModules['ext.translate.special.languagestats'] = array(
	'scripts' => 'resources/js/ext.translate.special.languagestats.js',
	'styles' => 'resources/css/ext.translate.special.languagestats.css',
	'messages' => array(
		'translate-langstats-expandall',
		'translate-langstats-collapseall',
		'translate-langstats-expand',
		'translate-langstats-collapse'
	),
	'dependencies' => 'jquery.tablesorter',
) + $resourcePaths;

$wgResourceModules['ext.translate.multiselectautocomplete'] = array(
	'scripts' => 'resources/js/ext.translate.multiselectautocomplete.js',
	'dependencies' => array(
		'jquery.ui.autocomplete',
	),
	'position' => 'top',
) + $resourcePaths;

$wgResourceModules['ext.translate.special.managegroups'] = array(
	'styles' => 'resources/css/ext.translate.special.managegroups.css',
	'position' => 'top',
) + $resourcePaths;

$wgResourceModules['ext.translate.special.pagetranslation'] = array(
	'scripts' => 'resources/js/ext.translate.special.pagetranslation.js',
	'styles' => 'resources/css/ext.translate.special.pagetranslation.css',
	'dependencies' => array(
		'ext.translate.multiselectautocomplete',
	),
	'position' => 'top',
) + $resourcePaths;

$wgResourceModules['ext.translate.special.translationstats'] = array(
	'scripts' => 'resources/js/ext.translate.special.translationstats.js',
	'dependencies' => array(
		'jquery.ui.datepicker',
	),
) + $resourcePaths;

$wgResourceModules['ext.translate.special.aggregategroups'] = array(
	'scripts' => 'resources/js/ext.translate.special.aggregategroups.js',
	'styles' => 'resources/css/ext.translate.special.aggregategroups.css',
	'position' => 'top',
	'dependencies' => array( 'mediawiki.util' ),
	'messages' => array(
		'tpt-aggregategroup-remove-confirm',
	),
) + $resourcePaths;

$wgResourceModules['ext.translate.special.supportedlanguages'] = array(
	'styles' => 'resources/css/ext.translate.special.supportedlanguages.css',
	'position' => 'top',
) + $resourcePaths;

$wgResourceModules['ext.translate.special.searchtranslations'] = array(
	'scripts' => 'resources/js/ext.translate.special.searchtranslations.js',
	'styles' => 'resources/css/ext.translate.special.searchtranslations.css',
	'dependencies' => array( 'ext.translate.editor' ),
	'position' => 'top',
) + $resourcePaths;

$wgResourceModules['ext.translate.special.translate'] = array(
	'styles' => 'resources/css/ext.translate.special.translate.css',
	'scripts' => 'resources/js/ext.translate.special.translate.js',
	'position' => 'top',
	'dependencies' => array(
		'mediawiki.jqueryMsg',
		'mediawiki.Uri',
		'mediawiki.api.parse',
		'ext.translate.base',
		'ext.translate.groupselector',
		'ext.translate.messagetable',
	),
	'messages' => array(
		'translate-workflow-set-do',
		'translate-workflow-set-doing',
		'translate-workflow-set-done',
		'translate-workflowstatus',
		'translate-workflow-set-error-alreadyset',
		'translate-js-support-unsaved-warning',
		'translate-documentation-language',
		'translate-workflow-state-',
		'tpt-discouraged-language-force',
		'tpt-discouraged-language',
		'tux-editor-proofreading-hide-own-translations',
		'tux-editor-proofreading-show-own-translations',
	),
) + $resourcePaths;

$wgResourceModules['jquery.autoresize'] = array(
	'scripts' => 'resources/js/jquery.autoresize.js',
) + $resourcePaths;

/** @endcond */


# == Configuration variables ==

# === Basic configuration ===
# <source lang=php>
/**
 * Language code for message documentation. Suggested values are qqq or info.
 * If set to false (default), message documentation feature is disabled.
 */
$wgTranslateDocumentationLanguageCode = false;

/**
 * Name of the bot which will invalidate translations and do maintenance
 * for page translation feature. Also used for importing messages from external
 * sources.
 */
$wgTranslateFuzzyBotName = 'FuzzyBot';

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
$wgTranslateLanguageFallbacks = array();

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
$wgTranslateTranslationServices = array();
$wgTranslateTranslationServices['TTMServer'] = array(
	'database' => false, // Passed to wfGetDB
	'cutoff' => 0.75,
	'type' => 'ttmserver',
	'public' => false,
);
$wgTranslateTranslationServices['Microsoft'] = array(
	'url' => 'http://api.microsofttranslator.com/V2/Http.svc/Translate',
	'key' => null,
	'timeout' => 3,
	'type' => 'microsoft',
);
$wgTranslateTranslationServices['Apertium'] = array(
	'url' => 'http://api.apertium.org/json/translate',
	'pairs' => 'http://api.apertium.org/json/listPairs',
	'key' => null,
	'timeout' => 3,
	'type' => 'apertium',
);
$wgTranslateTranslationServices['Yandex'] = array(
	'url' => 'http://translate.yandex.net/api/v1/tr.json/translate',
	'pairs' => 'http://translate.yandex.net/api/v1/tr.json/getLangs',
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
$wgTranslateTasks = array(
	'view' => 'ViewMessagesTask',
	'untranslated' => 'ViewUntranslatedTask',
	'optional' => 'ViewOptionalTask',
	'suggestions' => 'ViewWithSuggestionsTask',
//	'untranslatedoptional' => 'ViewUntranslatedOptionalTask',
//	'review'               => 'ReviewMessagesTask',
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
$wgTranslateSupportUrl = false;

/**
 * When unprivileged users opens a translation editor, he will
 * see message stating that special permission is needed for translating
 * messages. If this variable is defined, there is a button which will
 * take the user to that page to ask for permission.
 */
$wgTranslatePermissionUrl = 'Project:Translator';

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
$wgEnablePageTranslation = true;

/**
 * Number for the Translations namespace. Change this if it conflicts with
 * other namespace in your wiki.
 */
$wgPageTranslationNamespace = 1198;

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

$wgTranslateBlacklist = array();

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
$wgTranslateAuthorBlacklist = array();
$wgTranslateAuthorBlacklist[] = array( 'black', '/^.*;.*;.*Bot$/Ui' );

/**
 * List of namespace that contain messages. No talk namespaces.
 * @see http://translatewiki.net/wiki/Translating:Group_configuration
 */
$wgTranslateMessageNamespaces = array( NS_MEDIAWIKI );

/**
 * CC = Custom classes.
 * Custom classes can register themselves here.
 * Key is always the group id, while the value is an message group object
 * or callable function.
 * @deprecated Use TranslatePostInitGroups hook instead.
 */
$wgTranslateCC = array();

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
$wgTranslateGroupFiles = array();

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
 * $wgTranslateWorkflowStates["groupid"] = array(
 *      'new' => array( 'color' => 'FF0000' ), // red
 *      'needs_proofreading' => array( 'color' => '0000FF' ), // blue
 *      'ready' => array( 'color' => 'FFFF00' ), // yellow
 *      'published' => array(
 *          'color' => '00FF00', // green
 *          'right' => 'centralnotice-admin',
 *      ),
 * );
 * If there is a default workflowstate for all groups, define it like this:
 *  $wgTranslateWorkflowStates["default"] = array( // configuration )
 *
 */
$wgTranslateWorkflowStates = false;

# </source>
# === System setup related configuration ===
# <source lang=php>
/**
 * Location of your extensions, if not the default. Only matters
 * if you are localising your own extensions with this extension.
 */
$wgTranslateExtensionDirectory = "$IP/extensions/";

/**
 * Set location of cache files. Defaults to $wgCacheDirectory.
 */
$wgTranslateCacheDirectory = false;

/**
 * Configures how the message index is stored.
 * The other backends need $wgCacheDirectory to be functional.
 */
$wgTranslateMessageIndex = array( 'DatabaseMessageIndex' );
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
$wgTranslateYamlLibrary = 'spyc';

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

# </source>

/** @cond cli_support */
if ( !defined( 'TRANSLATE_CLI' ) ) {
	function STDOUT() {}
	function STDERR() {}
}
/** @endcond */

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
