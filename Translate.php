<?php
if ( !defined( 'MEDIAWIKI' ) ) die();
/**
 * An extension to ease the translation of Mediawiki and other projects.
 *
 * @addtogroup Extensions
 *
 * @author Niklas Laxström
 * @copyright Copyright © 2006-2009, Niklas Laxström
 * @copyright Copyright © 2007-2008, Siebrand Mazeland
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

define( 'TRANSLATE_VERSION', '11:2009-05-09' );

$wgExtensionCredits['specialpage'][] = array(
	'path'           => __FILE__,
	'name'           => 'Translate',
	'version'        => TRANSLATE_VERSION,
	'author'         => array( 'Niklas Laxström', 'Siebrand Mazeland' ),
	'description'    => '[[Special:Translate|Special page]] for translating Mediawiki and beyond',
	'descriptionmsg' => 'translate-desc',
	'url'            => 'http://www.mediawiki.org/wiki/Extension:Translate',
);

// Setup class autoloads
$dir = dirname( __FILE__ ) . '/';
require_once( $dir . '_autoload.php' );

$wgExtensionMessagesFiles['Translate'] = $dir . 'Translate.i18n.php';
$wgExtensionMessagesFiles['PageTranslation'] = $dir . 'PageTranslation.i18n.php';
$wgExtensionAliasesFiles['Translate'] = $dir . 'Translate.alias.php';
$wgExtensionFunctions[] = 'efTranslateInit';

$wgSpecialPages['Translate'] = 'SpecialTranslate';
$wgSpecialPages['Translations'] = 'SpecialTranslations';
$wgSpecialPages['Magic'] = 'SpecialMagic';
$wgSpecialPages['TranslationChanges'] = 'SpecialTranslationChanges';
$wgSpecialPages['TranslationStats'] = 'SpecialTranslationStats';
$wgSpecialPages['LanguageStats'] = 'SpecialLanguageStats';
$wgSpecialPageGroups['Magic'] = 'wiki';
$wgSpecialPageGroups['Translate'] = 'wiki';
$wgSpecialPageGroups['Translations'] = 'pages';
$wgSpecialPageGroups['TranslationChanges'] = 'changes';
$wgSpecialPageGroups['TranslationStats'] = 'wiki';
$wgSpecialPageGroups['LanguageStats'] = 'wiki';
$wgSpecialPageGroups['PageTranslation'] = 'pagetools';

$wgHooks['EditPage::showEditForm:initial'][] = 'TranslateEditAddons::addTools';
$wgHooks['OutputPageBeforeHTML'][] = 'TranslateEditAddons::addNavigation';
$wgHooks['AlternateEdit'][] = 'TranslateEditAddons::intro';
$wgHooks['EditPageBeforeEditButtons'][] = 'TranslateEditAddons::buttonHack';

$wgDefaultUserOptions['translate'] = 0;
$wgHooks['GetPreferences'][] = 'TranslatePreferences::onGetPreferences';

$wgHooks['SpecialRecentChangesQuery'][] = 'TranslateRcFilter::translationFilter';
$wgHooks['SpecialRecentChangesPanel'][] = 'TranslateRcFilter::translationFilterForm';
$wgHooks['SkinTemplateToolboxEnd'][] = 'TranslateToolbox::toolboxAllTranslations';

$wgEnablePageTranslation = false;
$wgPageTranslationNamespace = 1198;

//$wgJobClasses['RenderJob'] = 'RenderJob';
$wgAvailableRights[] = 'translate';

define( 'TRANSLATE_FUZZY', '!!FUZZY!!' );
define( 'TRANSLATE_INDEXFILE', $dir . 'data/messageindex.ser' );
define( 'TRANSLATE_CHECKFILE', $dir . 'data/messagecheck.ser' );
define( 'TRANSLATE_ALIASFILE', $dir . 'aliases.txt' );

#
# Configuration variables
#

/** Where to look for extension files */
$wgTranslateExtensionDirectory = "$IP/extensions/";

/** Which other language translations are displayed to help translator */
$wgTranslateLanguageFallbacks = array();

/** Name of the fuzzer bot */
$wgTranslateFuzzyBotName = 'FuzzyBot';

/** Address to css if non-default or false */
$wgTranslateCssLocation = $wgScriptPath . '/extensions/Translate';

/** Language code for special documentation language */
$wgTranslateDocumentationLanguageCode = false;

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

$wgTranslateMessageNamespaces = array( NS_MEDIAWIKI );

/** AC = Available classes */
$wgTranslateAC = array(
'core'                      => 'CoreMessageGroup',
'core-mostused'             => 'CoreMostUsedMessageGroup',
);

/**
 * Regexps for putting groups into subgroups. Deepest groups first.
 */
$wgTranslateGroupStructure = array(
	'/^core/' => array( 'core' ),
	'/^ext-collection/' => array( 'ext', 'collection' ),
	'/^ext-flaggedrevs/' => array( 'ext', 'flaggedrevs' ),
	'/^ext-socialprofile/' => array( 'ext', 'socialprofile' ),
	'/^ext-translate/' => array( 'ext', 'translate' ),
	'/^ext-uniwiki/' => array( 'ext', 'uniwiki' ),
	'/^ext/' => array( 'ext' ),
	'/^page\|/' => array( 'page' ),
);

$wgTranslateAddMWExtensionGroups = false;

/** EC = Enabled classes */
$wgTranslateEC = array();
$wgTranslateEC[] = 'core';

/** CC = Custom classes */
$wgTranslateCC = array();

