<?php
/**
 * Autoload definitions.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0+
 */

/** @cond file_level_code */
$dir = __DIR__;
/** @endcond */

/**
 * @name   "Core Translate classes"
 * @{
 */
$al['FatMessage'] = "$dir/Message.php";
$al['MediaWikiMessageChecker'] = "$dir/MediaWikiMessageChecker.php";
$al['MessageChecker'] = "$dir/MessageChecks.php";
$al['MessageCollection'] = "$dir/MessageCollection.php";
$al['MessageDefinitions'] = "$dir/MessageCollection.php";
$al['MessageGroups'] = "$dir/MessageGroups.php";
$al['MessageGroupConfigurationParser'] = "$dir/MessageGroupConfigurationParser.php";
$al['MetaYamlSchemaExtender'] = "$dir/MetaYamlSchemaExtender.php";
$al['TMessage'] = "$dir/Message.php";
$al['ThinMessage'] = "$dir/Message.php";
$al['TranslateEditAddons'] = "$dir/TranslateEditAddons.php";
$al['TranslateHooks'] = "$dir/TranslateHooks.php";
$al['TranslateTasks'] = "$dir/TranslateTasks.php";
$al['TranslateUtils'] = "$dir/TranslateUtils.php";
/**@}*/

/**
 * @name   "Special pages"
 * There are few more special pages in page translation section.
 * @{
 */
$al['SpecialAggregateGroups'] = "$dir/specials/SpecialAggregateGroups.php";
$al['SpecialImportTranslations'] = "$dir/specials/SpecialImportTranslations.php";
$al['SpecialExportTranslations'] = "$dir/specials/SpecialExportTranslations.php";
$al['SpecialLanguageStats'] = "$dir/specials/SpecialLanguageStats.php";
$al['SpecialMagic'] = "$dir/specials/SpecialMagic.php";
$al['SpecialManageGroups'] = "$dir/specials/SpecialManageGroups.php";
$al['SpecialMessageGroupStats'] = "$dir/specials/SpecialMessageGroupStats.php";
$al['SpecialPageMigration'] = "$dir/specials/SpecialPageMigration.php";
$al['SpecialPagePreparation'] = "$dir/specials/SpecialPagePreparation.php";
$al['SpecialSearchTranslations'] = "$dir/specials/SpecialSearchTranslations.php";
$al['SpecialSupportedLanguages'] = "$dir/specials/SpecialSupportedLanguages.php";
$al['SpecialTranslate'] = "$dir/specials/SpecialTranslate.php";
$al['SpecialManageTranslatorSandbox'] =
	"$dir/specials/SpecialManageTranslatorSandbox.php";
$al['SpecialTranslationStats'] = "$dir/specials/SpecialTranslationStats.php";
$al['SpecialTranslations'] = "$dir/specials/SpecialTranslations.php";
$al['SpecialTranslationStash'] = "$dir/specials/SpecialTranslationStash.php";
/**@}*/

/**
 * @name   "Various utilities"
 * @{
 */
$al['ArrayFlattener'] = "$dir/utils/ArrayFlattener.php";
$al['CDBMessageIndex'] = "$dir/utils/MessageIndex.php";
$al['CachedMessageIndex'] = "$dir/utils/MessageIndex.php";
$al['DatabaseMessageIndex'] = "$dir/utils/MessageIndex.php";
$al['ExternalMessageSourceStateComparator'] =
	"$dir/utils/ExternalMessageSourceStateComparator.php";
$al['ExternalMessageSourceStateImporter'] =
	"$dir/utils/ExternalMessageSourceStateImporter.php";
