<?php
/**
 * Contains class with basic non-feature specific hooks.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2011-2013, Niklas Laxström
 * @license GPL-2.0+
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
		$list[NS_TRANSLATIONS] = 'Translations';
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

			global $wgLogActionsHandlers, $wgLogTypes;
			$wgLogTypes[] = 'pagetranslation';
			$wgLogActionsHandlers['pagetranslation/mark'] = 'PageTranslationLogFormatter';
			$wgLogActionsHandlers['pagetranslation/unmark'] = 'PageTranslationLogFormatter';
			$wgLogActionsHandlers['pagetranslation/moveok'] = 'PageTranslationLogFormatter';
			$wgLogActionsHandlers['pagetranslation/movenok'] = 'PageTranslationLogFormatter';
			$wgLogActionsHandlers['pagetranslation/deletelok'] = 'PageTranslationLogFormatter';
			$wgLogActionsHandlers['pagetranslation/deletefok'] = 'PageTranslationLogFormatter';
			$wgLogActionsHandlers['pagetranslation/deletelnok'] = 'PageTranslationLogFormatter';
			$wgLogActionsHandlers['pagetranslation/deletefnok'] = 'PageTranslationLogFormatter';
			$wgLogActionsHandlers['pagetranslation/encourage'] = 'PageTranslationLogFormatter';
			$wgLogActionsHandlers['pagetranslation/discourage'] = 'PageTranslationLogFormatter';
			$wgLogActionsHandlers['pagetranslation/prioritylanguages'] =
				'PageTranslationLogFormatter';
			$wgLogActionsHandlers['pagetranslation/associate'] = 'PageTranslationLogFormatter';
			$wgLogActionsHandlers['pagetranslation/dissociate'] = 'PageTranslationLogFormatter';

			global $wgJobClasses;
			$wgJobClasses['TranslateRenderJob'] = 'TranslateRenderJob';
			$wgJobClasses['RenderJob'] = 'TranslateRenderJob';
			$wgJobClasses['TranslateMoveJob'] = 'TranslateMoveJob';
			$wgJobClasses['MoveJob'] = 'TranslateMoveJob';
			$wgJobClasses['TranslateDeleteJob'] = 'TranslateDeleteJob';
			$wgJobClasses['DeleteJob'] = 'TranslateDeleteJob';

			// Namespaces
			global $wgPageTranslationNamespace;
			global $wgNamespacesWithSubpages, $wgNamespaceProtection;
			global $wgTranslateMessageNamespaces;

			// Define constants for more readable core
			if ( !defined( 'NS_TRANSLATIONS' ) ) {
				define( 'NS_TRANSLATIONS', $wgPageTranslationNamespace );
				define( 'NS_TRANSLATIONS_TALK', $wgPageTranslationNamespace + 1 );
			}

			$wgNamespacesWithSubpages[NS_TRANSLATIONS] = true;
			$wgNamespacesWithSubpages[NS_TRANSLATIONS_TALK] = true;

			// Standard protection and register it for filtering
			$wgNamespaceProtection[NS_TRANSLATIONS] = array( 'translate' );
			$wgTranslateMessageNamespaces[] = NS_TRANSLATIONS;

			/// Page translation hooks

			/// @todo Register our css, is there a better place for this?
			$wgHooks['OutputPageBeforeHTML'][] = 'PageTranslationHooks::injectCss';

			if ( !defined( 'MW_SUPPORTS_CONTENTHANDLER' ) ) {
				// BC 1.20
				$wgHooks['ArticleSaveComplete'][] = 'PageTranslationHooks::onSectionSave';
				$wgHooks['ArticleSave'][] = 'PageTranslationHooks::tpSyntaxCheck';
				$wgHooks['EditFilterMerged'][] = 'PageTranslationHooks::tpSyntaxCheckForEditPage';
				$wgHooks['ArticleSaveComplete'][] = 'PageTranslationHooks::addTranstag';
				$wgHooks['RevisionInsertComplete'][] =
					'PageTranslationHooks::updateTranstagOnNullRevisions';
			} else {
				// Add transver tags and update translation target pages
				$wgHooks['PageContentSaveComplete'][] = 'PageTranslationHooks::onSectionSave';

				// Check syntax for \<translate>
				$wgHooks['PageContentSave'][] = 'PageTranslationHooks::tpSyntaxCheck';
				$wgHooks['EditFilterMergedContent'][] =
					'PageTranslationHooks::tpSyntaxCheckForEditContent';

				// Add transtag to page props for discovery
				$wgHooks['PageContentSaveComplete'][] = 'PageTranslationHooks::addTranstag';
				$wgHooks['RevisionInsertComplete'][] =
					'PageTranslationHooks::updateTranstagOnNullRevisions';
			}

			// Register \<languages/>
			$wgHooks['ParserFirstCallInit'][] = 'TranslateHooks::setupParserHooks';

			// Strip \<translate> tags etc. from source pages when rendering
			$wgHooks['ParserBeforeStrip'][] = 'PageTranslationHooks::renderTagPage';

			// Set the page content language
			$wgHooks['PageContentLanguage'][] = 'PageTranslationHooks::onPageContentLanguage';

			// Prevent editing of unknown pages in Translations namespace
			$wgHooks['getUserPermissionsErrorsExpensive'][] =
				'PageTranslationHooks::preventUnknownTranslations';
			// Prevent editing of translation in restricted languages
			$wgHooks['getUserPermissionsErrorsExpensive'][] =
				'PageTranslationHooks::preventRestrictedTranslations';
			// Prevent editing of translation pages directly
			$wgHooks['getUserPermissionsErrorsExpensive'][] =
				'PageTranslationHooks::preventDirectEditing';

			// Our custom header for translation pages
			$wgHooks['ArticleViewHeader'][] = 'PageTranslationHooks::translatablePageHeader';

			// Custom move page that can move all the associated pages too
			$wgHooks['SpecialPage_initList'][] = 'PageTranslationHooks::replaceMovePage';
			// Locking during page moves
			$wgHooks['getUserPermissionsErrorsExpensive'][] =
				'PageTranslationHooks::lockedPagesCheck';
			// Disable action=delete
			$wgHooks['ArticleConfirmDelete'][] = 'PageTranslationHooks::disableDelete';

			// Replace subpage logic behavior
			$wgHooks['SkinSubPageSubtitle'][] = 'PageTranslationHooks::replaceSubtitle';

			// Show page source code when export tab is opened
			$wgHooks['SpecialTranslate::executeTask'][] = 'PageTranslationHooks::sourceExport';

			// Replaced edit tab with translation tab for translation pages
			$wgHooks['SkinTemplateNavigation'][] = 'PageTranslationHooks::translateTab';
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
		$parser->setFunctionHook(
			'translationdialog',
			array( 'TranslateHooks', 'translationDialogMagicWord' )
		);

		$parser->setHook( 'translate', function () {
			throw new MWException( 'A <translate> reached the parser. This should not happen' );
		} );
		$parser->setHook( 'tvar', function () {
			throw new MWException( 'A <tvar> reached the parser. This should not happen' );
		} );

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

		$updater->addExtensionUpdate( array(
			'addTable',
			'translate_sections',
			"$dir/translate_sections.sql",
			true
		) );
		$updater->addExtensionUpdate( array(
			'addField',
			'translate_sections',
			'trs_order',
			"$dir/translate_sections-trs_order.patch.sql",
			true
		) );
		$updater->addExtensionUpdate( array(
			'addTable',
			'revtag', "$dir/revtag.sql",
			true
		) );
		$updater->addExtensionUpdate( array(
			'addTable',
			'translate_groupstats',
			"$dir/translate_groupstats.sql",
			true
		) );
		$updater->addExtensionUpdate( array(
			'addIndex',
			'translate_sections',
			'trs_page_order',
			"$dir/translate_sections-indexchange.sql",
			true
		) );
		$updater->addExtensionUpdate( array(
			'dropIndex',
			'translate_sections',
			'trs_page',
			"$dir/translate_sections-indexchange2.sql",
			true
		) );
		$updater->addExtensionUpdate( array(
			'addTable',
			'translate_reviews',
			"$dir/translate_reviews.sql",
			true
		) );
		$updater->addExtensionUpdate( array(
			'addTable',
			'translate_groupreviews',
			"$dir/translate_groupreviews.sql",
			true
		) );
		$updater->addExtensionUpdate( array(
			'addTable',
			'translate_tms',
			"$dir/translate_tm.sql",
			true
		) );
		$updater->addExtensionUpdate( array(
			'addTable',
			'translate_metadata',
			"$dir/translate_metadata.sql",
			true
		) );
		$updater->addExtensionUpdate( array(
			'addTable', 'translate_messageindex',
			"$dir/translate_messageindex.sql",
			true
		) );
		$updater->addExtensionUpdate( array(
			'addIndex',
			'translate_groupstats',
			'tgs_lang',
			"$dir/translate_groupstats-indexchange.sql",
			true
		) );
		$updater->addExtensionUpdate( array(
			'addField', 'translate_groupstats',
			'tgs_proofread',
			"$dir/translate_groupstats-proofread.sql",
			true
		) );

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
	 * Hook: TranslateSupportedLanguages
	 */
	public static function translateMessageDocumentationLanguage( &$names, $code ) {
		global $wgTranslateDocumentationLanguageCode;
		if ( $wgTranslateDocumentationLanguageCode ) {
			// Special case the native name, assuming it is given as a string
			if ( $wgTranslateDocumentationLanguageCode === $code ) {
				$code = 'en';
			}

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
	public static function searchProfileForm( SpecialSearch $search, &$form,
		/*string*/$profile, $term, array $opts
	) {
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

		$label = Xml::label(
			wfMessage( 'translate-search-languagefilter' )->text(),
			'languagefilter'
		) . '&#160;';
		$params = array( 'id' => 'mw-searchoptions' );

		$form = Xml::fieldset( false, false, $params ) .
			$hidden . $label . $selector .
			Html::closeElement( 'fieldset' );

		return false;
	}

	/// Hook: SpecialSearchSetupEngine
	public static function searchProfileSetupEngine( SpecialSearch $search,
		/*string*/$profile, SearchEngine $engine
	) {
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

	/**
	 * Parser function hook
	 */
	public static function translationDialogMagicWord( Parser $parser,
		$title = '', $linktext = ''
	) {
		$title = Title::newFromText( $title );
		if ( !$title ) {
			return '';
		}
		$handle = new MessageHandle( $title );
		if ( !$handle->isValid() ) {
			return '';
		}
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

	/// Hook: Translate:MessageGroupStats:isIncluded
	public static function hideDiscouragedFromStats( $id, $code ) {
		// Return true to keep, false to exclude
		return MessageGroups::getPriority( $id ) !== 'discouraged';
	}

	/// Hook: Translate:MessageGroupStats:isIncluded
	public static function hideRestrictedFromStats( $id, $code ) {
		$filterLangs = TranslateMetadata::get( $id, 'prioritylangs' );
		if ( strlen( $filterLangs ) === 0 ) {
			// No restrictions, keep everything
			return true;
		}

		$filter = array_flip( explode( ',', $filterLangs ) );

		// If the language is in the list, return true to not hide it
		return isset( $filter[$code] );
	}

	/// Hook LinksUpdate
	public static function preventCategorization( LinksUpdate $updater ) {
		$handle = new MessageHandle( $updater->getTitle() );
		if ( $handle->isMessageNamespace() && !$handle->isDoc() ) {
			$updater->mCategories = array();
		}

		return true;
	}

	/**
	 * Hook: MakeGlobalVariablesScript
	 *
	 * Adds $wgTranslateDocumentationLanguageCode to ResourceLoader configuration
	 * when Special:Translate is shown.
	 */
	public static function addConfig( &$vars, OutputPage $out ) {
		$request = $out->getRequest();
		$title = $out->getTitle();
		list( $alias, ) = SpecialPageFactory::resolveAlias( $title->getText() );

		if ( SpecialTranslate::isBeta( $request )
			&& $title->isSpecialPage()
			&& ( $alias === 'Translate' || $alias === 'SearchTranslations' )
		) {
			global $wgTranslateDocumentationLanguageCode, $wgTranslatePermissionUrl;
			$vars['TranslateRight'] = $out->getUser()->isAllowed( 'translate' );
			$vars['TranslateMessageReviewRight'] =
				$out->getUser()->isAllowed( 'translate-messagereview' );
			$vars['wgTranslateDocumentationLanguageCode'] = $wgTranslateDocumentationLanguageCode;
			$vars['wgTranslatePermissionUrl'] = $wgTranslatePermissionUrl;
		}

		return true;
	}
}
