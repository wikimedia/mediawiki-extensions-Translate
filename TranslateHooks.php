<?php
/**
 * Contains class with basic non-feature specific hooks.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */

use MediaWiki\Config\ServiceOptions;
use MediaWiki\Extension\Translate\SystemUsers\FuzzyBot;
use MediaWiki\Extension\Translate\SystemUsers\TranslateUserManager;
use MediaWiki\Extension\Translate\TranslatorSandbox\ManageTranslatorSandboxSpecialPage;
use MediaWiki\Extension\Translate\TranslatorSandbox\TranslationStashSpecialPage;
use MediaWiki\Hook\BeforeParserFetchTemplateRevisionRecordHook;
use MediaWiki\Hook\PageMoveCompleteHook;
use MediaWiki\Hook\SidebarBeforeOutputHook;
use MediaWiki\MediaWikiServices;

/**
 * Some hooks for Translate extension.
 */
class TranslateHooks {
	/**
	 * Any user of this list should make sure that the tables
	 * actually exist, since they may be optional
	 *
	 * @var array
	 */
	private static $userMergeTables = [
		'translate_stash' => 'ts_user',
		'translate_reviews' => 'trr_user',
	];

	/**
	 * Do late setup that depends on configuration.
	 */
	public static function setupTranslate() {
		global $wgHooks, $wgTranslateYamlLibrary;

		/*
		 * Text that will be shown in translations if the translation is outdated.
		 * Must be something that does not conflict with actual content.
		 */
		if ( !defined( 'TRANSLATE_FUZZY' ) ) {
			define( 'TRANSLATE_FUZZY', '!!FUZZY!!' );
		}

		if ( $wgTranslateYamlLibrary === null ) {
			$wgTranslateYamlLibrary = function_exists( 'yaml_parse' ) ? 'phpyaml' : 'spyc';
		}

		$usePageSaveComplete = version_compare( TranslateUtils::getMWVersion(), '1.35', '>=' );
		if ( $usePageSaveComplete ) {
			$wgHooks['PageSaveComplete'][] = 'TranslateEditAddons::onSaveComplete';
		} else {
			$wgHooks['PageContentSaveComplete'][] = 'TranslateEditAddons::onSave';
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
			$wgActionFilteredLogs['pagetranslation'] = [
				'mark' => [ 'mark' ],
				'unmark' => [ 'unmark' ],
				'move' => [ 'moveok', 'movenok' ],
				'delete' => [ 'deletefok', 'deletefnok', 'deletelok', 'deletelnok' ],
				'encourage' => [ 'encourage' ],
				'discourage' => [ 'discourage' ],
				'prioritylanguages' => [ 'prioritylanguages' ],
				'aggregategroups' => [ 'associate', 'dissociate' ],
			];

			global $wgJobClasses;
			$wgJobClasses['TranslateRenderJob'] = 'TranslateRenderJob';
			$wgJobClasses['RenderJob'] = 'TranslateRenderJob';
			$wgJobClasses['TranslatablePageMoveJob'] = 'TranslatablePageMoveJob';
			$wgJobClasses['TranslateDeleteJob'] = 'TranslateDeleteJob';
			$wgJobClasses['DeleteJob'] = 'TranslateDeleteJob';
			$wgJobClasses['TranslationsUpdateJob'] = 'TranslationsUpdateJob';

			// Namespaces
			global $wgNamespacesWithSubpages, $wgNamespaceProtection;
			global $wgTranslateMessageNamespaces;

			$wgNamespacesWithSubpages[NS_TRANSLATIONS] = true;
			$wgNamespacesWithSubpages[NS_TRANSLATIONS_TALK] = true;

			// Standard protection and register it for filtering
			$wgNamespaceProtection[NS_TRANSLATIONS] = [ 'translate' ];
			$wgTranslateMessageNamespaces[] = NS_TRANSLATIONS;

			/// Page translation hooks

			/// Register our CSS and metadata
			$wgHooks['BeforePageDisplay'][] = 'PageTranslationHooks::onBeforePageDisplay';

			// Check syntax for \<translate>
			$wgHooks['PageContentSave'][] = 'PageTranslationHooks::tpSyntaxCheck';
			$wgHooks['EditFilterMergedContent'][] =
				'PageTranslationHooks::tpSyntaxCheckForEditContent';

			// Add transtag to page props for discovery
			if ( $usePageSaveComplete ) {
				$wgHooks['PageSaveComplete'][] = 'PageTranslationHooks::addTranstagAfterSave';
			} else {
				$wgHooks['PageContentSaveComplete'][] = 'PageTranslationHooks::addTranstag';
			}
			$wgHooks['RevisionRecordInserted'][] =
				'PageTranslationHooks::updateTranstagOnNullRevisions';

			// Register different ways to show language links
			$wgHooks['ParserFirstCallInit'][] = 'TranslateHooks::setupParserHooks';
			$wgHooks['LanguageLinks'][] = 'PageTranslationHooks::addLanguageLinks';
			$wgHooks['SkinTemplateGetLanguageLink'][] = 'PageTranslationHooks::formatLanguageLink';

			// Strip \<translate> tags etc. from source pages when rendering
			$wgHooks['ParserBeforeInternalParse'][] = 'PageTranslationHooks::renderTagPage';
			$wgHooks['ParserOutputPostCacheTransform'][] =
				'PageTranslationHooks::onParserOutputPostCacheTransform';

			if ( interface_exists( BeforeParserFetchTemplateRevisionRecordHook::class ) ) {
				$wgHooks['BeforeParserFetchTemplateRevisionRecord'][] =
					'PageTranslationHooks::fetchTranslatableTemplateAndTitle';
			}

			// Set the page content language
			$wgHooks['PageContentLanguage'][] = 'PageTranslationHooks::onPageContentLanguage';

			// Prevent editing of certain pages in translations namespace
			$wgHooks['getUserPermissionsErrorsExpensive'][] =
				'PageTranslationHooks::onGetUserPermissionsErrorsExpensive';
			// Prevent editing of translation pages directly
			$wgHooks['getUserPermissionsErrorsExpensive'][] =
				'PageTranslationHooks::preventDirectEditing';

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
			if ( interface_exists( PageMoveCompleteHook::class ) ) {
				$wgHooks['PageMoveComplete'][] = 'PageTranslationHooks::onMovePageTranslationUnits';
			} else {
				$wgHooks['TitleMoveComplete'][] = 'PageTranslationHooks::onMoveTranslationUnits';
			}

			// Update translated page when translation unit is deleted
			$wgHooks['ArticleDeleteComplete'][] = 'PageTranslationHooks::onDeleteTranslationUnit';
		}

		global $wgTranslateUseSandbox;
		if ( $wgTranslateUseSandbox ) {
			global $wgSpecialPages, $wgAvailableRights, $wgDefaultUserOptions;

			$wgSpecialPages['ManageTranslatorSandbox'] = [
				'class' => ManageTranslatorSandboxSpecialPage::class,
				'services' => [
					'Translate:TranslationStashReader',
				],
				'args' => [
					function () {
						return new ServiceOptions(
							ManageTranslatorSandboxSpecialPage::CONSTRUCTOR_OPTIONS,
							MediaWikiServices::getInstance()->getMainConfig()
						);
					}
				]
			];
			$wgSpecialPages['TranslationStash'] = [
				'class' => TranslationStashSpecialPage::class,
				'services' => [
					'LanguageNameUtils',
					'Translate:TranslationStashReader'
				],
				'args' => [
					function () {
						return new ServiceOptions(
							TranslationStashSpecialPage::CONSTRUCTOR_OPTIONS,
							MediaWikiServices::getInstance()->getMainConfig()
						);
					}
				]
			];
			$wgDefaultUserOptions['translate-sandbox'] = '';
			// right-translate-sandboxmanage action-translate-sandboxmanage
			$wgAvailableRights[] = 'translate-sandboxmanage';

			$wgHooks['GetPreferences'][] = 'TranslateSandbox::onGetPreferences';
			$wgHooks['UserGetRights'][] = 'TranslateSandbox::enforcePermissions';
			$wgHooks['ApiCheckCanExecute'][] = 'TranslateSandbox::onApiCheckCanExecute';

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

			global $wgAPIModules;
			$wgAPIModules['translationstash'] = 'ApiTranslationStash';
			$wgAPIModules['translatesandbox'] = 'ApiTranslateSandbox';
		}

		global $wgNamespaceRobotPolicies;
		$wgNamespaceRobotPolicies[NS_TRANSLATIONS] = 'noindex';

		// If no service has been configured, we use a built-in fallback.
		global $wgTranslateTranslationDefaultService,
			$wgTranslateTranslationServices;
		if ( $wgTranslateTranslationDefaultService === true ) {
			$wgTranslateTranslationDefaultService = 'TTMServer';
			if ( !isset( $wgTranslateTranslationServices['TTMServer'] ) ) {
				$wgTranslateTranslationServices['TTMServer'] = [
					'database' => false, // Passed to wfGetDB
					'cutoff' => 0.75,
					'type' => 'ttmserver',
					'public' => false,
				];
			}
		}

		// Add the BaseTemplateToolbox handler only when the new hook hasn't been defined yet.
		if ( interface_exists( SidebarBeforeOutputHook::class ) ) {
			$wgHooks['SidebarBeforeOutput'][] = 'TranslateToolbox::toolboxAllTranslations';
		} else {
			$wgHooks['BaseTemplateToolbox'][] = 'TranslateToolbox::toolboxAllTranslationsOld';
		}
	}