$al['FCFontFinder'] = "$dir/utils/Font.php";
$al['FileCachedMessageIndex'] = "$dir/utils/MessageIndex.php";
$al['FuzzyBot'] = "$dir/utils/FuzzyBot.php";
$al['HTMLJsSelectToInputField'] = "$dir/utils/HTMLJsSelectToInputField.php";
$al['HashMessageIndex'] = "$dir/utils/MessageIndex.php";
$al['JsSelectToInput'] = "$dir/utils/JsSelectToInput.php";
$al['MessageChangeStorage'] = "$dir/utils/MessageChangeStorage.php";
$al['MessageGroupCache'] = "$dir/utils/MessageGroupCache.php";
$al['MessageGroupStates'] = "$dir/utils/MessageGroupStates.php";
$al['MessageGroupStatesUpdaterJob'] = "$dir/utils/MessageGroupStatesUpdaterJob.php";
$al['MessageGroupStats'] = "$dir/utils/MessageGroupStats.php";
$al['MessageHandle'] = "$dir/utils/MessageHandle.php";
$al['MessageIndex'] = "$dir/utils/MessageIndex.php";
$al['MessageIndexRebuildJob'] = "$dir/utils/MessageIndexRebuildJob.php";
$al['MessageTable'] = "$dir/utils/MessageTable.php";
$al['MessageUpdateJob'] = "$dir/utils/MessageUpdateJob.php";
$al['MessageWebImporter'] = "$dir/utils/MessageWebImporter.php";
$al['PHPVariableLoader'] = "$dir/utils/ResourceLoader.php";
$al['RevTag'] = "$dir/utils/RevTag.php";
$al['ReviewPerLanguageStats'] = "$dir/specials/SpecialTranslationStats.php";
$al['SerializedMessageIndex'] = "$dir/utils/MessageIndex.php";
$al['StatsBar'] = "$dir/utils/StatsBar.php";
$al['StatsTable'] = "$dir/utils/StatsTable.php";
$al['TTMServer'] = "$dir/utils/TTMServer.php";
$al['TranslateLogFormatter'] = "$dir/utils/TranslateLogFormatter.php";
$al['TranslateMetadata'] = "$dir/utils/TranslateMetadata.php";
$al['TranslatePerLanguageStats'] = "$dir/specials/SpecialTranslationStats.php";
$al['TranslatePreferences'] = "$dir/utils/UserToggles.php";
$al['TranslateRcFilter'] = "$dir/utils/RcFilter.php";
$al['TranslateRegistrationStats'] = "$dir/specials/SpecialTranslationStats.php";
$al['TranslateSandbox'] = "$dir/utils/TranslateSandbox.php";
$al['TranslateSandboxEmailJob'] = "$dir/utils/TranslateSandboxEmailJob.php";
$al['TranslateStatsOutput'] = "$dir/scripts/TranslateStatsOutput.php";
$al['TranslateToolbox'] = "$dir/utils/ToolBox.php";
$al['TranslateYaml'] = "$dir/utils/TranslateYaml.php";
$al['TranslationEditPage'] = "$dir/utils/TranslationEditPage.php";
$al['TranslationHelperException'] = "$dir/utils/TranslationHelpers.php";
$al['TranslationHelpers'] = "$dir/utils/TranslationHelpers.php";
$al['TranslationStats'] = "$dir/utils/TranslationStats.php";
$al['TranslationStatsBase'] = "$dir/specials/SpecialTranslationStats.php";
$al['TranslationStatsInterface'] = "$dir/specials/SpecialTranslationStats.php";
$al['TuxMessageTable'] = "$dir/utils/TuxMessageTable.php";
/**@}*/

/**
 * @name   "Classes for predefined non-managed message groups"
 * @{
 */
$al['PremadeMediawikiExtensionGroups'] = "$dir/ffs/MediaWikiExtensions.php";
$al['PremadeIntuitionTextdomains'] = "$dir/ffs/IntuitionTextdomains.php";
/**@}*/

/**
 * @name   "Support for MediaWiki non-message features"
 * @{
 */
$al['ComplexMessages'] = "$dir/ffs/MediaWikiComplexMessages.php";
$al['MagicWordsCM'] = "$dir/ffs/MediaWikiComplexMessages.php";
$al['NamespaceCM'] = "$dir/ffs/MediaWikiComplexMessages.php";
$al['SpecialPageAliasesCM'] = "$dir/ffs/MediaWikiComplexMessages.php";
/**@}*/

