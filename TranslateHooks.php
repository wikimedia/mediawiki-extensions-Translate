<?php
/**
 * Contains class with basic non-feature specific hooks.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */

use MediaWiki\Config\ServiceOptions;
use MediaWiki\Extension\AbuseFilter\Variables\VariableHolder;
use MediaWiki\Extension\Translate\PageTranslation\PageTranslationSpecialPage;
use MediaWiki\Extension\Translate\SystemUsers\FuzzyBot;
use MediaWiki\Extension\Translate\SystemUsers\TranslateUserManager;
use MediaWiki\Extension\Translate\TranslatorSandbox\ManageTranslatorSandboxSpecialPage;
use MediaWiki\Extension\Translate\TranslatorSandbox\TranslationStashSpecialPage;
use MediaWiki\Hook\BeforeParserFetchTemplateRevisionRecordHook;
use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\Hook\RevisionRecordInsertedHook;
use MediaWiki\Revision\RevisionLookup;
use Wikimedia\Rdbms\ILoadBalancer;

/**
 * Hooks for Translate extension.
 *
 * Main subsystems, like page translation, should have their own hook handler.
 *
 * Most of the hooks on this class are still old style static functions, but new new hooks should
 * use the new style hook handlers with interfaces.
 */
class TranslateHooks implements RevisionRecordInsertedHook {
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
	/** @var RevisionLookup */
	private $revisionLookup;
	/** @var ILoadBalancer */
	private $loadBalancer;

	public function __construct( RevisionLookup $revisionLookup, ILoadBalancer $loadBalancer ) {
		$this->revisionLookup = $revisionLookup;
		$this->loadBalancer = $loadBalancer;
	}

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

		$wgHooks['PageSaveComplete'][] = 'TranslateEditAddons::onSaveComplete';

