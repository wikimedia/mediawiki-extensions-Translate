<?php
/**
 * Contains class with basic non-feature specific hooks.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0+
 */

/**
 * Some hooks for Translate extension.
 */
class TranslateHooks {
	/**
	 * Hook: ResourceLoaderTestModules
	 */
	public static function onResourceLoaderTestModules( array &$modules ) {
		$modules['qunit']['ext.translate.parsers.test'] = array(
			'scripts' => array( 'tests/qunit/ext.translate.parsers.test.js' ),
			'dependencies' => array( 'ext.translate.parsers' ),
			'localBasePath' => __DIR__,
			'remoteExtPath' => 'Translate',
		);

		$modules['qunit']['ext.translate.special.pagemigration.test'] = array(
			'scripts' => array( 'tests/qunit/ext.translate.special.pagemigration.test.js' ),
			'dependencies' => array( 'ext.translate.special.pagemigration' ),
			'localBasePath' => __DIR__,
			'remoteExtPath' => 'Translate',
		);
	}

	/**
	 * Hook: CanonicalNamespaces
	 *
	 * @param array $list
	 */
	public static function setupNamespaces( array &$list ) {
		global $wgPageTranslationNamespace, $wgNamespaceRobotPolicies;
		if ( !defined( 'NS_TRANSLATIONS' ) ) {
			define( 'NS_TRANSLATIONS', $wgPageTranslationNamespace );
			define( 'NS_TRANSLATIONS_TALK', $wgPageTranslationNamespace + 1 );
		}
		$list[NS_TRANSLATIONS] = 'Translations';
		$list[NS_TRANSLATIONS_TALK] = 'Translations_talk';
		$wgNamespaceRobotPolicies[NS_TRANSLATIONS] = 'noindex';
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

		// Page translation setup check and init if enabled.
		global $wgEnablePageTranslation;
		if ( $wgEnablePageTranslation ) {
			// Special page and the right to use it
			global $wgSpecialPages, $wgAvailableRights;
			$wgSpecialPages['PageTranslation'] = 'SpecialPageTranslation';
			$wgSpecialPages['PageTranslationDeletePage'] = 'SpecialPageTranslationDeletePage';

			// right-pagetranslation action-pagetranslation
			$wgAvailableRights[] = 'pagetranslation';

			$wgSpecialPages['PageMigration'] = 'SpecialPageMigration';
			$wgSpecialPages['PagePreparation'] = 'SpecialPagePreparation';

			global $wgActionFilteredLogs, $wgLogActionsHandlers, $wgLogTypes;

			// log-description-pagetranslation log-name-pagetranslation logentry-pagetranslation-mark
			// logentry-pagetranslation-unmark logentry-pagetranslation-moveok
			// logentry-pagetranslation-movenok logentry-pagetranslation-deletefok
			// logentry-pagetranslation-deletefnok logentry-pagetranslation-deletelok
			// logentry-pagetranslation-deletelnok logentry-pagetranslation-encourage
			// logentry-pagetranslation-discourage logentry-pagetranslation-prioritylanguages
			// logentry-pagetranslation-associate logentry-pagetranslation-dissociate
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
			$wgActionFilteredLogs['pagetranslation'] = array(
				'mark' => array( 'mark' ),
				'unmark' => array( 'unmark' ),
				'move' => array( 'moveok', 'movenok' ),
				'delete' => array( 'deletefok', 'deletefnok', 'deletelok', 'deletelnok' ),
				'encourage' => array( 'encourage' ),
				'discourage' => array( 'discourage' ),
				'prioritylanguages' => array( 'prioritylanguages' ),
				'aggregategroups' => array( 'associate', 'dissociate' ),
			);

			global $wgJobClasses;
			$wgJobClasses['TranslateRenderJob'] = 'TranslateRenderJob';
			$wgJobClasses['RenderJob'] = 'TranslateRenderJob';
			$wgJobClasses['TranslateMoveJob'] = 'TranslateMoveJob';
			$wgJobClasses['MoveJob'] = 'TranslateMoveJob';
			$wgJobClasses['TranslateDeleteJob'] = 'TranslateDeleteJob';
			$wgJobClasses['DeleteJob'] = 'TranslateDeleteJob';
			$wgJobClasses['TranslationsUpdateJob'] = 'TranslationsUpdateJob';

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

			// Check syntax for \<translate>
			$wgHooks['PageContentSave'][] = 'PageTranslationHooks::tpSyntaxCheck';
			$wgHooks['EditFilterMergedContent'][] =
				'PageTranslationHooks::tpSyntaxCheckForEditContent';

			// Add transtag to page props for discovery
			$wgHooks['PageContentSaveComplete'][] = 'PageTranslationHooks::addTranstag';
			$wgHooks['RevisionInsertComplete'][] =
				'PageTranslationHooks::updateTranstagOnNullRevisions';

			// Register \<languages/>
			$wgHooks['ParserFirstCallInit'][] = 'TranslateHooks::setupParserHooks';

			// Strip \<translate> tags etc. from source pages when rendering
			$wgHooks['ParserBeforeStrip'][] = 'PageTranslationHooks::renderTagPage';

			// Set the page content language
			$wgHooks['PageContentLanguage'][] = 'PageTranslationHooks::onPageContentLanguage';

			// Prevent editing of certain pages in translations namespace
			$wgHooks['getUserPermissionsErrorsExpensive'][] =
				'PageTranslationHooks::onGetUserPermissionsErrorsExpensive';
			// Prevent editing of translation pages directly
			$wgHooks['getUserPermissionsErrorsExpensive'][] =
				'PageTranslationHooks::preventDirectEditing';
			// Prevent patroling of translation pages
			$wgHooks['getUserPermissionsErrors'][] =
				'PageTranslationHooks::preventPatrolling';

			// Our custom header for translation pages
			$wgHooks['ArticleViewHeader'][] = 'PageTranslationHooks::translatablePageHeader';

			// Edit notice shown on translatable pages
			$wgHooks['TitleGetEditNotices'][] = 'PageTranslationHooks::onTitleGetEditNotices';

			// Custom move page that can move all the associated pages too
			$wgHooks['SpecialPage_initList'][] = 'PageTranslationHooks::replaceMovePage';
			// Locking during page moves
			$wgHooks['getUserPermissionsErrorsExpensive'][] =
				'PageTranslationHooks::lockedPagesCheck';
			// Disable action=delete
			$wgHooks['ArticleConfirmDelete'][] = 'PageTranslationHooks::disableDelete';

			// Replace subpage logic behavior
			$wgHooks['SkinSubPageSubtitle'][] = 'PageTranslationHooks::replaceSubtitle';

			// Replaced edit tab with translation tab for translation pages
			$wgHooks['SkinTemplateNavigation'][] = 'PageTranslationHooks::translateTab';

			// Update translated page when translation unit is moved
			$wgHooks['TitleMoveComplete'][] = 'PageTranslationHooks::onMoveTranslationUnits';

			// Update translated page when translation unit is deleted
			$wgHooks['ArticleDeleteComplete'][] = 'PageTranslationHooks::onDeleteTranslationUnit';
		}

		global $wgTranslateUseSandbox;
		if ( $wgTranslateUseSandbox ) {
			global $wgSpecialPages, $wgAvailableRights, $wgDefaultUserOptions;

			$wgSpecialPages['ManageTranslatorSandbox'] = 'SpecialManageTranslatorSandbox';
			$wgSpecialPages['TranslationStash'] = 'SpecialTranslationStash';
			$wgDefaultUserOptions['translate-sandbox'] = '';
			// right-translate-sandboxmanage action-translate-sandboxmanage
			$wgAvailableRights[] = 'translate-sandboxmanage';

			$wgHooks['GetPreferences'][] = 'TranslateSandbox::onGetPreferences';
			$wgHooks['UserGetRights'][] = 'TranslateSandbox::enforcePermissions';
			$wgHooks['ApiCheckCanExecute'][] = 'TranslateSandbox::onApiCheckCanExecute';
			$wgHooks['UserGetRights'][] = 'TranslateSandbox::allowAccountCreation';

			global $wgLogTypes, $wgLogActionsHandlers;
			// log-name-translatorsandbox log-description-translatorsandbox
			$wgLogTypes[] = 'translatorsandbox';
			// logentry-translatorsandbox-promoted logentry-translatorsandbox-rejected
			$wgLogActionsHandlers['translatorsandbox/promoted'] = 'TranslateLogFormatter';
			$wgLogActionsHandlers['translatorsandbox/rejected'] = 'TranslateLogFormatter';

			// This is no longer used for new entries since 2016.07.
			// logentry-newusers-tsbpromoted
			$wgLogActionsHandlers['newusers/tsbpromoted'] = 'LogFormatter';

			global $wgJobClasses;
			$wgJobClasses['TranslateSandboxEmailJob'] = 'TranslateSandboxEmailJob';
		}
	}

