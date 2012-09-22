<?php
/**
 * Autoload definitions.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2008-2012, Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

/** @cond file_level_code */
$dir = dirname( __FILE__ );
/** @endcond */

/**
 * @name   Core Translate classes
 * @{
 */
$wgAutoloadClasses['FatMessage'] = "$dir/Message.php";
$wgAutoloadClasses['MediaWikiMessageChecker'] = "$dir/MediaWikiMessageChecker.php";
$wgAutoloadClasses['MessageChecker'] = "$dir/MessageChecks.php";
$wgAutoloadClasses['MessageCollection'] = "$dir/MessageCollection.php";
$wgAutoloadClasses['MessageDefinitions'] = "$dir/MessageCollection.php";
$wgAutoloadClasses['MessageGroups'] = "$dir/MessageGroups.php";
$wgAutoloadClasses['TMessage'] = "$dir/Message.php";
$wgAutoloadClasses['TaskOptions'] = "$dir/TranslateTasks.php";
$wgAutoloadClasses['ThinMessage'] = "$dir/Message.php";
$wgAutoloadClasses['TranslateEditAddons'] = "$dir/TranslateEditAddons.php";
$wgAutoloadClasses['TranslateHooks'] = "$dir/TranslateHooks.php";
$wgAutoloadClasses['TranslateRcFilter'] = "$dir/RcFilter.php";
$wgAutoloadClasses['TranslateTasks'] = "$dir/TranslateTasks.php";
$wgAutoloadClasses['TranslateUtils'] = "$dir/TranslateUtils.php";
$wgAutoloadClasses['VoctrainMessageChecker'] = "$dir/ffs/Voctrain.php";
/**@}*/

/**
 * @name   Special pages
 * There are few more special pages in page translation section.
 * @{
 */
$wgAutoloadClasses['SpecialAggregateGroups'] = "$dir/specials/SpecialAggregateGroups.php";
$wgAutoloadClasses['SpecialFirstSteps'] = "$dir/specials/SpecialFirstSteps.php";
$wgAutoloadClasses['SpecialImportTranslations'] = "$dir/specials/SpecialImportTranslations.php";
$wgAutoloadClasses['SpecialLanguageStats'] = "$dir/specials/SpecialLanguageStats.php";
$wgAutoloadClasses['SpecialMagic'] = "$dir/specials/SpecialMagic.php";
$wgAutoloadClasses['SpecialManageGroups'] = "$dir/specials/SpecialManageGroups.php";
$wgAutoloadClasses['SpecialMessageGroupStats'] = "$dir/specials/SpecialMessageGroupStats.php";
$wgAutoloadClasses['SpecialMyLanguage'] = "$dir/specials/SpecialMyLanguage.php";
$wgAutoloadClasses['SpecialSupportedLanguages'] = "$dir/specials/SpecialSupportedLanguages.php";
$wgAutoloadClasses['SpecialTranslate'] = "$dir/specials/SpecialTranslate.php";
$wgAutoloadClasses['SpecialTranslationStats'] = "$dir/specials/SpecialTranslationStats.php";
$wgAutoloadClasses['SpecialTranslations'] = "$dir/specials/SpecialTranslations.php";
/**@}*/

/**
 * @name   Old-style file format support (FFS)
 * @{
 */
$wgAutoloadClasses['SimpleFormatReader'] = "$dir/ffs/Simple.php";
$wgAutoloadClasses['SimpleFormatWriter'] = "$dir/ffs/Simple.php";
$wgAutoloadClasses['WikiExtensionFormatReader'] = "$dir/ffs/WikiExtension.php";
$wgAutoloadClasses['WikiExtensionFormatWriter'] = "$dir/ffs/WikiExtension.php";
$wgAutoloadClasses['WikiFormatReader'] = "$dir/ffs/Wiki.php";
$wgAutoloadClasses['WikiFormatWriter'] = "$dir/ffs/Wiki.php";
$wgAutoloadClasses['XliffFormatWriter'] = "$dir/ffs/Xliff.php";
/**@}*/

/**
 * @name   Various utilities
 * @{
 */