	/**
	 * Hook: UserGetReservedNames
	 * Prevents anyone from registering or logging in as FuzzyBot
	 *
	 * @param array &$names
	 */
	public static function onUserGetReservedNames( array &$names ) {
		$names[] = FuzzyBot::getName();
		$names[] = TranslateUserManager::getName();
	}

	/**
	 * Used for setting an AbuseFilter variable.
	 *
	 * @param AbuseFilterVariableHolder &$vars
	 * @param Title $title
	 * @param User $user
	 */
	public static function onAbuseFilterAlterVariables(
		&$vars, Title $title, User $user
	) {
		$handle = new MessageHandle( $title );

		// Only set this variable if we are in a proper namespace to avoid
		// unnecessary overhead in non-translation pages
		if ( $handle->isMessageNamespace() ) {
			$vars->setLazyLoadVar(
				'translate_source_text',
				'translate-get-source',
				[ 'handle' => $handle ]
			);
			$vars->setLazyLoadVar(
				'translate_target_language',
				'translate-get-target-language',
				[ 'handle' => $handle ]
			);
		}
	}

	/**
	 * Computes the translate_source_text and translate_target_language AbuseFilter variables
	 * @param string $method
	 * @param AbuseFilterVariableHolder $vars
	 * @param array $parameters
	 * @param null &$result
	 * @return bool
	 */
	public static function onAbuseFilterComputeVariable( $method, $vars, $parameters, &$result ) {
		if ( $method !== 'translate-get-source' && $method !== 'translate-get-target-language' ) {
			return true;
		}

		$handle = $parameters['handle'];
		$value = '';
		if ( $handle->isValid() ) {
			if ( $method === 'translate-get-source' ) {
				$group = $handle->getGroup();
				$value = $group->getMessage( $handle->getKey(), $group->getSourceLanguage() );
			} else {
				$value = $handle->getCode();
			}
		}

		$result = $value;

		return false;
	}

