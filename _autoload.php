<?php
/**
 * Autoload definitions.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2008-2010, Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

/** @cond file_level_code */
$dir = dirname( __FILE__ ) . '/';
/** @endcond */

/**
 * @name   Core extension classes
 * @{
 */
$wgAutoloadClasses['TranslateTasks'] = $dir . 'TranslateTasks.php';
$wgAutoloadClasses['TaskOptions'] = $dir . 'TranslateTasks.php';

$wgAutoloadClasses['TranslateUtils'] = $dir . 'TranslateUtils.php';
$wgAutoloadClasses['HTMLSelector'] = $dir . 'TranslateUtils.php';

$wgAutoloadClasses['MessageChecker'] = $dir . 'MessageChecks.php';

$wgAutoloadClasses['MessageGroup'] = $dir . 'Groups.php';
$wgAutoloadClasses['MessageGroupBase'] = $dir . 'Groups.php';
$wgAutoloadClasses['FileBasedMessageGroup'] = $dir . 'Groups.php';

$wgAutoloadClasses['MessageGroupOld'] = $dir . 'MessageGroups.php';
$wgAutoloadClasses['MessageGroups'] = $dir . 'MessageGroups.php';
$wgAutoloadClasses['WikiPageMessageGroup'] = $dir . 'MessageGroups.php';
$wgAutoloadClasses['AliasMessageGroup'] = $dir . 'MessageGroups.php';

$wgAutoloadClasses['MessageCollection'] = $dir . 'MessageCollection.php';
$wgAutoloadClasses['MessageDefinitions'] = $dir . 'MessageCollection.php';
$wgAutoloadClasses['TestMessageCollection'] = $dir . 'MessageCollection.php';
$wgAutoloadClasses['TMessage'] = $dir . 'Message.php';
$wgAutoloadClasses['ThinMessage'] = $dir . 'Message.php';
$wgAutoloadClasses['FatMessage'] = $dir . 'Message.php';

$wgAutoloadClasses['TranslateEditAddons'] = $dir . 'TranslateEditAddons.php';
$wgAutoloadClasses['TranslateRcFilter'] = $dir . 'RcFilter.php';
/**@}*/

/**
 * @name   MediaWiki core classes
 * These are not autoloaded by default in MediaWiki core.
 * @{
 */
$wgAutoloadClasses['languages'] = $IP . '/maintenance/language/languages.inc';
$wgAutoloadClasses['MessageWriter'] = $IP . '/maintenance/language/writeMessagesArray.inc';
/**@}*/

/**
 * @name   Special pages
 * There are few more special pages in page translation section.
 * @{
 */
$wgAutoloadClasses['SpecialTranslate'] = $dir . 'TranslatePage.php';
$wgAutoloadClasses['SpecialMagic'] = $dir . 'SpecialMagic.php';
$wgAutoloadClasses['SpecialTranslationChanges'] = $dir . 'SpecialTranslationChanges.php';
$wgAutoloadClasses['SpecialTranslationStats'] = $dir . 'SpecialTranslationStats.php';
$wgAutoloadClasses['SpecialTranslations'] = $dir . 'SpecialTranslations.php';
$wgAutoloadClasses['SpecialLanguageStats'] = $dir . 'SpecialLanguageStats.php';
$wgAutoloadClasses['SpecialImportTranslations'] = $dir . 'SpecialImportTranslations.php';
$wgAutoloadClasses['SpecialFirstSteps'] = $dir . 'SpecialFirstSteps.php';
$wgAutoloadClasses['SpecialSupportedLanguages'] = $dir . 'SpecialSupportedLanguages.php';
$wgAutoloadClasses['SpecialMyLanguage'] = $dir . 'SpecialMyLanguage.php';
$wgAutoloadClasses['SpecialManageGroups'] = $dir . 'SpecialManageGroups.php';
/**@}*/

/**
 * @name   Old-style file format support (FFS)
 * @{
 */
$wgAutoloadClasses['SimpleFormatReader'] = $dir . 'ffs/Simple.php';
$wgAutoloadClasses['SimpleFormatWriter'] = $dir . 'ffs/Simple.php';
$wgAutoloadClasses['WikiFormatReader'] = $dir . 'ffs/Wiki.php';
$wgAutoloadClasses['WikiFormatWriter'] = $dir . 'ffs/Wiki.php';
$wgAutoloadClasses['WikiExtensionFormatReader'] = $dir . 'ffs/WikiExtension.php';
$wgAutoloadClasses['WikiExtensionFormatWriter'] = $dir . 'ffs/WikiExtension.php';
$wgAutoloadClasses['GettextFormatReader'] = $dir . 'ffs/Gettext.php';
$wgAutoloadClasses['GettextFormatWriter'] = $dir . 'ffs/Gettext.php';
$wgAutoloadClasses['OpenLayersFormatReader'] = $dir . 'ffs/OpenLayers.php';
$wgAutoloadClasses['OpenLayersFormatWriter'] = $dir . 'ffs/OpenLayers.php';
$wgAutoloadClasses['XliffFormatWriter'] = $dir . 'ffs/Xliff.php';
/**@}*/

/**
 * @name   Various utilities
 * @{
 */
$wgAutoloadClasses['ResourceLoader'] = $dir . 'utils/ResourceLoader.php';
$wgAutoloadClasses['StringMangler'] = $dir . 'utils/StringMatcher.php';
$wgAutoloadClasses['StringMatcher'] = $dir . 'utils/StringMatcher.php';
$wgAutoloadClasses['FCFontFinder'] = $dir . 'utils/Font.php';