	/**
	 * Hook: UserGetReservedNames
	 * Prevents anyone from registering or logging in as FuzzyBot
	 *
	 * @param array $names
	 */
	public static function onUserGetReservedNames( array &$names ) {
		global $wgTranslateFuzzyBotName;
		$names[] = $wgTranslateFuzzyBotName;
	}

	/**
	 * Used for setting an AbuseFilter variable.
	 *
	 * @param AbuseFilterVariableHolder &$vars
	 * @param Title|null $title
	 */
	public static function onAbuseFilterFilterAction( &$vars, $title ) {
		if ( !$title instanceof Title ) {
			wfDebugLog( 'T143073', 'Got non-Title in ' . wfGetAllCallers( 5 ) );
			return;
		}

		$handle = new MessageHandle( $title );

		// Only set this variable if we are in a proper namespace to avoid
		// unnecessary overhead in non-translation pages
		if ( $handle->isMessageNamespace() ) {
			$vars->setLazyLoadVar(
				'translate_source_text',
				'translate-get-source',
				array( 'handle' => $handle )
			);
		}
	}

	/**
	 * Computes the translate_source_text AbuseFilter variable
	 * @param string $method
	 * @param AbuseFilterVariableHolder $vars
	 * @param array $parameters
	 * @param null &$result
	 * @return bool
	 */
	public static function onAbuseFilterComputeVariable( $method, $vars, $parameters, &$result ) {
		if ( $method !== 'translate-get-source' ) {
			return true;
		}

		$handle = $parameters['handle'];
		$source = '';
		if ( $handle->isValid() ) {
			$group = $handle->getGroup();
			$source = $group->getMessage( $handle->getKey(), $group->getSourceLanguage() );
		}

		$result = $source;

		return false;
	}