/**
 * @name   "Classes for page translation feature"
 * @ingroup PageTranslation
 * @{
 */
$al['TranslateDeleteJob'] = "$dir/tag/TranslateDeleteJob.php";
$al['TranslateMoveJob'] = "$dir/tag/TranslateMoveJob.php";
$al['PageTranslationHooks'] = "$dir/tag/PageTranslationHooks.php";
$al['PageTranslationLogFormatter'] = "$dir/tag/PageTranslationLogFormatter.php";
$al['TranslateRenderJob'] = "$dir/tag/TranslateRenderJob.php";
$al['TranslationsUpdateJob']= "$dir/tag/TranslationsUpdateJob.php";
$al['SpecialPageTranslation'] = "$dir/tag/SpecialPageTranslation.php";
$al['SpecialPageTranslationDeletePage'] =
	"$dir/tag/SpecialPageTranslationDeletePage.php";
$al['SpecialPageTranslationMovePage'] =
	"$dir/tag/SpecialPageTranslationMovePage.php";
$al['TPException'] = "$dir/tag/TPException.php";
$al['TPParse'] = "$dir/tag/TPParse.php";
$al['TPSection'] = "$dir/tag/TPSection.php";
$al['TranslatablePage'] = "$dir/tag/TranslatablePage.php";
/**@}*/

/**
 * @name   "Classes for TTMServer"
 * @ingroup TTMServer
 * @{
 */
$al['ReadableTTMServer'] = "$dir/ttmserver/Interfaces.php";
$al['SearchableTTMServer'] = "$dir/ttmserver/Interfaces.php";
$al['WritableTTMServer'] = "$dir/ttmserver/Interfaces.php";

$al['TTMServerException'] = "$dir/ttmserver/Exceptions.php";

$al['DatabaseTTMServer'] = "$dir/ttmserver/DatabaseTTMServer.php";
$al['ElasticSearchTTMServer'] = "$dir/ttmserver/ElasticSearchTTMServer.php";
$al['FuzzyLikeThis'] = "$dir/ttmserver/FuzzyLikeThis.php";
$al['FakeTTMServer'] = "$dir/ttmserver/FakeTTMServer.php";
$al['RemoteTTMServer'] = "$dir/ttmserver/RemoteTTMServer.php";
$al['SolrTTMServer'] = "$dir/ttmserver/SolrTTMServer.php";
$al['TTMServer'] = "$dir/ttmserver/TTMServer.php";
$al['TTMServerMessageUpdateJob'] = "$dir/ttmserver/TTMServerMessageUpdateJob.php";
$al['CrossLanguageTranslationSearchQuery'] =
	"$dir/ttmserver/CrossLanguageTranslationSearchQuery.php";
/**@}*/

/**
 * @name   "Classes for file format support (FFS)"
 * @{
 */
$al['AmdFFS'] = "$dir/ffs/AmdFFS.php";
$al['AndroidXmlFFS'] = "$dir/ffs/AndroidXmlFFS.php";
$al['AppleFFS'] = "$dir/ffs/AppleFFS.php";
$al['DtdFFS'] = "$dir/ffs/DtdFFS.php";
$al['FFS'] = "$dir/ffs/FFS.php";
$al['FlatPhpFFS'] = "$dir/ffs/FlatPhpFFS.php";
$al['GettextFFS'] = "$dir/ffs/GettextFFS.php";
$al['GettextPluralException'] = "$dir/ffs/GettextFFS.php";
$al['IniFFS'] = "$dir/ffs/IniFFS.php";
$al['JavaFFS'] = "$dir/ffs/JavaFFS.php";
$al['JavaScriptFFS'] = "$dir/ffs/JavaScriptFFS.php";
$al['JsonFFS'] = "$dir/ffs/JsonFFS.php";
$al['MediaWikiExtensionFFS'] = "$dir/ffs/MediaWikiExtensionFFS.php";
$al['RubyYamlFFS'] = "$dir/ffs/RubyYamlFFS.php";
$al['ShapadoJsFFS'] = "$dir/ffs/JavaScriptFFS.php";
$al['SimpleFFS'] = "$dir/ffs/SimpleFFS.php";
$al['XliffFFS'] = "$dir/ffs/XliffFFS.php";
$al['YamlFFS'] = "$dir/ffs/YamlFFS.php";
/**@}*/

