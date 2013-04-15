<?php
/**
 * Autoload definitions.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2008-2013, Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

/** @cond file_level_code */
$dir = dirname( __FILE__ );
/** @endcond */

/**
 * @name   "Core Translate classes"
 * @{
 */
$wgAutoloadClasses['FatMessage'] = "$dir/Message.php";
$wgAutoloadClasses['MediaWikiMessageChecker'] = "$dir/MediaWikiMessageChecker.php";
$wgAutoloadClasses['MessageChecker'] = "$dir/MessageChecks.php";
$wgAutoloadClasses['MessageCollection'] = "$dir/MessageCollection.php";
$wgAutoloadClasses['MessageDefinitions'] = "$dir/MessageCollection.php";
$wgAutoloadClasses['MessageGroups'] = "$dir/MessageGroups.php";
$wgAutoloadClasses['TMessage'] = "$dir/Message.php";
$wgAutoloadClasses['ThinMessage'] = "$dir/Message.php";
$wgAutoloadClasses['TranslateEditAddons'] = "$dir/TranslateEditAddons.php";
$wgAutoloadClasses['TranslateHooks'] = "$dir/TranslateHooks.php";
$wgAutoloadClasses['TranslateTasks'] = "$dir/TranslateTasks.php";
$wgAutoloadClasses['TranslateUtils'] = "$dir/TranslateUtils.php";
/**@}*/

/**
 * @name   "Special pages"
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
$wgAutoloadClasses['SpecialSearchTranslations'] = "$dir/specials/SpecialSearchTranslations.php";
$wgAutoloadClasses['SpecialSupportedLanguages'] = "$dir/specials/SpecialSupportedLanguages.php";
$wgAutoloadClasses['SpecialTranslate'] = "$dir/specials/SpecialTranslate.php";
$wgAutoloadClasses['SpecialTranslateSandbox'] = "$dir/specials/SpecialTranslateSandbox.php";
$wgAutoloadClasses['SpecialTranslationStats'] = "$dir/specials/SpecialTranslationStats.php";
$wgAutoloadClasses['SpecialTranslations'] = "$dir/specials/SpecialTranslations.php";
/**@}*/

/**
 * @name   Old-style file format support (FFS)
 * @{
 */
$wgAutoloadClasses['SimpleFormatReader'] = "$dir/ffs/Simple.php";
$wgAutoloadClasses['SimpleFormatWriter'] = "$dir/ffs/Simple.php";
$wgAutoloadClasses['WikiFormatReader'] = "$dir/ffs/Wiki.php";
$wgAutoloadClasses['WikiFormatWriter'] = "$dir/ffs/Wiki.php";
/**@}*/

/**
 * @name   "Various utilities"
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
$wgAutoloadClasses['MessageGroupStates'] = "$dir/utils/MessageGroupStates.php";
$wgAutoloadClasses['MessageGroupStatesUpdaterJob'] = "$dir/utils/MessageGroupStatesUpdaterJob.php";
$wgAutoloadClasses['MessageGroupStats'] = "$dir/utils/MessageGroupStats.php";
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
$wgAutoloadClasses['StatsBar'] = "$dir/utils/StatsBar.php";
$wgAutoloadClasses['StatsTable'] = "$dir/utils/StatsTable.php";
$wgAutoloadClasses['StringMangler'] = "$dir/utils/StringMatcher.php";
$wgAutoloadClasses['StringMatcher'] = "$dir/utils/StringMatcher.php";
$wgAutoloadClasses['TTMServer'] = "$dir/utils/TTMServer.php";
$wgAutoloadClasses['TranslateLogFormatter'] = "$dir/utils/TranslateLogFormatter.php";
$wgAutoloadClasses['TranslateMetadata'] = "$dir/utils/TranslateMetadata.php";
$wgAutoloadClasses['TranslatePerLanguageStats'] = "$dir/specials/SpecialTranslationStats.php";
$wgAutoloadClasses['TranslatePreferences'] = "$dir/utils/UserToggles.php";
$wgAutoloadClasses['TranslateRcFilter'] = "$dir/utils/RcFilter.php";
$wgAutoloadClasses['TranslateRegistrationStats'] = "$dir/specials/SpecialTranslationStats.php";
$wgAutoloadClasses['TranslateSandbox'] = "$dir/utils/TranslateSandbox.php";
$wgAutoloadClasses['TranslateSandboxReminderJob'] = "$dir/utils/TranslateSandboxReminderJob.php";
$wgAutoloadClasses['TranslateStatsOutput'] = "$dir/scripts/groupStatistics.php";
$wgAutoloadClasses['TranslateToolbox'] = "$dir/utils/ToolBox.php";
$wgAutoloadClasses['TranslateYaml'] = "$dir/utils/TranslateYaml.php";
$wgAutoloadClasses['TranslationEditPage'] = "$dir/utils/TranslationEditPage.php";
$wgAutoloadClasses['TranslationHelperException'] = "$dir/utils/TranslationHelpers.php";
$wgAutoloadClasses['TranslationHelpers'] = "$dir/utils/TranslationHelpers.php";
$wgAutoloadClasses['TranslationStats'] = "$dir/utils/TranslationStats.php";
$wgAutoloadClasses['TranslationStatsBase'] = "$dir/specials/SpecialTranslationStats.php";
$wgAutoloadClasses['TranslationStatsInterface'] = "$dir/specials/SpecialTranslationStats.php";
$wgAutoloadClasses['TuxMessageTable'] = "$dir/utils/TuxMessageTable.php";
/**@}*/