$wgAutoloadClasses['CDBMessageIndex'] = "$dir/utils/MessageIndex.php";
$wgAutoloadClasses['CachedMessageIndex'] = "$dir/utils/MessageIndex.php";
$wgAutoloadClasses['DatabaseMessageIndex'] = "$dir/utils/MessageIndex.php";
$wgAutoloadClasses['FCFontFinder'] = "$dir/utils/Font.php";
$wgAutoloadClasses['FileCachedMessageIndex'] = "$dir/utils/MessageIndex.php";
$wgAutoloadClasses['FuzzyBot'] = "$dir/utils/FuzzyBot.php";
$wgAutoloadClasses['HTMLJsSelectToInputField'] = "$dir/utils/HTMLJsSelectToInputField.php";
$wgAutoloadClasses['JsSelectToInput'] = "$dir/utils/JsSelectToInput.php";
$wgAutoloadClasses['MessageGroupCache'] = "$dir/utils/MessageGroupCache.php";
$wgAutoloadClasses['MessageGroupStats'] = "$dir/utils/MessageGroupStats.php";
$wgAutoloadClasses['MessageGroupWorkflowStateUpdaterJob'] = "$dir/utils/MessageGroupWorkflowStateUpdaterJob.php";
$wgAutoloadClasses['MessageHandle'] = "$dir/utils/MessageHandle.php";
$wgAutoloadClasses['MessageIndex'] = "$dir/utils/MessageIndex.php";
$wgAutoloadClasses['MessageIndexRebuildJob'] = "$dir/utils/MessageIndexRebuildJob.php";
$wgAutoloadClasses['MessageTable'] = "$dir/utils/MessageTable.php";
$wgAutoloadClasses['MessageUpdateJob'] = "$dir/utils/MessageUpdateJob.php";
$wgAutoloadClasses['MessageWebImporter'] = "$dir/utils/MessageWebImporter.php";
$wgAutoloadClasses['PHPVariableLoader'] = "$dir/utils/ResourceLoader.php";
$wgAutoloadClasses['RevTag'] = "$dir/utils/RevTag.php";
$wgAutoloadClasses['ReviewPerLanguageStats'] = "$dir/specials/SpecialTranslationStats.php";
$wgAutoloadClasses['SerializedMessageIndex'] = "$dir/utils/MessageIndex.php";
$wgAutoloadClasses['StatsTable'] = "$dir/utils/StatsTable.php";
$wgAutoloadClasses['StringMangler'] = "$dir/utils/StringMatcher.php";
$wgAutoloadClasses['StringMatcher'] = "$dir/utils/StringMatcher.php";
$wgAutoloadClasses['TTMServer'] = "$dir/utils/TTMServer.php";
$wgAutoloadClasses['TranslateMetadata'] = "$dir/utils/TranslateMetadata.php";
$wgAutoloadClasses['TranslatePerLanguageStats'] = "$dir/specials/SpecialTranslationStats.php";
$wgAutoloadClasses['TranslatePreferences'] = "$dir/utils/UserToggles.php";
$wgAutoloadClasses['TranslateRegistrationStats'] = "$dir/specials/SpecialTranslationStats.php";
$wgAutoloadClasses['TranslateStatsOutput'] = "$dir/scripts/groupStatistics.php";
$wgAutoloadClasses['TranslateToolbox'] = "$dir/utils/ToolBox.php";
$wgAutoloadClasses['TranslateYaml'] = "$dir/utils/TranslateYaml.php";
$wgAutoloadClasses['TranslationEditPage'] = "$dir/utils/TranslationEditPage.php";
$wgAutoloadClasses['TranslationHelperException'] = "$dir/utils/TranslationHelpers.php";
$wgAutoloadClasses['TranslationHelpers'] = "$dir/utils/TranslationHelpers.php";
$wgAutoloadClasses['TranslationStats'] = "$dir/utils/TranslationStats.php";
$wgAutoloadClasses['TranslationStatsBase'] = "$dir/specials/SpecialTranslationStats.php";
$wgAutoloadClasses['TranslationStatsInterface'] = "$dir/specials/SpecialTranslationStats.php";
/**@}*/

/**
 * @name   Classes for predefined non-managed message groups
 * @{
 */
$wgAutoloadClasses['PremadeMediawikiExtensionGroups'] = "$dir/ffs/MediaWikiExtensions.php";
$wgAutoloadClasses['PremadeToolserverTextdomains'] = "$dir/ffs/ToolserverTextdomains.php";
/**@}*/

/**
 * @name   Support for MediaWiki non-message features
 * @{
 */