		// Page translation setup check and init if enabled.
		global $wgEnablePageTranslation;
		if ( $wgEnablePageTranslation ) {
			// Special page and the right to use it
			global $wgSpecialPages, $wgAvailableRights;
			$wgSpecialPages['PageTranslation'] = [
				'class' => PageTranslationSpecialPage::class,
				'services' => [
					'LanguageNameUtils',
					'LanguageFactory',
					'Translate:TranslationUnitStoreFactory',
					'Translate:TranslatablePageParser',
					'LinkBatchFactory'
				]
			];
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
			$wgHooks['MultiContentSave'][] = 'PageTranslationHooks::tpSyntaxCheck';
			$wgHooks['EditFilterMergedContent'][] =
				'PageTranslationHooks::tpSyntaxCheckForEditContent';

			// Add transtag to page props for discovery
			$wgHooks['PageSaveComplete'][] = 'PageTranslationHooks::addTranstagAfterSave';

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
			$wgHooks['PageMoveComplete'][] = 'PageTranslationHooks::onMovePageTranslationUnits';

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
					'UserOptionsLookup'
				],
				'args' => [
					static function () {
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
					'Translate:TranslationStashReader',
					'UserOptionsLookup'
				],
				'args' => [
					static function () {
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

		$wgHooks['SidebarBeforeOutput'][] = 'TranslateToolbox::toolboxAllTranslations';
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
	 * @param VariableHolder &$vars
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
	 * @param VariableHolder $vars
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
		$dbType = $updater->getDB()->getType();

		if ( $dbType === 'mysql' || $dbType === 'sqlite' ) {
			$updater->addExtensionTable(
				'translate_sections',
				"{$dir}/{$dbType}/translate_sections.sql"
			);
			$updater->addExtensionUpdate( [
				'addField',
				'translate_sections',
				'trs_order',
				"$dir/translate_sections-trs_order.patch.sql",
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
			$updater->addExtensionTable(
				'revtag',
				"{$dir}/{$dbType}/revtag.sql"
			);
			$updater->addExtensionTable(
				'translate_groupstats',
				"{$dir}/{$dbType}/translate_groupstats.sql"
			);
			$updater->addExtensionTable(
				'translate_reviews',
				"{$dir}/{$dbType}/translate_reviews.sql"
			);
			$updater->addExtensionTable(
				'translate_groupreviews',
				"{$dir}/{$dbType}/translate_groupreviews.sql"
			);
			$updater->addExtensionTable(
				'translate_tms',
				"{$dir}/{$dbType}/translate_tm.sql"
			);
			$updater->addExtensionTable(
				'translate_metadata',
				"{$dir}/{$dbType}/translate_metadata.sql"
			);
			$updater->addExtensionTable(
				'translate_messageindex',
				"{$dir}/{$dbType}/translate_messageindex.sql"
			);
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

			$updater->addExtensionTable(
				'translate_stash',
				"{$dir}/{$dbType}/translate_stash.sql"
			);

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

			$updater->addExtensionTable(
				'translate_cache',
				"{$dir}/{$dbType}/translate_cache.sql"
			);

			if ( $dbType === 'mysql' ) {
				$updater->modifyExtensionField(
					'translate_cache',
					'tc_key',
					"{$dir}/{$dbType}/translate_cache-alter-varbinary.sql"
				);
			}
		} elseif ( $dbType === 'postgres' ) {
			$updater->addExtensionTable(
				'translate_sections',
				"{$dir}/{$dbType}/tables-generated.sql"
			);
		}
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
	 * Hook: ParserAfterTidy
	 * @param Parser $parser
	 * @param string &$html
	 */
	public static function preventCategorization( Parser $parser, &$html ) {
		$handle = new MessageHandle( $parser->getTitle() );
		if ( $handle->isMessageNamespace() && !$handle->isDoc() ) {
			$parserOutput = $parser->getOutput();
			$parserOutput->setExtensionData( 'translate-fake-categories',
				$parserOutput->getCategories() );
			if ( method_exists( $parserOutput, 'setCategories' ) ) { // 1.38+
				$parserOutput->setCategories( [] );
			} else {
				$parserOutput->setCategoryLinks( [] );
			}
		}
	}

	/**
	 * Hook: OutputPageParserOutput
	 * @param OutputPage $outputPage
	 * @param ParserOutput $parserOutput
	 */
	public static function showFakeCategories( OutputPage $outputPage, ParserOutput $parserOutput ) {
		$fakeCategories = $parserOutput->getExtensionData( 'translate-fake-categories' );
		if ( $fakeCategories ) {
			$outputPage->setCategoryLinks( $fakeCategories );
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
		$dbw = wfGetDB( DB_PRIMARY );

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
		$dbw = wfGetDB( DB_PRIMARY );

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

	/**
	 * Runs the configured validator to ensure that the message meets the required criteria.
	 * Hook: EditFilterMergedContent
	 * @param IContextSource $context
	 * @param Content $content
	 * @param Status $status
	 * @param string $summary
	 * @param User $user
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
				$status->fatal( new ApiRawMessage(
					$context->msg( 'translate-syntax-error' )->parse(),
					'translate-validation-failed',
					[
						'validation' => [
							'errors' => $validationResponse->getDescriptiveErrors( $context ),
							'warnings' => $validationResponse->getDescriptiveWarnings( $context )
						]
					]
				) );
				// @todo Remove this line after this extension do not support mediawiki version 1.36 and before
				$status->value = EditPage::AS_HOOK_ERROR_EXPECTED;
				return false;
			}
		}

		return true;
	}

	/** @inheritDoc */
	public function onRevisionRecordInserted( $revisionRecord ): void {
		$parentId = $revisionRecord->getParentId();
		if ( $parentId === 0 || $parentId === null ) {
			// No parent, bail out.
			return;
		}

		$prevRev = $this->revisionLookup->getRevisionById( $parentId );
		if ( !$prevRev || !$revisionRecord->hasSameContent( $prevRev ) ) {
			// Not a null revision, bail out.
			return;
		}

		// List of tags that should be copied over when updating
		// tp:tag and tp:mark handling is in PageTranslationHooks::updateTranstagOnNullRevisions.
		$tagsToCopy = [ 'fuzzy', 'tp:transver' ];

		$db = $this->loadBalancer->getConnectionRef( DB_PRIMARY );
		$db->insertSelect(
			'revtag',
			'revtag',
			[
				'rt_type' => 'rt_type',
				'rt_page' => 'rt_page',
				'rt_revision' => $revisionRecord->getId(),
				'rt_value' => 'rt_value',

			],
			[
				'rt_type' => $tagsToCopy,
				'rt_revision' => $parentId,
			],
			__METHOD__
		);
	}
}