/**
 * @name   "Classes for predefined non-managed message groups"
 * @{
 */
$wgAutoloadClasses['PremadeMediawikiExtensionGroups'] = "$dir/ffs/MediaWikiExtensions.php";
$wgAutoloadClasses['PremadeToolserverTextdomains'] = "$dir/ffs/ToolserverTextdomains.php";
/**@}*/

/**
 * @name   "Support for MediaWiki non-message features"
 * @{
 */
$wgAutoloadClasses['ComplexMessages'] = "$dir/ffs/MediaWikiComplexMessages.php";
$wgAutoloadClasses['MagicWordsCM'] = "$dir/ffs/MediaWikiComplexMessages.php";
$wgAutoloadClasses['NamespaceCM'] = "$dir/ffs/MediaWikiComplexMessages.php";
$wgAutoloadClasses['SpecialPageAliasesCM'] = "$dir/ffs/MediaWikiComplexMessages.php";
/**@}*/

/**
 * @name   "Classes for page translation feature"
 * @ingroup PageTranslation
 * @{
 */
$wgAutoloadClasses['TranslateDeleteJob'] = "$dir/tag/TranslateDeleteJob.php";
$wgAutoloadClasses['TranslateMoveJob'] = "$dir/tag/TranslateMoveJob.php";
$wgAutoloadClasses['PageTranslationHooks'] = "$dir/tag/PageTranslationHooks.php";
$wgAutoloadClasses['PageTranslationLogFormatter'] = "$dir/tag/PageTranslationLogFormatter.php";
$wgAutoloadClasses['TranslateRenderJob'] = "$dir/tag/TranslateRenderJob.php";
$wgAutoloadClasses['SpecialPageTranslation'] = "$dir/tag/SpecialPageTranslation.php";
$wgAutoloadClasses['SpecialPageTranslationDeletePage'] = "$dir/tag/SpecialPageTranslationDeletePage.php";
$wgAutoloadClasses['SpecialPageTranslationMovePage'] = "$dir/tag/SpecialPageTranslationMovePage.php";
$wgAutoloadClasses['TPException'] = "$dir/tag/TPException.php";
$wgAutoloadClasses['TPParse'] = "$dir/tag/TPParse.php";
$wgAutoloadClasses['TPSection'] = "$dir/tag/TPSection.php";
$wgAutoloadClasses['TranslatablePage'] = "$dir/tag/TranslatablePage.php";
/**@}*/

/**
 * @name   "Classes for TTMServer"
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
$wgAutoloadClasses['TTMServerMessageUpdateJob'] = "$dir/ttmserver/TTMServerMessageUpdateJob.php";
/**@}*/

/**
 * @name   "Classes for file format support (FFS)"
 * @{
 */