$wgAutoloadClasses['ComplexMessages'] = "$dir/ffs/MediaWikiComplexMessages.php";
$wgAutoloadClasses['MagicWordsCM'] = "$dir/ffs/MediaWikiComplexMessages.php";
$wgAutoloadClasses['NamespaceCM'] = "$dir/ffs/MediaWikiComplexMessages.php";
$wgAutoloadClasses['SpecialPageAliasesCM'] = "$dir/ffs/MediaWikiComplexMessages.php";
/**@}*/

/**
 * @name   Classes for page translation feature
 * @ingroup PageTranslation
 * @{
 */
$wgAutoloadClasses['DeleteJob'] = "$dir/tag/DeleteJob.php";
$wgAutoloadClasses['MoveJob'] = "$dir/tag/MoveJob.php";
$wgAutoloadClasses['PageTranslationHooks'] = "$dir/tag/PageTranslationHooks.php";
$wgAutoloadClasses['RenderJob'] = "$dir/tag/RenderJob.php";
$wgAutoloadClasses['SpecialPageTranslation'] = "$dir/tag/SpecialPageTranslation.php";
$wgAutoloadClasses['SpecialPageTranslationDeletePage'] = "$dir/tag/SpecialPageTranslationDeletePage.php";
$wgAutoloadClasses['SpecialPageTranslationMovePage'] = "$dir/tag/SpecialPageTranslationMovePage.php";
$wgAutoloadClasses['TPException'] = "$dir/tag/TPException.php";
$wgAutoloadClasses['TPParse'] = "$dir/tag/TPParse.php";
$wgAutoloadClasses['TPSection'] = "$dir/tag/TPSection.php";
$wgAutoloadClasses['TranslatablePage'] = "$dir/tag/TranslatablePage.php";
/**@}*/

/**
 * @name   Classes for TTMServer
 * @ingroup TTMServer
 * @{
 */
$wgAutoloadClasses['ReadableTTMServer'] = "$dir/ttmserver/Interfaces.php";
$wgAutoloadClasses['WritableTTMServer'] = "$dir/ttmserver/Interfaces.php";

$wgAutoloadClasses['DatabaseTTMServer'] = "$dir/ttmserver/DatabaseTTMServer.php";
$wgAutoloadClasses['FakeTTMServer'] = "$dir/ttmserver/FakeTTMServer.php";
$wgAutoloadClasses['RemoteTTMServer'] = "$dir/ttmserver/RemoteTTMServer.php";
$wgAutoloadClasses['SolrTTMServer'] = "$dir/ttmserver/SolrTTMServer.php";
$wgAutoloadClasses['TTMServer'] = "$dir/ttmserver/TTMServer.php";
/**@}*/

/**
 * @name   Classes for file format support (FFS)
 * @{
 */
$wgAutoloadClasses['AndroidXmlFFS'] = "$dir/ffs/AndroidXmlFFS.php";
$wgAutoloadClasses['DtdFFS'] = "$dir/ffs/DtdFFS.php";
$wgAutoloadClasses['FFS'] = "$dir/ffs/FFS.php";
$wgAutoloadClasses['FlatPhpFFS'] = "$dir/ffs/FlatPhpFFS.php";
$wgAutoloadClasses['GettextFFS'] = "$dir/ffs/GettextFFS.php";
$wgAutoloadClasses['GettextPluralException'] = "$dir/ffs/GettextFFS.php";
$wgAutoloadClasses['JavaFFS'] = "$dir/ffs/JavaFFS.php";
$wgAutoloadClasses['JavaScriptFFS'] = "$dir/ffs/JavaScriptFFS.php";
$wgAutoloadClasses['JsonFFS'] = "$dir/ffs/JsonFFS.php";
$wgAutoloadClasses['PythonSingleFFS'] = "$dir/ffs/PythonSingleFFS.php";
$wgAutoloadClasses['RubyYamlFFS'] = "$dir/ffs/RubyYamlFFS.php";
$wgAutoloadClasses['ShapadoJsFFS'] = "$dir/ffs/JavaScriptFFS.php";
$wgAutoloadClasses['SimpleFFS'] = "$dir/ffs/FFS.php";
$wgAutoloadClasses['YamlFFS'] = "$dir/ffs/YamlFFS.php";
/**@}*/

/**
 * @name   API modules
 * @{
 */
