<?php
/**
 * Contains class with basic non-feature specific hooks.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2011-2012, Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

/**
 * Some hooks for Translate extension.
 */
class TranslateHooks {

	/**
	 * Hook: CanonicalNamespaces
	 * @param $list array
	 * @return bool
	 */
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
			$wgLogActionsHandlers['pagetranslation/encourage'] = 'PageTranslationHooks::formatLogEntry';
			$wgLogActionsHandlers['pagetranslation/discourage'] = 'PageTranslationHooks::formatLogEntry';
			$wgLogActionsHandlers['pagetranslation/prioritylanguages'] = 'PageTranslationHooks::formatLogEntry';
			$wgLogActionsHandlers['pagetranslation/associate'] = 'PageTranslationHooks::formatLogEntry';
			$wgLogActionsHandlers['pagetranslation/dissociate'] = 'PageTranslationHooks::formatLogEntry';

			global $wgJobClasses;
			$wgJobClasses['RenderJob'] = 'RenderJob';
			$wgJobClasses['MoveJob'] = 'MoveJob';
			$wgJobClasses['DeleteJob'] = 'DeleteJob';

			// Namespaces
			global $wgPageTranslationNamespace, $wgExtraNamespaces;
			global $wgNamespacesWithSubpages, $wgNamespaceProtection;
			global $wgTranslateMessageNamespaces;

			// Define constants for more readable core
			if ( !defined( 'NS_TRANSLATIONS' ) ) {
				define( 'NS_TRANSLATIONS', $wgPageTranslationNamespace );
				define( 'NS_TRANSLATIONS_TALK', $wgPageTranslationNamespace + 1 );
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
			$wgHooks['RevisionInsertComplete'][] = 'PageTranslationHooks::updateTranstagOnNullRevisions';

			// Prevent editing of unknown pages in Translations namespace
			$wgHooks['getUserPermissionsErrorsExpensive'][] = 'PageTranslationHooks::preventUnknownTranslations';
			// Prevent editing of translation in restricted languages
			$wgHooks['getUserPermissionsErrorsExpensive'][] = 'PageTranslationHooks::preventRestrictedTranslations';
			// Prevent editing of translation pages directly
			$wgHooks['getUserPermissionsErrorsExpensive'][] = 'PageTranslationHooks::preventDirectEditing';

			// Locking during page moves
			$wgHooks['getUserPermissionsErrorsExpensive'][] = 'PageTranslationHooks::lockedPagesCheck';

			// Our custom header for translation pages
			$wgHooks['ArticleViewHeader'][] = 'PageTranslationHooks::translatablePageHeader';

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
		$parser->setFunctionHook( 'translationdialog', array( 'TranslateHooks', 'translationDialogMagicWord' ) );
		return true;
	}

	/**
	 * Hook: UnitTestsList
	 * @param $files array
	 * @return bool
	 */
	public static function setupUnitTests( &$files ) {
		$testDir = __DIR__ . '/tests/';
		$files = array_merge( $files, glob( "$testDir/*Test.php" ) );
		return true;
	}

	/**
	 * Hook: LoadExtensionSchemaUpdates
	 * @param $updater DatabaseUpdater
	 * @return bool
	 */
	public static function schemaUpdates( $updater ) {
		$dir = __DIR__ . '/sql';

		$updater->addExtensionUpdate( array( 'addTable', 'translate_sections', "$dir/translate_sections.sql", true ) );
		$updater->addExtensionUpdate( array( 'addField', 'translate_sections', 'trs_order', "$dir/translate_sections-trs_order.patch.sql", true ) );
		$updater->addExtensionUpdate( array( 'addTable', 'revtag', "$dir/revtag.sql", true ) );
		$updater->addExtensionUpdate( array( 'addTable', 'translate_groupstats', "$dir/translate_groupstats.sql", true ) );
		$updater->addExtensionUpdate( array( 'addIndex', 'translate_sections', 'trs_page_order', "$dir/translate_sections-indexchange.sql", true ) );
		$updater->addExtensionUpdate( array( 'dropIndex', 'translate_sections', 'trs_page', "$dir/translate_sections-indexchange2.sql", true ) );
		$updater->addExtensionUpdate( array( 'addTable', 'translate_reviews', "$dir/translate_reviews.sql", true ) );
		$updater->addExtensionUpdate( array( 'addTable', 'translate_groupreviews', "$dir/translate_groupreviews.sql", true ) );
		$updater->addExtensionUpdate( array( 'addTable', 'translate_tms', "$dir/translate_tm.sql", true ) );
		$updater->addExtensionUpdate( array( 'addTable', 'translate_metadata', "$dir/translate_metadata.sql", true ) );
		$updater->addExtensionUpdate( array( 'addTable', 'translate_messageindex', "$dir/translate_messageindex.sql", true ) );
		$updater->addExtensionUpdate( array( 'addIndex', 'translate_groupstats', 'tgs_lang', "$dir/translate_groupstats-indexchange.sql", true ) );
		$updater->addExtensionUpdate( array( 'addField', 'translate_groupstats', 'tgs_proofread', "$dir/translate_groupstats-proofread.sql", true ) );
		return true;
	}