	/**
	 * Register AbuseFilter variables provided by Translate.
	 * @param array &$builderValues
	 */
	public static function onAbuseFilterBuilder( array &$builderValues ) {
		// Uses: 'abusefilter-edit-builder-vars-translate-source-text'
		// and 'abusefilter-edit-builder-vars-translate-target-language'
		$builderValues['vars']['translate_source_text'] = 'translate-source-text';
		$builderValues['vars']['translate_target_language'] = 'translate-target-language';
	}

	/**
	 * Hook: ParserFirstCallInit
	 * Registers \<languages> tag with the parser.
	 *
	 * @param Parser $parser
	 */
	public static function setupParserHooks( Parser $parser ) {
		// For nice language list in-page
		$parser->setHook( 'languages', [ 'PageTranslationHooks', 'languages' ] );
	}

	/**
	 * Hook: LoadExtensionSchemaUpdates
	 *
	 * @param DatabaseUpdater $updater
	 */
	public static function schemaUpdates( DatabaseUpdater $updater ) {
		$dir = __DIR__ . '/sql';

		$updater->addExtensionUpdate( [
			'addTable',
			'translate_sections',
			"$dir/translate_sections.sql",
			true
		] );
		$updater->addExtensionUpdate( [
			'addField',
			'translate_sections',
			'trs_order',
			"$dir/translate_sections-trs_order.patch.sql",
			true
		] );
		$updater->addExtensionUpdate( [
			'addTable',
			'revtag', "$dir/revtag.sql",
			true
		] );
		$updater->addExtensionUpdate( [
			'addTable',
			'translate_groupstats',
			"$dir/translate_groupstats.sql",
			true
		] );
		$updater->addExtensionUpdate( [
			'addIndex',
			'translate_sections',
			'trs_page_order',
			"$dir/translate_sections-indexchange.sql",
			true
		] );
		$updater->addExtensionUpdate( [
			'dropIndex',
			'translate_sections',
			'trs_page',
			"$dir/translate_sections-indexchange2.sql",
			true
		] );
		$updater->addExtensionUpdate( [
			'addTable',
			'translate_reviews',
			"$dir/translate_reviews.sql",
			true
		] );
		$updater->addExtensionUpdate( [
			'addTable',
			'translate_groupreviews',
			"$dir/translate_groupreviews.sql",
			true
		] );
		$updater->addExtensionUpdate( [
			'addTable',
			'translate_tms',
			"$dir/translate_tm.sql",
			true
		] );
		$updater->addExtensionUpdate( [
			'addTable',
			'translate_metadata',
			"$dir/translate_metadata.sql",
			true
		] );
		$updater->addExtensionUpdate( [
			'addTable', 'translate_messageindex',
			"$dir/translate_messageindex.sql",
			true
		] );
		$updater->addExtensionUpdate( [
			'addIndex',
			'translate_groupstats',
			'tgs_lang',
			"$dir/translate_groupstats-indexchange.sql",
			true
		] );
		$updater->addExtensionUpdate( [
			'addField', 'translate_groupstats',
			'tgs_proofread',
			"$dir/translate_groupstats-proofread.sql",
			true
		] );

		$updater->addExtensionUpdate( [
			'addTable',
			'translate_stash',
			"$dir/translate_stash.sql",
			true
		] );

		// This also adds a PRIMARY KEY
		$updater->addExtensionUpdate( [
			'renameIndex',
			'translate_reviews',
			'trr_user_page_revision',
			'PRIMARY',
			false,
			"$dir/translate_reviews-patch-01-primary-key.sql",
			true
		] );

		$updater->addExtensionUpdate( [
			'addTable',
			'translate_cache',
			"$dir/translate_cache.sql",
			true
		] );
	}