/**
 * @name   "API modules"
 * @{
 */
$al['ApiAggregateGroups'] = "$dir/api/ApiAggregateGroups.php";
$al['ApiGroupReview'] = "$dir/api/ApiGroupReview.php";
$al['ApiQueryLanguageStats'] = "$dir/api/ApiQueryLanguageStats.php";
$al['ApiQueryMessageCollection'] = "$dir/api/ApiQueryMessageCollection.php";
$al['ApiQueryMessageGroupStats'] = "$dir/api/ApiQueryMessageGroupStats.php";
$al['ApiQueryMessageGroups'] = "$dir/api/ApiQueryMessageGroups.php";
$al['ApiQueryMessageTranslations'] = "$dir/api/ApiQueryMessageTranslations.php";
$al['ApiStatsQuery'] = "$dir/api/ApiStatsQuery.php";
$al['ApiTTMServer'] = "$dir/api/ApiTTMServer.php";
$al['ApiSearchTranslations'] = "$dir/api/ApiSearchTranslations.php";
$al['ApiTranslateSandbox'] = "$dir/api/ApiTranslateSandbox.php";
$al['ApiTranslationAids'] = "$dir/api/ApiQueryTranslationAids.php";
$al['ApiTranslationReview'] = "$dir/api/ApiTranslationReview.php";
$al['ApiTranslationStash'] = "$dir/api/ApiTranslationStash.php";
/**@}*/

/**
 * @name   "Task classes"
 * @{
 */
$al['AcceptQueueMessagesTask'] = "$dir/TranslateTasks.php";
$al['CustomFilteredMessagesTask'] = "$dir/TranslateTasks.php";
$al['ReviewAllMessagesTask'] = "$dir/TranslateTasks.php";
$al['ReviewMessagesTask'] = "$dir/TranslateTasks.php";
$al['TranslateTask'] = "$dir/TranslateTasks.php";
$al['ViewMessagesTask'] = "$dir/TranslateTasks.php";
$al['ViewOptionalTask'] = "$dir/TranslateTasks.php";
$al['ViewUntranslatedTask'] = "$dir/TranslateTasks.php";
/**@}*/

/**
 * @name   "Message group classes"
 * @{
 */
$al['AggregateMessageGroup'] = "$dir/messagegroups/AggregateMessageGroup.php";
$al['FileBasedMessageGroup'] = "$dir/messagegroups/FileBasedMessageGroup.php";
$al['MediaWikiExtensionMessageGroup'] =
	"$dir/messagegroups/MediaWikiExtensionMessageGroup.php";
$al['MessageGroup'] = "$dir/messagegroups/MessageGroup.php";
$al['MessageGroupBase'] = "$dir/messagegroups/MessageGroupBase.php";
$al['MessageGroupOld'] = "$dir/messagegroups/MessageGroupOld.php";
$al['RecentMessageGroup'] = "$dir/messagegroups/RecentMessageGroup.php";
$al['RecentAdditionsMessageGroup'] =
	"$dir/messagegroups/RecentAdditionsMessageGroup.php";
$al['SandboxMessageGroup'] = "$dir/messagegroups/SandboxMessageGroup.php";
$al['WikiMessageGroup'] = "$dir/messagegroups/WikiMessageGroup.php";
$al['WikiPageMessageGroup'] = "$dir/messagegroups/WikiPageMessageGroup.php";
$al['WorkflowStatesMessageGroup'] =
	"$dir/messagegroups/WorkflowStatesMessageGroup.php";
/**@}*/

/**
 * @name   "Stash"
 * @{
 */
$al['StashedTranslation'] = "$dir/stash/StashedTranslation.php";
$al['TranslationStashStorage'] = "$dir/stash/TranslationStashStorage.php";
/**@}*/