	/**
	 * Hook: ParserTestTables
	 * @param $tables array
	 * @return bool
	 */
	public static function parserTestTables( &$tables ) {
		$tables[] = 'revtag';
		$tables[] = 'translate_groupstats';
		$tables[] = 'translate_messageindex';
		return true;
	}

	/**
	 * Set the correct page content language for translation units.
	 * Hook: PageContentLanguage
	 * @param $title Title
	 * @param $pageLang
	 * @return bool
	 */
	public static function onPageContentLanguage( Title $title, &$pageLang ) {
		$handle = new MessageHandle( $title );
		if ( $handle->isMessageNamespace() ) {
			$pageLang = $handle->getEffectiveLanguageCode();
		}
		return true;
	}

	/**
	 * Hook: LanguageGetTranslatedLanguageNames
	 */
	public static function translateMessageDocumentationLanguage( &$names, $code ) {
		global $wgTranslateDocumentationLanguageCode;
		if ( $wgTranslateDocumentationLanguageCode ) {
			$names[$wgTranslateDocumentationLanguageCode] =
				wfMessage( 'translate-documentation-language' )->inLanguage( $code )->plain();
		}
		return true;
	}

	/**
	 * Hook: SpecialSearchProfiles
	 */
	public static function searchProfile( array &$profiles ) {
		global $wgTranslateMessageNamespaces;
		$insert = array();
		$insert['translation'] = array(
			'message' => 'translate-searchprofile',
			'tooltip' => 'translate-searchprofile-tooltip',
			'namespaces' => $wgTranslateMessageNamespaces,
		);

		$profiles = wfArrayInsertAfter( $profiles, $insert, 'help' );
		return true;
	}

	/**
	 * Hook: SpecialSearchProfileForm
	 */
	public static function searchProfileForm( SpecialSearch $search, &$form, /*string*/ $profile, $term, array $opts ) {
		if ( $profile !== 'translation' ) {
			return true;
		}

		if ( !$search->getSearchEngine()->supports( 'title-suffix-filter' ) ) {
			return false;
		}

		$hidden = '';
		foreach ( $opts as $key => $value ) {
			$hidden .= Html::hidden( $key, $value );
		}

		$context = $search->getContext();
		$code = $context->getLanguage()->getCode();
		$selected = $context->getRequest()->getVal( 'languagefilter' );

		if ( is_callable( array( 'LanguageNames', 'getNames' ) ) ) {
			$languages = LanguageNames::getNames( $code,
				LanguageNames::FALLBACK_NORMAL,
				LanguageNames::LIST_MW
			);
		} else {
			$languages = Language::getLanguageNames( false );
		}

		ksort( $languages );

		$selector = new XmlSelect( 'languagefilter', 'languagefilter' );
		$selector->setDefault( $selected );
		$selector->addOption( wfMessage( 'translate-search-nofilter' )->text(), '-' );
		foreach ( $languages as $code => $name ) {
			$selector->addOption( "$code - $name", $code );
		}

		$selector = $selector->getHTML();

		$label = Xml::label( wfMessage( 'translate-search-languagefilter' )->text(), 'languagefilter' ) . '&#160;';
		$params = array( 'id' => 'mw-searchoptions' );

		$form = Xml::fieldset( false, false, $params ) .
			$hidden . $label . $selector .
			Html::closeElement( 'fieldset' );
		return false;
	}