$wgAutoloadClasses['AndroidXmlFFS'] = "$dir/ffs/AndroidXmlFFS.php";
$wgAutoloadClasses['DtdFFS'] = "$dir/ffs/DtdFFS.php";
$wgAutoloadClasses['FFS'] = "$dir/ffs/FFS.php";
$wgAutoloadClasses['FlatPhpFFS'] = "$dir/ffs/FlatPhpFFS.php";
$wgAutoloadClasses['GettextFFS'] = "$dir/ffs/GettextFFS.php";
$wgAutoloadClasses['GettextPluralException'] = "$dir/ffs/GettextFFS.php";
$wgAutoloadClasses['IniFFS'] = "$dir/ffs/IniFFS.php";
$wgAutoloadClasses['JavaFFS'] = "$dir/ffs/JavaFFS.php";
$wgAutoloadClasses['JavaScriptFFS'] = "$dir/ffs/JavaScriptFFS.php";
$wgAutoloadClasses['JsonFFS'] = "$dir/ffs/JsonFFS.php";
$wgAutoloadClasses['MediaWikiExtensionFFS'] = "$dir/ffs/MediaWikiExtensionFFS.php";
$wgAutoloadClasses['PythonSingleFFS'] = "$dir/ffs/PythonSingleFFS.php";
$wgAutoloadClasses['RubyYamlFFS'] = "$dir/ffs/RubyYamlFFS.php";
$wgAutoloadClasses['ShapadoJsFFS'] = "$dir/ffs/JavaScriptFFS.php";
$wgAutoloadClasses['SimpleFFS'] = "$dir/ffs/FFS.php";
$wgAutoloadClasses['XliffFFS'] = "$dir/ffs/XliffFFS.php";
$wgAutoloadClasses['YamlFFS'] = "$dir/ffs/YamlFFS.php";
/**@}*/

/**
 * @name   "API modules"
 * @{
 */
$wgAutoloadClasses['ApiAggregateGroups'] = "$dir/api/ApiAggregateGroups.php";
$wgAutoloadClasses['ApiGroupReview'] = "$dir/api/ApiGroupReview.php";
$wgAutoloadClasses['ApiQueryLanguageStats'] = "$dir/api/ApiQueryLanguageStats.php";
$wgAutoloadClasses['ApiQueryMessageCollection'] = "$dir/api/ApiQueryMessageCollection.php";
$wgAutoloadClasses['ApiQueryMessageGroupStats'] = "$dir/api/ApiQueryMessageGroupStats.php";
$wgAutoloadClasses['ApiQueryMessageGroups'] = "$dir/api/ApiQueryMessageGroups.php";
$wgAutoloadClasses['ApiQueryMessageTranslations'] = "$dir/api/ApiQueryMessageTranslations.php";
$wgAutoloadClasses['ApiTTMServer'] = "$dir/api/ApiTTMServer.php";
$wgAutoloadClasses['ApiTranslateSandbox'] = "$dir/api/ApiTranslateSandbox.php";
$wgAutoloadClasses['ApiTranslateUser'] = "$dir/api/ApiTranslateUser.php";
$wgAutoloadClasses['ApiTranslationReview'] = "$dir/api/ApiTranslationReview.php";
$wgAutoloadClasses['ApiTranslationAids'] = "$dir/api/ApiQueryTranslationAids.php";
$wgAutoloadClasses['ApiStatsQuery'] = "$dir/api/ApiStatsQuery.php";
$wgAutoloadClasses['ApiHardMessages'] = "$dir/api/ApiHardMessages.php";
/**@}*/

/**
 * @name   "Task classes"
 * @{
 */
$wgAutoloadClasses['AcceptQueueMessagesTask'] = "$dir/TranslateTasks.php";
$wgAutoloadClasses['CustomFilteredMessagesTask'] = "$dir/TranslateTasks.php";
$wgAutoloadClasses['ExportAsPoMessagesTask'] = "$dir/TranslateTasks.php";
$wgAutoloadClasses['ExportMessagesTask'] = "$dir/TranslateTasks.php";
$wgAutoloadClasses['ExportToFileMessagesTask'] = "$dir/TranslateTasks.php";
$wgAutoloadClasses['ReviewAllMessagesTask'] = "$dir/TranslateTasks.php";
$wgAutoloadClasses['ReviewMessagesTask'] = "$dir/TranslateTasks.php";
$wgAutoloadClasses['TranslateTask'] = "$dir/TranslateTasks.php";
$wgAutoloadClasses['ViewMessagesTask'] = "$dir/TranslateTasks.php";
$wgAutoloadClasses['ViewOptionalTask'] = "$dir/TranslateTasks.php";
$wgAutoloadClasses['ViewUntranslatedTask'] = "$dir/TranslateTasks.php";
/**@}*/

/**
 * @name   "Message group classes"
 * @{
 */