	/**
	 * Register AbuseFilter variables provided by Translate.
	 * @param array &$builderValues
	 */
	public static function onAbuseFilterBuilder( &$builderValues ) {
		// Uses: 'abusefilter-edit-builder-vars-translate-source-text'
		$builderValues['vars']['translate_source_text'] = 'translate-source-text';
	}

	/**
	 * Hook: ParserFirstCallInit
	 * Registers \<languages> tag with the parser.
	 *
	 * @param Parser $parser
	 */
	public static function setupParserHooks( Parser $parser ) {
		// For nice language list in-page
		$parser->setHook( 'languages', array( 'PageTranslationHooks', 'languages' ) );
	}

	/**
	 * Hook: UnitTestsList
	 *
	 * @param array $files
	 */
	public static function setupUnitTests( array &$files ) {
		$dir = __DIR__ . '/tests/phpunit';
		$directoryIterator = new RecursiveDirectoryIterator( $dir );
		$fileIterator = new RecursiveIteratorIterator( $directoryIterator );

		/** @var SplFileInfo $fileInfo */
		foreach ( $fileIterator as $fileInfo ) {
			if ( substr( $fileInfo->getFilename(), -8 ) === 'Test.php' ) {
				$files[] = $fileInfo->getPathname();
			}
		}
	}

	/**
	 * Hook: LoadExtensionSchemaUpdates
	 *
	 * @param DatabaseUpdater $updater
	 */
	public static function schemaUpdates( DatabaseUpdater $updater ) {
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

		$updater->addExtensionUpdate( array(
			'addTable',
			'translate_stash',
			"$dir/translate_stash.sql",
			true
		) );
	}

	/**
	 * Hook: ParserTestTables
	 */
	public static function parserTestTables( array &$tables ) {
		$tables[] = 'revtag';
		$tables[] = 'translate_groupstats';
		$tables[] = 'translate_messageindex';
		$tables[] = 'translate_stash';
	}