	/// Hook: SpecialSearchSetupEngine
	public static function searchProfileSetupEngine( $search, /*string*/ $profile, SearchEngine $engine ) {
		if ( $profile !== 'translation' ) {
			return true;
		}

		$context = $search->getContext();
		$selected = $context->getRequest()->getVal( 'languagefilter' );
		if ( $selected !== '-' && $selected ) {
			$engine->setFeatureData( 'title-suffix-filter', "/$selected" );
			$search->setExtraParam( 'languagefilter', $selected );
		}
		return true;
	}

	/// Log action handler
	public static function formatTranslationreviewLogEntry( $type, $action, $title, $forUI, $params ) {
		global $wgLang, $wgContLang;

		$language = $forUI === null ? $wgContLang : $wgLang;

		if ( $action === 'message' ) {
			$link = $forUI ?
				Linker::link( $title, null, array(), array( 'oldid' => $params[0] ) ) :
				$title->getPrefixedText();
			return wfMessage( 'logentry-translationreview-message' )->params(
				'', // User link in the new system
				'#', // User name for gender in the new system
				Message::rawParam( $link )
			)->inLanguage( $language )->text();
		}

		if ( $action === 'group' ) {
			$languageCode = $params[0];
			$languageNames = Language::getTranslatedLanguageNames( $language->getCode() );
			$languageName = "$languageNames[$languageCode] ($languageCode)";
			$groupLabel = $params[1];
			$oldState = $params[2];
			$newState = $params[3];
			$oldStateMessage = wfMessage( "translate-workflow-state-$oldState" );
			$newStateMessage = wfMessage( "translate-workflow-state-$newState" );
			$oldState = $oldStateMessage->isBlank() ? $oldState : $oldStateMessage->text();
			$newState = $newStateMessage->isBlank() ? $newState : $newStateMessage->text();

			$link = $forUI ?
				Linker::link( $title, $groupLabel, array(), array( 'language' => $languageCode ) ) :
				$groupLabel;

			return wfMessage( 'logentry-groupreview-message' )->params(
				'', // User link in the new system
				'#', // User name for gender in the new system
				Message::rawParam( $link ),
				$languageName,
				$oldState,
				$newState
			)->inLanguage( $language )->text();
		}

		return '';
	}

	/**
	 * Parser function hook
	 */
	public static function translationDialogMagicWord( Parser $parser, $title = '', $linktext = '' ) {
		$title = Title::newFromText( $title );
		if ( !$title ) return '';
		$handle = new MessageHandle( $title );
		if ( !$handle->isValid() ) return '';
		$group = $handle->getGroup();
		$callParams = array( $title->getPrefixedText(), $group->getId() );
		$call = Xml::encodeJsCall( 'mw.translate.openDialog', $callParams );
		$js = <<<JAVASCRIPT
mw.loader.using( 'ext.translate.quickedit', function() { $call; } ); return false;
JAVASCRIPT;

		$a = array(
			'href' => $title->getFullUrl( array( 'action' => 'edit' ) ),
			'onclick' => $js,
		);

		if ( $linktext === '' ) {
			$linktext = wfMessage( 'translate-edit-jsopen' )->text();
		}
		$output = Html::element( 'a', $a, $linktext );
		return $parser->insertStripItem( $output, $parser->mStripState );
	}

	/**
	 * Shovels the new translation into TTMServer.
	 * Hook: Translate:newTranslation
	 */
	public static function updateTM( MessageHandle $handle, $revision, $text, User $user ) {
		TTMServer::primary()->update( $handle, $text );
		return true;
	}

	/// Hook: Translate:MessageGroupStats:isIncluded
	public static function hideDiscouragedFromStats( $id, $code ) {
		// Return true to keep, false to exclude
		return MessageGroups::getPriority( $id ) !== 'discouraged';
	}

	/// Hook: Translate:MessageGroupStats:isIncluded
	public static function hideRestrictedFromStats( $id, $code ) {
		$filterLangs = TranslateMetadata::get( $id, 'prioritylangs' );
		$priorityForce = TranslateMetadata::get( $id, 'priorityforce' );
		if ( strlen( $filterLangs ) === 0 || $priorityForce === 'off' ) {
			// No restrictions, keep everything
			return true;
		}

		$filter = array_flip( explode( ',', $filterLangs ) );
		// If the language is in the list, return true to not hide it
		return isset( $filter[$code] );
	}

}