$wgAutoloadClasses['ApiAggregateGroups'] = "$dir/api/ApiAggregateGroups.php";
$wgAutoloadClasses['ApiGroupReview'] = "$dir/api/ApiGroupReview.php";
$wgAutoloadClasses['ApiQueryMessageCollection'] = "$dir/api/ApiQueryMessageCollection.php";
$wgAutoloadClasses['ApiQueryMessageGroupStats'] = "$dir/api/ApiQueryMessageGroupStats.php";
$wgAutoloadClasses['ApiQueryMessageGroups'] = "$dir/api/ApiQueryMessageGroups.php";
$wgAutoloadClasses['ApiQueryMessageTranslations'] = "$dir/api/ApiQueryMessageTranslations.php";
$wgAutoloadClasses['ApiTTMServer'] = "$dir/api/ApiTTMServer.php";
$wgAutoloadClasses['ApiTranslationReview'] = "$dir/api/ApiTranslationReview.php";
/**@}*/

/**
 * @name   Task classes
 * @{
 */
$wgAutoloadClasses['AcceptQueueMessagesTask'] = "$dir/TranslateTasks.php";
$wgAutoloadClasses['ExportAsPoMessagesTask'] = "$dir/TranslateTasks.php";
$wgAutoloadClasses['ExportMessagesTask'] = "$dir/TranslateTasks.php";
$wgAutoloadClasses['ExportToFileMessagesTask'] = "$dir/TranslateTasks.php";
$wgAutoloadClasses['ExportToXliffMessagesTask'] = "$dir/TranslateTasks.php";
$wgAutoloadClasses['ReviewAllMessagesTask'] = "$dir/TranslateTasks.php";
$wgAutoloadClasses['ReviewMessagesTask'] = "$dir/TranslateTasks.php";
$wgAutoloadClasses['TranslateTask'] = "$dir/TranslateTasks.php";
$wgAutoloadClasses['ViewMessagesTask'] = "$dir/TranslateTasks.php";
$wgAutoloadClasses['ViewOptionalTask'] = "$dir/TranslateTasks.php";
$wgAutoloadClasses['ViewUntranslatedOptionalTask'] = "$dir/TranslateTasks.php";
$wgAutoloadClasses['ViewUntranslatedTask'] = "$dir/TranslateTasks.php";
$wgAutoloadClasses['ViewWithSuggestionsTask'] = "$dir/TranslateTasks.php";
/**@}*/

/**
 * @name   Message group classes
 * @{
 */
$wgAutoloadClasses['AggregateMessageGroup'] = "$dir/messagegroups/AggregateMessageGroup.php";
$wgAutoloadClasses['AliasMessageGroup'] = "$dir/messagegroups/AliasMessageGroup.php";
$wgAutoloadClasses['CoreMessageGroup'] = "$dir/messagegroups/CoreMessageGroup.php";
$wgAutoloadClasses['CoreMostUsedMessageGroup'] = "$dir/messagegroups/CoreMostUsedMessageGroup.php";
$wgAutoloadClasses['ExtensionMessageGroup'] = "$dir/messagegroups/ExtensionMessageGroup.php";
$wgAutoloadClasses['FileBasedMessageGroup'] = "$dir/messagegroups/FileBasedMessageGroup.php";
$wgAutoloadClasses['MediaWikiMessageGroup'] = "$dir/messagegroups/MediaWikiMessageGroup.php";
$wgAutoloadClasses['MessageGroup'] = "$dir/messagegroups/MessageGroup.php";
$wgAutoloadClasses['MessageGroupBase'] = "$dir/messagegroups/MessageGroupBase.php";
$wgAutoloadClasses['MessageGroupOld'] = "$dir/messagegroups/MessageGroupOld.php";
$wgAutoloadClasses['RecentMessageGroup'] = "$dir/messagegroups/RecentMessageGroup.php";
$wgAutoloadClasses['SingleFileBasedMessageGroup'] = "$dir/messagegroups/FileBasedMessageGroup.php";
$wgAutoloadClasses['VoctrainMessageGroup'] = "$dir/ffs/Voctrain.php";
$wgAutoloadClasses['WikiMessageGroup'] = "$dir/messagegroups/WikiMessageGroup.php";
$wgAutoloadClasses['WikiPageMessageGroup'] = "$dir/messagegroups/WikiPageMessageGroup.php";
$wgAutoloadClasses['WorkflowStatesMessageGroup'] = "$dir/messagegroups/WorkflowStatesMessageGroup.php";
/**@}*/