$wgAutoloadClasses['ArrayMemoryCache'] = $dir . 'utils/MemoryCache.php';

$wgAutoloadClasses['TranslatePreferences'] = $dir . 'utils/UserToggles.php';
$wgAutoloadClasses['TranslateToolbox'] = $dir . 'utils/ToolBox.php';

$wgAutoloadClasses['MessageIndexRebuilder'] = $dir . 'utils/MessageIndexRebuilder.php';
$wgAutoloadClasses['MessageTable'] = $dir . 'utils/MessageTable.php';
$wgAutoloadClasses['JsSelectToInput'] = $dir . 'utils/JsSelectToInput.php';
$wgAutoloadClasses['HTMLJsSelectToInputField'] = $dir . 'utils/HTMLJsSelectToInputField.php';
$wgAutoloadClasses['MessageGroupCache'] = $dir . 'utils/MessageGroupCache.php';
$wgAutoloadClasses['MessageWebImporter'] = $dir . 'utils/MessageWebImporter.php';
$wgAutoloadClasses['TranslationEditPage'] = $dir . 'utils/TranslationEditPage.php';
$wgAutoloadClasses['TranslationHelpers'] = $dir . 'utils/TranslationHelpers.php';
$wgAutoloadClasses['TranslationStats'] = $dir . 'utils/TranslationStats.php';

$wgAutoloadClasses['TranslationMemoryUpdater'] = $dir . 'utils/TranslationMemoryUpdater.php';

$wgAutoloadClasses['TranslateYaml'] = $dir . 'utils/TranslateYaml.php';
/**@}*/

/**
 * @name   Classes for predefined old-style message groups
 * @{
 */
$wgAutoloadClasses['AllMediawikiExtensionsGroup'] = $dir . 'groups/MediaWikiExtensions.php';
$wgAutoloadClasses['PremadeMediawikiExtensionGroups'] = $dir . 'groups/MediaWikiExtensions.php';
$wgAutoloadClasses['PremadeWikiaExtensionGroups'] = $dir . 'groups/Wikia/WikiaExtensions.php';
$wgAutoloadClasses['OpenLayersMessageGroup'] = $dir . 'groups/OpenLayers.php';
$wgAutoloadClasses['MediaWikiMessageChecker'] = $dir . 'groups/MediaWiki/Checker.php';
/**@}*/

/**
 * @name   Non-message translation item support
 * @{
 */
$wgAutoloadClasses['ComplexMessages'] = $dir . 'groups/ComplexMessages.php';
$wgAutoloadClasses['SpecialPageAliasesCM'] = $dir . 'groups/ComplexMessages.php';
$wgAutoloadClasses['MagicWordsCM'] = $dir . 'groups/ComplexMessages.php';
$wgAutoloadClasses['NamespaceCM'] = $dir . 'groups/ComplexMessages.php';
/**@}*/

/**
 * @name   Classes for page translation feature
 * @ingroup PageTranslation
 * @{
 */
$wgAutoloadClasses['PageTranslationHooks'] = $dir . 'tag/PageTranslationHooks.php';
$wgAutoloadClasses['TranslatablePage'] = $dir . 'tag/TranslatablePage.php';
$wgAutoloadClasses['TPException'] = $dir . 'tag/TranslatablePage.php';
$wgAutoloadClasses['TPParse'] = $dir . 'tag/TPParse.php';
$wgAutoloadClasses['TPSection'] = $dir . 'tag/TPSection.php';
$wgAutoloadClasses['SpecialPageTranslation'] = $dir . 'tag/SpecialPageTranslation.php';
$wgAutoloadClasses['SpecialPageTranslationMovePage'] = $dir . 'tag/SpecialPageTranslationMovePage.php';
$wgAutoloadClasses['RenderJob'] = $dir . 'tag/RenderJob.php';
$wgAutoloadClasses['MoveJob'] = $dir . 'tag/MoveJob.php';
/**@}*/

/**
 * @name   Classes for new-style file format support (FFS)
 * @{
 */
$wgAutoloadClasses['FFS'] = $dir . 'FFS.php';
$wgAutoloadClasses['SimpleFFS'] = $dir . 'FFS.php';
$wgAutoloadClasses['JavaFFS'] = $dir . 'FFS.php';
$wgAutoloadClasses['YamlFFS'] = $dir . 'FFS.php';
$wgAutoloadClasses['RubyYamlFFS'] = $dir . 'FFS.php';
$wgAutoloadClasses['JavaScriptFFS'] = $dir . 'FFS.php';
$wgAutoloadClasses['OpenLayersFFS'] = $dir . 'FFS.php';
$wgAutoloadClasses['ShapadoJsFFS'] = $dir . 'FFS.php';
$wgAutoloadClasses['GettextFFS'] = $dir . '/ffs/Gettext.php';
$wgAutoloadClasses['FlatPhpFFS'] = $dir . 'ffs/PhpVariables.php';
$wgAutoloadClasses['DtdFFS'] = $dir . 'ffs/DTD.php';
/**@}*/

/**
 * @name   Classes for different kind of html building
 * @{
 */
$wgAutoloadClasses['HtmlTag'] = $dir . 'utils/Html.php';
$wgAutoloadClasses['RawHtml'] = $dir . 'utils/Html.php';
$wgAutoloadClasses['TagContainer'] = $dir . 'utils/Html.php';
/**@}*/