	/**
	 * Hook: PageContentLanguage
	 * Set the correct page content language for translation units.
	 *
	 * @param Title $title
	 * @param string $pageLang
	 */
	public static function onPageContentLanguage( Title $title, &$pageLang ) {
		$handle = new MessageHandle( $title );
		if ( $handle->isMessageNamespace() ) {
			$pageLang = $handle->getEffectiveLanguage();
		}
	}

	/**
	 * Hook: LanguageGetTranslatedLanguageNames
	 * Hook: TranslateSupportedLanguages
	 */
	public static function translateMessageDocumentationLanguage( array &$names, $code ) {
		global $wgTranslateDocumentationLanguageCode;
		if ( $wgTranslateDocumentationLanguageCode ) {
			// Special case the autonyms
			if (
				$wgTranslateDocumentationLanguageCode === $code ||
				$code === null
			) {
				$code = 'en';
			}

			$names[$wgTranslateDocumentationLanguageCode] =
				wfMessage( 'translate-documentation-language' )->inLanguage( $code )->plain();
		}
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

		// Insert translations before 'all'
		$index = array_search( 'all', array_keys( $profiles ) );

		// Or just at the end if all is not found
		if ( $index === false ) {
			wfWarn( '"all" not found in search profiles' );
			$index = count( $profiles );
		}

		$profiles = array_merge(
			array_slice( $profiles, 0, $index ),
			$insert,
			array_slice( $profiles, $index )
		);
	}