/**
 * @name   "Test classes"
 * @{
 */
$al['MockFileBasedMessageGroup'] =
	"$dir/tests/phpunit/MockFileBasedMessageGroup.php";
$al['MockMessageCollectionForExport'] =
	"$dir/tests/phpunit/MockMessageCollectionForExport.php";
$al['MockSuperUser'] = "$dir/tests/phpunit/MockSuperUser.php";
$al['MockWikiMessageGroup'] = "$dir/tests/phpunit/MockWikiMessageGroup.php";
$al['MediaWikiInsertablesSuggesterTest'] =
	"$dir/tests/phpunit/insertables/MediaWikiInsertablesSuggesterTest.php";

/**@}*/

/**
 * @name   "Translation aids"
 * @{
 */
$al['CurrentTranslationAid'] = "$dir/translationaids/CurrentTranslationAid.php";
$al['DocumentationAid'] = "$dir/translationaids/DocumentationAid.php";
$al['GettextDocumentationAid'] = "$dir/translationaids/GettextDocumentationAid.php";
$al['InOtherLanguagesAid'] = "$dir/translationaids/InOtherLanguagesAid.php";
$al['InsertablesAid'] = "$dir/translationaids/InsertablesAid.php";
$al['MachineTranslationAid'] = "$dir/translationaids/MachineTranslationAid.php";
$al['MessageDefinitionAid'] = "$dir/translationaids/MessageDefinitionAid.php";
$al['QueryAggregatorAwareTranslationAid'] =
	"$dir/translationaids/QueryAggregatorAwareTranslationAid.php";
$al['SupportAid'] = "$dir/translationaids/SupportAid.php";
$al['TTMServerAid'] = "$dir/translationaids/TTMServerAid.php";
$al['TranslationAid'] = "$dir/translationaids/TranslationAid.php";
$al['UnsupportedTranslationAid'] =
	"$dir/translationaids/UnsupportedTranslationAid.php";
$al['UpdatedDefinitionAid'] = "$dir/translationaids/UpdatedDefinitionAid.php";
/**@}*/

/**
 * @name   "Translation web services"
 * @{
 */
$al['ApertiumWebService'] = "$dir/webservices/ApertiumWebService.php";
$al['CxserverWebService'] = "$dir/webservices/CxserverWebService.php";
$al['MicrosoftWebService'] = "$dir/webservices/MicrosoftWebService.php";
$al['RemoteTTMServerWebService'] = "$dir/webservices/RemoteTTMServerWebService.php";
$al['TranslationQuery'] = "$dir/webservices/TranslationQuery.php";
$al['TranslationQueryResponse'] = "$dir/webservices/TranslationQueryResponse.php";
$al['TranslationWebService'] = "$dir/webservices/TranslationWebService.php";
$al['TranslationWebServiceException'] =
	"$dir/webservices/TranslationWebServiceException.php";
$al['QueryAggregator'] = "$dir/webservices/QueryAggregator.php";
$al['QueryAggregatorAware'] = "$dir/webservices/QueryAggregatorAware.php";
$al['YandexWebService'] = "$dir/webservices/YandexWebService.php";
/**@}*/

/**
 * @name   "Insertables"
 * @{
 */
$al['Insertable'] = "$dir/insertables/Insertable.php";
$al['InsertablesSuggester'] = "$dir/insertables/InsertablesSuggester.php";
$al['MediaWikiInsertablesSuggester'] =
	"$dir/insertables/MediaWikiInsertablesSuggester.php";
$al['TranslatablePageInsertablesSuggester'] =
	"$dir/insertables/TranslatablePageInsertablesSuggester.php";
/**@}*/

/**
 * @name   "StringMangler"
 * @{
 */
$al['StringMangler'] = "$dir/stringmangler/StringMangler.php";
$al['StringMatcher'] = "$dir/stringmangler/StringMatcher.php";
/**@}*/

global $wgAutoloadClasses;
$wgAutoloadClasses = array_merge( $wgAutoloadClasses, $al );