$wgAutoloadClasses['AggregateMessageGroup'] = "$dir/messagegroups/AggregateMessageGroup.php";
$wgAutoloadClasses['CoreMessageGroup'] = "$dir/messagegroups/CoreMessageGroup.php";
$wgAutoloadClasses['CoreMostUsedMessageGroup'] = "$dir/messagegroups/CoreMostUsedMessageGroup.php";
$wgAutoloadClasses['FileBasedMessageGroup'] = "$dir/messagegroups/FileBasedMessageGroup.php";
$wgAutoloadClasses['MediaWikiMessageGroup'] = "$dir/messagegroups/MediaWikiMessageGroup.php";
$wgAutoloadClasses['MediaWikiExtensionMessageGroup'] = "$dir/messagegroups/MediaWikiExtensionMessageGroup.php";
$wgAutoloadClasses['MessageGroup'] = "$dir/messagegroups/MessageGroup.php";
$wgAutoloadClasses['MessageGroupBase'] = "$dir/messagegroups/MessageGroupBase.php";
$wgAutoloadClasses['MessageGroupOld'] = "$dir/messagegroups/MessageGroupOld.php";
$wgAutoloadClasses['RecentMessageGroup'] = "$dir/messagegroups/RecentMessageGroup.php";
$wgAutoloadClasses['RecentAdditionsMessageGroup'] = "$dir/messagegroups/RecentAdditionsMessageGroup.php";
$wgAutoloadClasses['SingleFileBasedMessageGroup'] = "$dir/messagegroups/SingleFileBasedMessageGroup.php";
$wgAutoloadClasses['VoctrainMessageGroup'] = "$dir/ffs/Voctrain.php";
$wgAutoloadClasses['WikiMessageGroup'] = "$dir/messagegroups/WikiMessageGroup.php";
$wgAutoloadClasses['WikiPageMessageGroup'] = "$dir/messagegroups/WikiPageMessageGroup.php";
$wgAutoloadClasses['WorkflowStatesMessageGroup'] = "$dir/messagegroups/WorkflowStatesMessageGroup.php";
/**@}*/

/**
 * @name   "Test classes"
 * @{
 */
$wgAutoloadClasses['MockFileBasedMessageGroup'] = "$dir/tests/MockFileBasedMessageGroup.php";
$wgAutoloadClasses['MockMessageCollectionForExport'] = "$dir/tests/MockMessageCollectionForExport.php";
$wgAutoloadClasses['MockSuperUser'] = "$dir/tests/MockSuperUser.php";
$wgAutoloadClasses['MockWikiMessageGroup'] = "$dir/tests/MockWikiMessageGroup.php";
/**@}*/

/**
 * @name   "Translation aids"
 * @{
 */
$wgAutoloadClasses['CurrentTranslationAid'] = "$dir/translationaids/CurrentTranslationAid.php";
$wgAutoloadClasses['DocumentationAid'] = "$dir/translationaids/DocumentationAid.php";
$wgAutoloadClasses['GettextDocumentationAid'] = "$dir/translationaids/GettextDocumentationAid.php";
$wgAutoloadClasses['InOtherLanguagesAid'] = "$dir/translationaids/InOtherLanguagesAid.php";
$wgAutoloadClasses['MachineTranslationAid'] = "$dir/translationaids/MachineTranslationAid.php";
$wgAutoloadClasses['MessageDefinitionAid'] = "$dir/translationaids/MessageDefinitionAid.php";
$wgAutoloadClasses['SupportAid'] = "$dir/translationaids/SupportAid.php";
$wgAutoloadClasses['TTMServerAid'] = "$dir/translationaids/TTMServerAid.php";
$wgAutoloadClasses['TranslationAid'] = "$dir/translationaids/TranslationAid.php";
$wgAutoloadClasses['UnsupportedTranslationAid'] = "$dir/translationaids/UnsupportedTranslationAid.php";
$wgAutoloadClasses['UpdatedDefinitionAid'] = "$dir/translationaids/UpdatedDefinitionAid.php";
/**@}*/

/**
 * @name   "Translation web services"
 * @{
 */
$wgAutoloadClasses['ApertiumWebService'] = "$dir/webservices/ApertiumWebService.php";
$wgAutoloadClasses['MicrosoftWebService'] = "$dir/webservices/MicrosoftWebService.php";
$wgAutoloadClasses['RemoteTTMServerWebService'] = "$dir/webservices/RemoteTTMServerWebService.php";
$wgAutoloadClasses['TranslationWebService'] = "$dir/webservices/TranslationWebService.php";
$wgAutoloadClasses['TranslationWebServiceException'] = "$dir/webservices/TranslationWebServiceException.php";
$wgAutoloadClasses['YandexWebService'] = "$dir/webservices/YandexWebService.php";
/**@}*/
