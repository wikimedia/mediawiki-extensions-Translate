<?php
/**
 * Contains class with basic non-feature specific hooks.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2011, Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

/**
 * Some hooks for Translate extension.
 */
class TranslateHooks {
	/// Hook: CanonicalNamespaces
	public static function setupNamespaces( &$list ) {
		global $wgPageTranslationNamespace;
		if ( !defined( 'NS_TRANSLATIONS' ) ) {
			define( 'NS_TRANSLATIONS', $wgPageTranslationNamespace );
			define( 'NS_TRANSLATIONS_TALK', $wgPageTranslationNamespace + 1 );
		}
		$list[NS_TRANSLATIONS]      = 'Translations';
		$list[NS_TRANSLATIONS_TALK] = 'Translations_talk';
		return true;
	}

	/**
	 * Initialises the extension.
	 * Does late-initialization that is not possible at file level,
	 * because it depends on user configuration.
	 */
	public static function setupTranslate() {
		global $wgTranslatePHPlot, $wgAutoloadClasses, $wgHooks;

		if ( $wgTranslatePHPlot ) {
			$wgAutoloadClasses['PHPlot'] = $wgTranslatePHPlot;
		}

		global $wgReservedUsernames, $wgTranslateFuzzyBotName;
		$wgReservedUsernames[] = $wgTranslateFuzzyBotName;

		// Page translation setup check and init if enabled.
		global $wgEnablePageTranslation;
		if ( $wgEnablePageTranslation ) {
			// Special page and the right to use it
			global $wgSpecialPages, $wgAvailableRights, $wgSpecialPageGroups;
			$wgSpecialPages['PageTranslation'] = 'SpecialPageTranslation';
			$wgSpecialPageGroups['PageTranslation'] = 'pagetools';
			$wgSpecialPages['PageTranslationDeletePage'] = 'SpecialPageTranslationDeletePage';
			$wgSpecialPageGroups['PageTranslationDeletePage'] = 'pagetools';
			$wgAvailableRights[] = 'pagetranslation';

			global $wgLogNames, $wgLogActionsHandlers, $wgLogTypes, $wgLogHeaders;
			$wgLogTypes[] = 'pagetranslation';
			$wgLogHeaders['pagetranslation'] = 'pt-log-header';
			$wgLogNames['pagetranslation'] = 'pt-log-name';
			$wgLogActionsHandlers['pagetranslation/mark'] = 'PageTranslationHooks::formatLogEntry';
			$wgLogActionsHandlers['pagetranslation/unmark'] = 'PageTranslationHooks::formatLogEntry';
			$wgLogActionsHandlers['pagetranslation/moveok'] = 'PageTranslationHooks::formatLogEntry';
			$wgLogActionsHandlers['pagetranslation/movenok'] = 'PageTranslationHooks::formatLogEntry';
			$wgLogActionsHandlers['pagetranslation/deletelok'] = 'PageTranslationHooks::formatLogEntry';
			$wgLogActionsHandlers['pagetranslation/deletefok'] = 'PageTranslationHooks::formatLogEntry';
			$wgLogActionsHandlers['pagetranslation/deletelnok'] = 'PageTranslationHooks::formatLogEntry';
			$wgLogActionsHandlers['pagetranslation/deletefnok'] = 'PageTranslationHooks::formatLogEntry';


			global $wgJobClasses;
			$wgJobClasses['RenderJob'] = 'RenderJob';
			$wgJobClasses['MoveJob'] = 'MoveJob';
			$wgJobClasses['DeleteJob'] = 'DeleteJob';

			// Namespaces
			global $wgPageTranslationNamespace, $wgExtraNamespaces;
			global $wgNamespacesWithSubpages, $wgNamespaceProtection;
			global $wgTranslateMessageNamespaces, $wgVersion;

			// Define constants for more readable core
			if ( !defined( 'NS_TRANSLATIONS' ) ) {
				define( 'NS_TRANSLATIONS', $wgPageTranslationNamespace );
				define( 'NS_TRANSLATIONS_TALK', $wgPageTranslationNamespace + 1 );
			}

			if ( version_compare( $wgVersion, '1.17alpha', '<' ) ) {
				efTranslateNamespaces( $wgExtraNamespaces );
			}

			$wgNamespacesWithSubpages[NS_TRANSLATIONS]      = true;
			$wgNamespacesWithSubpages[NS_TRANSLATIONS_TALK] = true;

			// Standard protection and register it for filtering
			$wgNamespaceProtection[NS_TRANSLATIONS] = array( 'translate' );
			$wgTranslateMessageNamespaces[] = NS_TRANSLATIONS;

			/// Page translation hooks

			/// @todo Register our css, is there a better place for this?
			$wgHooks['OutputPageBeforeHTML'][] = 'PageTranslationHooks::injectCss';

			// Add transver tags and update translation target pages
			$wgHooks['ArticleSaveComplete'][] = 'PageTranslationHooks::onSectionSave';

			// Register \<languages/>
			$wgHooks['ParserFirstCallInit'][] = 'TranslateHooks::setupParserHooks';

			// Strip \<translate> tags etc. from source pages when rendering
			$wgHooks['ParserBeforeStrip'][] = 'PageTranslationHooks::renderTagPage';

			// Check syntax for \<translate>
			$wgHooks['ArticleSave'][] = 'PageTranslationHooks::tpSyntaxCheck';
			$wgHooks['EditFilterMerged'][] = 'PageTranslationHooks::tpSyntaxCheckForEditPage';

			// Set the page content language
			$wgHooks['PageContentLanguage'][] = 'PageTranslationHooks::onPageContentLanguage';

			// Add transtag to page props for discovery
			$wgHooks['ArticleSaveComplete'][] = 'PageTranslationHooks::addTranstag';

			// Prevent editing of unknown pages in Translations namespace
			$wgHooks['getUserPermissionsErrorsExpensive'][] = 'PageTranslationHooks::preventUnknownTranslations';
			// Prevent editing of translation pages directly
			$wgHooks['getUserPermissionsErrorsExpensive'][] = 'PageTranslationHooks::preventDirectEditing';

			// Locking during page moves
			$wgHooks['getUserPermissionsErrorsExpensive'][] = 'PageTranslationHooks::lockedPagesCheck';

			// Our custom header for translation pages
			$wgHooks['ArticleViewHeader'][] = 'PageTranslationHooks::test';

			// Prevent section pages appearing in categories
			$wgHooks['LinksUpdate'][] = 'PageTranslationHooks::preventCategorization';

			// Custom move page that can move all the associated pages too
			$wgHooks['SpecialPage_initList'][] = 'PageTranslationHooks::replaceMovePage';

			// Replace subpage logic behaviour
			$wgHooks['SkinSubPageSubtitle'][] = 'PageTranslationHooks::replaceSubtitle';

			// Disable action=delete
			$wgHooks['ArticleConfirmDelete'][] = 'PageTranslationHooks::disableDelete';
		}
	}