/** Tasks */
$wgTranslateTasks = array(
	'view'                 => 'ViewMessagesTask',
	'untranslated'         => 'ViewUntranslatedTask',
	'optional'             => 'ViewOptionalTask',
	'untranslatedoptional' => 'ViewUntranslatedOptionalTask',
	'problematic'          => 'ViewProblematicTask',
	'review'               => 'ReviewMessagesTask',
	'reviewall'            => 'ReviewAllMessagesTask',
	'export-as-po'         => 'ExportasPoMessagesTask',
//	'export'               => 'ExportMessagesTask',
	'export-to-file'       => 'ExportToFileMessagesTask',
//	'export-to-xliff'      => 'ExportToXliffMessagesTask',
);

/** PHPlot for nice graphs */
$wgTranslatePHPlot = false;
$wgTranslatePHPlotFont = '/usr/share/fonts/truetype/ttf-dejavu/DejaVuSans.ttf';


function wfMemIn() { }
function wfMemOut() { }

function efTranslateInit() {
	global $wgTranslatePHPlot, $wgAutoloadClasses;
	if ( $wgTranslatePHPlot ) {
		$wgAutoloadClasses['PHPlot'] = $wgTranslatePHPlot;
	}

	global $wgEnablePageTranslation;
	if ( $wgEnablePageTranslation ) {

		// Special page + the right to use it
		global $wgSpecialPages, $wgAvailableRights;
		$wgSpecialPages['PageTranslation'] = 'SpecialPageTranslation';
		$wgAvailableRights[] = 'pagetranslation';

		// Namespaces
		global $wgPageTranslationNamespace, $wgExtraNamespaces;
		global $wgNamespacesWithSubpages, $wgNamespaceProtection;
		global $wgTranslateMessageNamespaces;
		// Defines for nice usage
		define ( 'NS_TRANSLATIONS', $wgPageTranslationNamespace );
		define ( 'NS_TRANSLATIONS_TALK', $wgPageTranslationNamespace +1 );
		// Register them as namespaces
		$wgExtraNamespaces[NS_TRANSLATIONS]      = 'Translations';
		$wgExtraNamespaces[NS_TRANSLATIONS_TALK] = 'Translations_talk';
		$wgNamespacesWithSubpages[NS_TRANSLATIONS]      = true;
		$wgNamespacesWithSubpages[NS_TRANSLATIONS_TALK] = true;
		// Standard protection and register it for filtering
		$wgNamespaceProtection[NS_TRANSLATIONS] = array( 'translate' );
		$wgTranslateMessageNamespaces[] = NS_TRANSLATIONS;

		// Page translation hooks
		global $wgHooks;

		// Database schema
		$wgHooks['LoadExtensionSchemaUpdates'][] = 'PageTranslationHooks::schemaUpdates';

		// Do not activate hooks if not setup properly
		if ( !efTranslateCheckPT() ) {
			$wgEnablePageTranslation = false;
			return true;
		}


		// Register our css, is there a better place for this?
		$wgHooks['OutputPageBeforeHTML'][] = 'PageTranslationHooks::injectCss';

		// Add transver tags and update translation target pages
		$wgHooks['ArticleSaveComplete'][] = 'PageTranslationHooks::onSectionSave';

		// Foo
		#$wgHooks['SkinTemplateOutputPageBeforeExec'][] = 'TranslateTagHooks::addSidebar';

		// Register <languages/>
		$wgHooks['ParserFirstCallInit'][] = 'efTranslateInitTags';

		// Strip <translate> tags etc. from source pages when rendering
		$wgHooks['ParserBeforeStrip'][] = 'PageTranslationHooks::renderTagPage';

		// Check syntax for <translate>
		$wgHooks['ArticleSave'][] = 'PageTranslationHooks::tpSyntaxCheck';

		// Add transtag to page props for discovery
		$wgHooks['ArticleSaveComplete'][] = 'PageTranslationHooks::addTranstag';

		// Prevent editing of unknown pages in Translations namespace
		$wgHooks['getUserPermissionsErrorsExpensive'][] = 'PageTranslationHooks::translationsCheck';

		$wgHooks['ArticleViewHeader'][] = 'PageTranslationHooks::test';

	}

}


function efTranslateCheckPT() {
	global $wgHooks, $wgMemc;

	$version = 2;
	global $wgMemc;
	$memcKey = wfMemcKey( 'pt' );
	$ok = $wgMemc->get( $memcKey );
	
	wfLoadExtensionMessages( 'PageTranslation' );
	if ( $ok === $version ) {
		return true;
	}


	// Add our tags if they are not registered yet
	// tp:tag is called also the ready tag
	$tags = array( 'tp:mark', 'tp:tag', 'tp:transver' );

	$dbw = wfGetDB( DB_MASTER );
	if ( !$dbw->tableExists('revtag_type') ) {
		$wgHooks['SiteNoticeAfter'][] = array('efTranslateCheckWarn', wfMsg( 'tpt-install' ) );
		return false;
	}
		
	foreach ( $tags as $tag ) {
		// TODO: use insert ignore
		$field = array( 'rtt_name' => $tag );
		$ret = $dbw->selectField( 'revtag_type', 'rtt_name', $field, __METHOD__ );
		if ( $ret !== $tag ) $dbw->insert( 'revtag_type', $field, __METHOD__ );
	}

	$wgMemc->set( $memcKey, $version );

	return true;
}

function efTranslateCheckWarn( $msg, &$sitenotice ) {
	global $wgOut;
	$sitenotice = $msg;
	$wgOut->enableClientCache( false );
	return true;
}

function efTranslateInitTags( $parser ) {
	// For nice language list in-page
	$parser->setHook( 'languages', array( 'PageTranslationHooks', 'languages' ) );
	return true;
}


if ( !defined('TRANSLATE_CLI') ) {
	function STDOUT() {}
	function STDERR() {}
}