	/**
	 * Hook: SpecialSearchProfileForm
	 */
	public static function searchProfileForm(
		SpecialSearch $search,
		/*string*/&$form,
		/*string*/$profile,
		/*string*/$term,
		array $opts
	) {
		if ( $profile !== 'translation' ) {
			return true;
		}

		$server = TTMServer::primary();
		if ( TTMServer::primary() instanceof SearchableTTMServer ) {
			$href = SpecialPage::getTitleFor( 'SearchTranslations' )
				->getFullUrl( array( 'query' => $term ) );
			$wrapper = new RawMessage( '<div class="successbox plainlinks">$1</div>' );
			$form = $wrapper
				->params( $search->msg( 'translate-searchprofile-note', $href )->plain() )
				->parse();

			return false;
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

		$languages = TranslateUtils::getLanguageNames( $code );
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

	/**
	 * Hook: SpecialSearchSetupEngine
	 */
	public static function searchProfileSetupEngine(
		SpecialSearch $search,
		/*string*/$profile,
		SearchEngine $engine
	) {
		if ( $profile !== 'translation' ) {
			return;
		}

		$context = $search->getContext();
		$selected = $context->getRequest()->getVal( 'languagefilter' );
		if ( $selected !== '-' && $selected ) {
			$engine->setFeatureData( 'title-suffix-filter', "/$selected" );
			$search->setExtraParam( 'languagefilter', $selected );
		}
	}

	/**
	 * Hook: Translate:MessageGroupStats:isIncluded
	 */
	public static function hideDiscouragedFromStats( $id, $code ) {
		// Return true to keep, false to exclude
		return MessageGroups::getPriority( $id ) !== 'discouraged';
	}

	/**
	 * Hook: Translate:MessageGroupStats:isIncluded
	 */
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

	/**
	 * Hook: LinksUpdate
	 */
	public static function preventCategorization( LinksUpdate $updater ) {
		$handle = new MessageHandle( $updater->getTitle() );
		if ( $handle->isMessageNamespace() && !$handle->isDoc() ) {
			$updater->mCategories = array();
		}
	}

	/**
	 * Hook: MakeGlobalVariablesScript
	 *
	 * Adds $wgTranslateDocumentationLanguageCode to ResourceLoader configuration
	 * when Special:Translate is shown.
	 */
	public static function addConfig( array &$vars, OutputPage $out ) {
		$request = $out->getRequest();
		$title = $out->getTitle();
		list( $alias, ) = SpecialPageFactory::resolveAlias( $title->getText() );

		if ( SpecialTranslate::isBeta( $request )
			&& $title->isSpecialPage()
			&& ( $alias === 'Translate'
				|| $alias === 'TranslationStash'
				|| $alias === 'SearchTranslations' )
		) {
			global $wgTranslateDocumentationLanguageCode, $wgTranslatePermissionUrl,
				$wgTranslateUseSandbox;
			$vars['TranslateRight'] = $out->getUser()->isAllowed( 'translate' );
			$vars['TranslateMessageReviewRight'] =
				$out->getUser()->isAllowed( 'translate-messagereview' );
			$vars['DeleteRight'] = $out->getUser()->isAllowed( 'delete' );
			$vars['wgTranslateDocumentationLanguageCode'] = $wgTranslateDocumentationLanguageCode;
			$vars['wgTranslatePermissionUrl'] = $wgTranslatePermissionUrl;
			$vars['wgTranslateUseSandbox'] = $wgTranslateUseSandbox;
		}
	}

	/**
	 * Hook: AdminLinks
	 */
	public static function onAdminLinks( ALTree $tree ) {
		global $wgTranslateUseSandbox;

		if ( $wgTranslateUseSandbox ) {
			$sectionLabel = wfMessage( 'adminlinks_users' )->text();
			$row = $tree->getSection( $sectionLabel )->getRow( 'main' );
			$row->addItem( ALItem::newFromSpecialPage( 'TranslateSandbox' ) );
		}
	}

	/**
	 * Any user of this list should make sure that the tables
	 * actually exist, since they may be optional
	 *
	 * @var array
	 */
	private static $userMergeTables = array(
		'translate_stash' => 'ts_user',
		'translate_reviews' => 'trr_user',
	);

	/**
	 * Hook: MergeAccountFromTo
	 * For UserMerge extension.
	 *
	 * @param User $oldUser
	 * @param User $newUser
	 */
	public static function onMergeAccountFromTo( User $oldUser, User $newUser ) {
		$dbw = wfGetDB( DB_MASTER );

		// Update the non-duplicate rows, we'll just delete
		// the duplicate ones later
		foreach ( self::$userMergeTables as $table => $field ) {
			if ( $dbw->tableExists( $table ) ) {
				$dbw->update(
					$table,
					array( $field => $newUser->getId() ),
					array( $field => $oldUser->getId() ),
					__METHOD__,
					array( 'IGNORE' )
				);
			}
		}
	}

	/**
	 * Hook: DeleteAccount
	 * For UserMerge extension.
	 *
	 * @param User $oldUser
	 */
	public static function onDeleteAccount( User $oldUser ) {
		$dbw = wfGetDB( DB_MASTER );

		// Delete any remaining rows that didn't get merged
		foreach ( self::$userMergeTables as $table => $field ) {
			if ( $dbw->tableExists( $table ) ) {
				$dbw->delete(
					$table,
					array( $field => $oldUser->getId() ),
					__METHOD__
				);
			}
		}
	}

	/**
	 * Hook: AbortEmailNotification
	 *
	 * False aborts the email.
	 */
	public static function onAbortEmailNotificationReview(
		User $editor,
		Title $title,
		RecentChange $rc
	) {
		if ( $rc->mAttribs['rc_log_type'] === 'translationreview' ) {
			return false;
		}
	}

	/**
	 * Hook: TitleIsAlwaysKnown
	 * Make Special:MyLanguage links red if the target page doesn't exist.
	 * A bit hacky because the core code is not so flexible.
	 *
	 * @param Title $target
	 * @param bool &$isKnown
	 * @return bool
	 */
	public static function onTitleIsAlwaysKnown( Title $target, &$isKnown ) {
		if ( !$target->inNamespace( NS_SPECIAL ) ) {
			return true;
		}

		list( $name, $subpage ) = SpecialPageFactory::resolveAlias( $target->getDBkey() );
		if ( $name !== 'MyLanguage' ) {
			return true;
		}

		if ( (string)$subpage === '' ) {
			return true;
		}

		$realTarget = Title::newFromText( $subpage );
		if ( !$realTarget || !$realTarget->exists() ) {
			$isKnown = false;

			return false;
		}

		return true;
	}

	/**
	 * Hook: ParserFirstCallInit
	 */
	public static function setupTranslateParserFunction( Parser $parser ) {
		$parser->setFunctionHook( 'translation', 'TranslateHooks::translateRenderParserFunction' );
	}

	public static function translateRenderParserFunction( Parser $parser ) {
		$pageTitle = $parser->getTitle();

		$handle = new MessageHandle( $pageTitle );
		$code = $handle->getCode();
		if ( Language::isKnownLanguageTag( $code ) ) {
			return '/' . $code;
		}
		return '';
	}
}