	/**
	 * Hook: ParserFirstCallInit
	 * Registers \<languages> tag with the parser.
	 *
	 * @param $parser Parser
	 *
	 * @return bool
	 */
	public static function setupParserHooks( $parser ) {
		// For nice language list in-page
		$parser->setHook( 'languages', array( 'PageTranslationHooks', 'languages' ) );
		return true;
	}

	/// Hook: UnitTestsList
	public static function setupUnitTests( &$files ) {
		$testDir = dirname( __FILE__ ) . '/tests/';
		$files[] = $testDir . 'MessageGroupBaseTest.php';
		return true;
	}

	/**
	 * Hook: LoadExtensionSchemaUpdates
	 * @param $updater DatabaseUpdater
	 * @return bool
	 */
	public static function schemaUpdates( $updater ) {
		$dir = dirname( __FILE__ ) . '/sql';

		$updater->addExtensionUpdate( array( 'addTable', 'translate_sections', "$dir/translate_sections.sql", true ) );
		$updater->addExtensionUpdate( array( 'addField', 'translate_sections', 'trs_order', "$dir/translate_sections-trs_order.patch.sql", true ) );
		$updater->addExtensionUpdate( array( 'addTable', 'revtag', "$dir/revtag.sql", true ) );
		$updater->addExtensionUpdate( array( 'addTable', 'translate_groupstats', "$dir/translate_groupstats.sql", true ) );
		$updater->addExtensionUpdate( array( 'addIndex', 'translate_sections', 'trs_page_order', "$dir/translate_sections-indexchange.sql", true ) );
		$updater->addExtensionUpdate( array( 'dropIndex', 'translate_sections', 'trs_page', "$dir/translate_sections-indexchange2.sql", true ) );

		return true;
	}

	/// Hook: ParserTestTables
	public static function parserTestTables( &$tables ) {
		$tables[] = 'revtag';
		$tables[] = 'translate_groupstats';
		return true;
	}


	/**
	 * Set the right page content language for message group translations ("Page/xx").
	 * Hook: PageContentLanguage
	 * @param $title Title
	 * @param $pageLang
	 * @return bool
	 */
	public static function onPageContentLanguage( $title, &$pageLang ) {
		global $wgTranslateMessageNamespaces;
		// For translation pages, parse plural, grammar etc with correct language, and set the right direction
		if ( in_array( $title->getNamespace(), $wgTranslateMessageNamespaces ) ) {
			list( , $code ) = TranslateUtils::figureMessage( $title->getText() );
			$pageLang = $code;
		}
		return true;
	}

}