	/**
	 * Hook: ParserTestTables
	 * @param array &$tables
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
	 * @param Language|StubUserLang|string &$pageLang
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
	 * @param array &$names
	 * @param string $code
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
	 * @param array &$profiles
	 */
	public static function searchProfile( array &$profiles ) {
		global $wgTranslateMessageNamespaces;
		$insert = [];
		$insert['translation'] = [
			'message' => 'translate-searchprofile',
			'tooltip' => 'translate-searchprofile-tooltip',
			'namespaces' => $wgTranslateMessageNamespaces,
		];

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
	 * @param SpecialSearch $search
	 * @param string &$form
	 * @param string $profile
	 * @param string $term
	 * @param array $opts
	 * @return bool
	 */
	public static function searchProfileForm(
		SpecialSearch $search,
		&$form,
		$profile,
		$term,
		array $opts
	) {
		if ( $profile !== 'translation' ) {
			return true;
		}

		if ( TTMServer::primary() instanceof SearchableTTMServer ) {
			$href = SpecialPage::getTitleFor( 'SearchTranslations' )
				->getFullUrl( [ 'query' => $term ] );
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
		$params = [ 'id' => 'mw-searchoptions' ];

		$form = Xml::fieldset( false, false, $params ) .
			$hidden . $label . $selector .
			Html::closeElement( 'fieldset' );

		return false;
	}

	/**
	 * Hook: SpecialSearchSetupEngine
	 * @param SpecialSearch $search
	 * @param string $profile
	 * @param SearchEngine $engine
	 */
	public static function searchProfileSetupEngine(
		SpecialSearch $search,
		$profile,
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
	 * @param int $id
	 * @param string $code
	 * @return bool
	 */
	public static function hideDiscouragedFromStats( $id, $code ) {
		// Return true to keep, false to exclude
		return MessageGroups::getPriority( $id ) !== 'discouraged';
	}

	/**
	 * Hook: Translate:MessageGroupStats:isIncluded
	 * @param int $id
	 * @param string $code
	 * @return false
	 */
	public static function hideRestrictedFromStats( $id, $code ) {
		$filterLangs = TranslateMetadata::get( $id, 'prioritylangs' );
		$hasPriorityForce = TranslateMetadata::get( $id, 'priorityforce' ) === 'on';
		if ( strlen( $filterLangs ) === 0 || !$hasPriorityForce ) {
			// No restrictions, keep everything
			return true;
		}

		$filter = array_flip( explode( ',', $filterLangs ) );

		// If the language is in the list, return true to not hide it
		return isset( $filter[$code] );
	}

	/**
	 * Hook: LinksUpdate
	 * @param LinksUpdate $updater
	 */
	public static function preventCategorization( LinksUpdate $updater ) {
		$handle = new MessageHandle( $updater->getTitle() );
		if ( $handle->isMessageNamespace() && !$handle->isDoc() ) {
			$updater->mCategories = [];
		}
	}

	/**
	 * Hook: MakeGlobalVariablesScript
	 *
	 * Adds $wgTranslateDocumentationLanguageCode to ResourceLoader configuration
	 * when Special:Translate is shown.
	 * @param array &$vars
	 * @param OutputPage $out
	 */
	public static function addConfig( array &$vars, OutputPage $out ) {
		$title = $out->getTitle();
		[ $alias, ] = MediaWikiServices::getInstance()
			->getSpecialPageFactory()->resolveAlias( $title->getText() );

		if ( $title->isSpecialPage()
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
			$vars['TranslateManageRight'] = $out->getUser()->isAllowed( 'translate-manage' );
			$vars['wgTranslateDocumentationLanguageCode'] = $wgTranslateDocumentationLanguageCode;
			$vars['wgTranslatePermissionUrl'] = $wgTranslatePermissionUrl;
			$vars['wgTranslateUseSandbox'] = $wgTranslateUseSandbox;
		}
	}

	/**
	 * Hook: AdminLinks
	 * @param ALTree $tree
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
			if ( $dbw->tableExists( $table, __METHOD__ ) ) {
				$dbw->update(
					$table,
					[ $field => $newUser->getId() ],
					[ $field => $oldUser->getId() ],
					__METHOD__,
					[ 'IGNORE' ]
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
			if ( $dbw->tableExists( $table, __METHOD__ ) ) {
				$dbw->delete(
					$table,
					[ $field => $oldUser->getId() ],
					__METHOD__
				);
			}
		}
	}

	/**
	 * Hook: AbortEmailNotification
	 *
	 * False aborts the email.
	 * @param User $editor
	 * @param Title $title
	 * @param RecentChange $rc
	 * @return bool
	 */
	public static function onAbortEmailNotificationReview(
		User $editor,
		Title $title,
		RecentChange $rc
	) {
		if ( $rc->getAttribute( 'rc_log_type' ) === 'translationreview' ) {
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

		[ $name, $subpage ] = MediaWikiServices::getInstance()
			->getSpecialPageFactory()->resolveAlias( $target->getDBkey() );
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
	 * @param Parser $parser
	 */
	public static function setupTranslateParserFunction( Parser $parser ) {
		$parser->setFunctionHook( 'translation', 'TranslateHooks::translateRenderParserFunction' );
	}

	/**
	 * @param Parser $parser
	 * @return string
	 */
	public static function translateRenderParserFunction( Parser $parser ) {
		$pageTitle = $parser->getTitle();

		$handle = new MessageHandle( $pageTitle );
		$code = $handle->getCode();
		if ( Language::isKnownLanguageTag( $code ) ) {
			return '/' . $code;
		}
		return '';
	}

	/** @param ResourceLoader $resourceLoader */
	public static function onResourceLoaderRegisterModules( ResourceLoader $resourceLoader ) {
		// Support: MediaWiki <= 1.34
		$hasOldTokens = $hasOldNotify = version_compare(
			TranslateUtils::getMWVersion(), '1.35', '<'
		);

		$tpl = [
			'localBasePath' => __DIR__,
			'remoteExtPath' => 'Translate',
			'targets' => [ 'desktop', 'mobile' ],
		];

		$modules = [
			'ext.translate.recentgroups' => $tpl + [
				'scripts' => 'resources/js/ext.translate.recentgroups.js',
				'dependencies' => [
					'mediawiki.storage'
				],
			],
			'ext.translate.groupselector' => $tpl + [
				'styles' => 'resources/css/ext.translate.groupselector.less',
				'scripts' => 'resources/js/ext.translate.groupselector.js',
				'dependencies' => [
					'ext.translate.base',
					'ext.translate.loader',
					'ext.translate.statsbar',
					'jquery.ui',
					'mediawiki.jqueryMsg'
				],
				'messages' => [
					'translate-msggroupselector-search-all',
					'translate-msggroupselector-search-placeholder',
					'translate-msggroupselector-search-recent',
					'translate-msggroupselector-view-subprojects'
				]
			],
			'ext.translate.special.aggregategroups' => $tpl + [
				'scripts' => 'resources/js/ext.translate.special.aggregategroups.js',
				'dependencies' => [
					'jquery.ui',
					'mediawiki.api',
					'mediawiki.util'
				],
				'messages' => [
					'tpt-aggregategroup-add',
					'tpt-aggregategroup-edit-description',
					'tpt-aggregategroup-edit-name',
					'tpt-aggregategroup-remove-confirm',
					'tpt-aggregategroup-update',
					'tpt-aggregategroup-update-cancel',
					'tpt-invalid-group'
				]
			],
			'ext.translate.special.importtranslations' => $tpl + [
				'scripts' => 'resources/js/ext.translate.special.importtranslations.js',
				'dependencies' => [
					'jquery.ui',
				]
			],
			'ext.translate.special.managetranslatorsandbox' => $tpl + [
				'scripts' => 'resources/js/ext.translate.special.managetranslatorsandbox.js',
				'dependencies' => array_merge( [
					'ext.translate.loader',
					'ext.translate.translationstashstorage',
					'ext.uls.mediawiki',
					'jquery.ui',
					'mediawiki.api',
					'mediawiki.jqueryMsg',
					'mediawiki.language',
				], $hasOldNotify ? [ 'mediawiki.notify' ] : [] ),
				'messages' => [
					'tsb-accept-all-button-label',
					'tsb-accept-button-label',
					'tsb-reject-confirmation',
					'tsb-accept-confirmation',
					'tsb-all-languages-button-label',
					'tsb-didnt-make-any-translations',
					'tsb-no-requests-from-new-users',
					'tsb-older-requests',
					'tsb-reject-all-button-label',
					'tsb-reject-button-label',
					'tsb-reminder-failed',
					'tsb-reminder-link-text',
					'tsb-reminder-sending',
					'tsb-reminder-sent',
					'tsb-reminder-sent-new',
					'tsb-request-count',
					'tsb-selected-count',
					'tsb-translations-current',
					'tsb-translations-source',
					'tsb-translations-user',
					'tsb-user-posted-a-comment'
				]
			],
			'ext.translate.special.searchtranslations.operatorsuggest' => $tpl + [
				'scripts' => 'resources/js/ext.translate.special.operatorsuggest.js',
				'dependencies' => [
					'jquery.ui',
				]
			],
			'ext.translate.special.pagetranslation' => $tpl + [
				'packageFiles' => [
					'resources/js/ext.translate.special.pagetranslation.js',
					'resources/js/LanguagesMultiselectWidget.js'
				],
				'dependencies' => [
					'mediawiki.Uri',
					'mediawiki.api',
					'mediawiki.ui.button',
					'mediawiki.widgets',
					'oojs-ui-widgets',
					$hasOldTokens ? 'user.tokens' : 'user.options',
				],
				'targets' => [
					'desktop'
				]
			],
			"ext.translate.editor" => $tpl + [
				"scripts" => [
					"resources/js/ext.translate.storage.js",
					"resources/lib/jquery.autosize.js",
					"resources/js/ext.translate.editor.helpers.js",
					"resources/js/ext.translate.editor.js",
					"resources/js/ext.translate.editor.shortcuts.js",
					"resources/js/ext.translate.pagemode.js",
					"resources/js/ext.translate.proofread.js"
				],
				"styles" => [
					"resources/css/ext.translate.editor.css",
					"resources/css/ext.translate.pagemode.css",
					"resources/css/ext.translate.proofread.css"
				],
				"dependencies" => array_merge( [
					"ext.translate.base",
					"ext.translate.dropdownmenu",
					"jquery.makeCollapsible",
					"jquery.textSelection",
					"jquery.textchange",
					"mediawiki.Uri",
					"mediawiki.api",
					"mediawiki.jqueryMsg",
					"mediawiki.language",
					"mediawiki.user",
					"mediawiki.util"
				], $hasOldNotify ? [ 'mediawiki.notify' ] : [] ),
				"messages" => [
					"translate-edit-askpermission",
					"translate-edit-nopermission",
					"tux-editor-add-desc",
					"tux-editor-ask-help",
					"tux-editor-cancel-button-label",
					"tux-editor-close-tooltip",
					"tux-editor-collapse-tooltip",
					"tux-editor-confirm-button-label",
					"tux-editor-discard-changes-button-label",
					"tux-editor-doc-editor-cancel",
					"tux-editor-doc-editor-placeholder",
					"tux-editor-doc-editor-save",
					"tux-editor-edit-desc",
					"tux-editor-expand-tooltip",
					"tux-editor-in-other-languages",
					"tux-editor-loading",
					"tux-editor-loading-failed",
					"tux-editor-message-desc-less",
					"tux-editor-message-desc-more",
					"tux-editor-message-tools-show-editor",
					"tux-editor-message-tools-delete",
					"tux-editor-message-tools-history",
					"tux-editor-message-tools-translations",
					"tux-editor-message-tools-linktothis",
					"tux-editor-n-uses",
					"tux-editor-need-more-help",
					"tux-editor-outdated-notice",
					"tux-editor-outdated-notice-diff-link",
					"tux-editor-paste-original-button-label",
					"tux-editor-placeholder",
					"tux-editor-editsummary-placeholder",
					"tux-editor-proofread-button-label",
					"tux-editor-save-button-label",
					"tux-editor-save-failed",
					"tux-editor-shortcut-info",
					"tux-editor-skip-button-label",
					"tux-editor-suggestions-title",
					"tux-editor-tm-match",
					"tux-proofread-action-tooltip",
					"tux-proofread-edit-label",
					"tux-proofread-translated-by-self",
					"tux-session-expired",
					"tux-status-saving",
					"tux-status-translated",
					"tux-status-unsaved",
					"tux-save-unknown-error",
					"tux-notices-hide",
					"tux-notices-more",
					"spamprotectiontext"
				],
				"targets" => [
					"desktop",
					"mobile"
				]
			],
			"ext.translate.special.managegroups" => $tpl + [
				"dependencies" => array_merge( [
					"ext.translate.messagerenamedialog"
				], $hasOldNotify ? [ 'mediawiki.notify' ] : [] ),
				"messages" => [
					"translate-smg-rename-new",
					"translate-smg-rename-rename",
					"translate-smg-rename-dialog-title",
					"percent"
				],
				"scripts" => [
					"resources/js/ext.translate.special.managegroups.js"
				],
				"targets" => [
					"desktop",
					"mobile"
				]
			],
		];

		$resourceLoader->register( $modules );
	}

	/**
	 * Runs the configured validator to ensure that the message meets the required criteria.
	 * Hook: EditFilterMergedContent
	 * @param IContextSource $context
	 * @param Content $content
	 * @param Status $status
	 * @param string $summary
	 * @param \User $user
	 * @return bool true if message is valid, false otherwise.
	 */
	public static function validateMessage( IContextSource $context, Content $content,
		Status $status, $summary, User $user
	) {
		if ( !$content instanceof TextContent ) {
			// Not interested
			return true;
		}

		$text = $content->getText();
		$title = $context->getTitle();
		$handle = new MessageHandle( $title );

		if ( !$handle->isValid() ) {
			return true;
		}

		// Don't bother validating if FuzzyBot or translation admin are saving.
		if ( $user->isAllowed( 'translate-manage' ) || $user->equals( FuzzyBot::getUser() ) ) {
			return true;
		}

		// Check the namespace, and perform validations for all messages excluding documentation.
		if ( $handle->isMessageNamespace() && !$handle->isDoc() ) {
			$group = $handle->getGroup();

			if ( is_callable( [ $group, 'getMessageContent' ] ) ) {
				// @phan-suppress-next-line PhanUndeclaredMethod
				$definition = $group->getMessageContent( $handle );
			} else {
				$definition = $group->getMessage( $handle->getKey(), $group->getSourceLanguage() );
			}

			$message = new FatMessage( $handle->getKey(), $definition );
			$message->setTranslation( $text );

			$messageValidator = $group->getValidator();
			if ( !$messageValidator ) {
				return true;
			}

			$validationResponse = $messageValidator->validateMessage( $message, $handle->getCode() );
			if ( $validationResponse->hasErrors() ) {
				// @phan-suppress-next-line SecurityCheck-DoubleEscaped
				$status->fatal( new \ApiRawMessage(
					$context->msg( 'translate-syntax-error' )->parse(),
					'translate-validation-failed',
					[
						'validation' => [
							'errors' => $validationResponse->getDescriptiveErrors( $context ),
							'warnings' => $validationResponse->getDescriptiveWarnings( $context )
						]
					]
				) );
				return false;
			}
		}

		return true;
	}
}
