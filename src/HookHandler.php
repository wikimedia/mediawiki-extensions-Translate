<?php
declare( strict_types=1 );

namespace MediaWiki\Extension\Translate;

use AggregateMessageGroup;
use ALItem;
use ALTree;
use LogFormatter;
use MediaWiki\Api\ApiRawMessage;
use MediaWiki\ChangeTags\Hook\ChangeTagsListActiveHook;
use MediaWiki\ChangeTags\Hook\ListDefinedTagsHook;
use MediaWiki\Config\Config;
use MediaWiki\Config\ConfigException;
use MediaWiki\Config\ServiceOptions;
use MediaWiki\Content\Content;
use MediaWiki\Content\TextContent;
use MediaWiki\Context\IContextSource;
use MediaWiki\Extension\AbuseFilter\Variables\VariableHolder;
use MediaWiki\Extension\Translate\LogFormatter as TranslateLogFormatter;
use MediaWiki\Extension\Translate\MessageBundleTranslation\ScribuntoHookHandler;
use MediaWiki\Extension\Translate\MessageGroupProcessing\DeleteTranslatableBundleJob;
use MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroupSubscriptionHookHandler;
use MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroupSubscriptionNotificationJob;
use MediaWiki\Extension\Translate\MessageGroupProcessing\MoveTranslatableBundleJob;
use MediaWiki\Extension\Translate\MessageGroupProcessing\RevTagStore;
use MediaWiki\Extension\Translate\MessageGroupProcessing\TranslatableBundleLogFormatter;
use MediaWiki\Extension\Translate\MessageLoading\FatMessage;
use MediaWiki\Extension\Translate\MessageLoading\MessageHandle;
use MediaWiki\Extension\Translate\PageTranslation\DeleteTranslatableBundleSpecialPage;
use MediaWiki\Extension\Translate\PageTranslation\Hooks;
use MediaWiki\Extension\Translate\PageTranslation\MarkForTranslationActionApi;
use MediaWiki\Extension\Translate\PageTranslation\MigrateTranslatablePageSpecialPage;
use MediaWiki\Extension\Translate\PageTranslation\PageTranslationSpecialPage;
use MediaWiki\Extension\Translate\PageTranslation\PrepareTranslatablePageSpecialPage;
use MediaWiki\Extension\Translate\PageTranslation\RenderTranslationPageJob;
use MediaWiki\Extension\Translate\PageTranslation\UpdateTranslatablePageJob;
use MediaWiki\Extension\Translate\SystemUsers\FuzzyBot;
use MediaWiki\Extension\Translate\SystemUsers\TranslateUserManager;
use MediaWiki\Extension\Translate\TranslatorInterface\TranslateEditAddons;
use MediaWiki\Extension\Translate\TranslatorSandbox\ManageTranslatorSandboxSpecialPage;
use MediaWiki\Extension\Translate\TranslatorSandbox\TranslateSandboxEmailJob;
use MediaWiki\Extension\Translate\TranslatorSandbox\TranslationStashActionApi;
use MediaWiki\Extension\Translate\TranslatorSandbox\TranslationStashSpecialPage;
use MediaWiki\Extension\Translate\TranslatorSandbox\TranslatorSandboxActionApi;
use MediaWiki\Extension\Translate\TtmServer\SearchableTtmServer;
use MediaWiki\Extension\Translate\Utilities\Utilities;
use MediaWiki\Hook\ParserFirstCallInitHook;
use MediaWiki\Html\Html;
use MediaWiki\Language\Language;
use MediaWiki\Languages\LanguageNameUtils;
use MediaWiki\MediaWikiServices;
use MediaWiki\Output\OutputPage;
use MediaWiki\Parser\Parser;
use MediaWiki\Parser\ParserOutput;
use MediaWiki\Registration\ExtensionRegistry;
use MediaWiki\ResourceLoader\Context;
use MediaWiki\Revision\Hook\RevisionRecordInsertedHook;
use MediaWiki\Revision\RevisionLookup;
use MediaWiki\Settings\SettingsBuilder;
use MediaWiki\SpecialPage\SpecialPage;
use MediaWiki\Specials\SpecialSearch;
use MediaWiki\Status\Status;
use MediaWiki\StubObject\StubUserLang;
use MediaWiki\Title\Title;
use MediaWiki\Title\TitleValue;
use MediaWiki\User\Hook\UserGetReservedNamesHook;
use MediaWiki\User\User;
use MediaWiki\Xml\XmlSelect;
use RecentChange;
use SearchEngine;
use Wikimedia\Rdbms\ILoadBalancer;

/**
 * Hooks for Translate extension.
 * Contains class with basic non-feature specific hooks.
 * Main subsystems, like page translation, should have their own hook handler. *
 * Most of the hooks on this class are still old style static functions, but new new hooks should
 * use the new style hook handlers with interfaces.
 *
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */
class HookHandler implements
	ChangeTagsListActiveHook,
	ListDefinedTagsHook,
	ParserFirstCallInitHook,
	RevisionRecordInsertedHook,
	UserGetReservedNamesHook
{
	/**
	 * Any user of this list should make sure that the tables
	 * actually exist, since they may be optional
	 */
	private const USER_MERGE_TABLES = [
		'translate_stash' => 'ts_user',
		'translate_reviews' => 'trr_user',
	];
	private RevisionLookup $revisionLookup;
	private ILoadBalancer $loadBalancer;
	private Config $config;
	private LanguageNameUtils $languageNameUtils;

	public function __construct(
		RevisionLookup $revisionLookup,
		ILoadBalancer $loadBalancer,
		Config $config,
		LanguageNameUtils $languageNameUtils
	) {
		$this->revisionLookup = $revisionLookup;
		$this->loadBalancer = $loadBalancer;
		$this->config = $config;
		$this->languageNameUtils = $languageNameUtils;
	}

	/** Do late setup that depends on configuration. */
	public static function setupTranslate(): void {
		global $wgTranslateYamlLibrary, $wgLogTypes;
		$hooks = [];

		/*
		 * Text that will be shown in translations if the translation is outdated.
		 * Must be something that does not conflict with actual content.
		 */
		if ( !defined( 'TRANSLATE_FUZZY' ) ) {
			define( 'TRANSLATE_FUZZY', '!!FUZZY!!' );
		}

		$wgTranslateYamlLibrary ??= function_exists( 'yaml_parse' ) ? 'phpyaml' : 'spyc';

		$hooks['PageSaveComplete'][] = [ TranslateEditAddons::class, 'onSaveComplete' ];

		// Page translation setup check and init if enabled.
		global $wgEnablePageTranslation;
		if ( $wgEnablePageTranslation ) {
			// Special page and the right to use it
			global $wgSpecialPages, $wgAvailableRights;
			$wgSpecialPages['PageTranslation'] = [
				'class' => PageTranslationSpecialPage::class,
				'services' => [
					'LanguageFactory',
					'LinkBatchFactory',
					'JobQueueGroup',
					'PermissionManager',
					'Translate:TranslatablePageMarker',
					'Translate:TranslatablePageParser',
					'Translate:MessageGroupMetadata',
					'Translate:TranslatablePageView',
					'Translate:TranslatablePageStateStore',
					'FormatterFactory'
				]
			];
			$wgSpecialPages['PageTranslationDeletePage'] = [
				'class' => DeleteTranslatableBundleSpecialPage::class,
				'services' => [
					'PermissionManager',
					'Translate:TranslatableBundleDeleter',
					'Translate:TranslatableBundleFactory',
				]
			];

			// right-pagetranslation action-pagetranslation
			$wgAvailableRights[] = 'pagetranslation';

			$wgSpecialPages['PageMigration'] = MigrateTranslatablePageSpecialPage::class;
			$wgSpecialPages['PagePreparation'] = PrepareTranslatablePageSpecialPage::class;

			global $wgActionFilteredLogs, $wgLogActionsHandlers;

			// log-description-pagetranslation log-name-pagetranslation logentry-pagetranslation-mark
			// logentry-pagetranslation-unmark logentry-pagetranslation-moveok
			// logentry-pagetranslation-movenok logentry-pagetranslation-deletefok
			// logentry-pagetranslation-deletefnok logentry-pagetranslation-deletelok
			// logentry-pagetranslation-deletelnok logentry-pagetranslation-encourage
			// logentry-pagetranslation-discourage logentry-pagetranslation-prioritylanguages
			// logentry-pagetranslation-associate logentry-pagetranslation-dissociate
			if ( !in_array( 'pagetranslation', $wgLogTypes ) ) {
				$wgLogTypes[] = 'pagetranslation';
			}
			$wgLogActionsHandlers['pagetranslation/mark'] = TranslatableBundleLogFormatter::class;
			$wgLogActionsHandlers['pagetranslation/unmark'] = TranslatableBundleLogFormatter::class;
			$wgLogActionsHandlers['pagetranslation/moveok'] = TranslatableBundleLogFormatter::class;
			$wgLogActionsHandlers['pagetranslation/movenok'] = TranslatableBundleLogFormatter::class;
			$wgLogActionsHandlers['pagetranslation/deletelok'] = TranslatableBundleLogFormatter::class;
			$wgLogActionsHandlers['pagetranslation/deletefok'] = TranslatableBundleLogFormatter::class;
			$wgLogActionsHandlers['pagetranslation/deletelnok'] = TranslatableBundleLogFormatter::class;
			$wgLogActionsHandlers['pagetranslation/deletefnok'] = TranslatableBundleLogFormatter::class;
			$wgLogActionsHandlers['pagetranslation/encourage'] = TranslatableBundleLogFormatter::class;
			$wgLogActionsHandlers['pagetranslation/discourage'] = TranslatableBundleLogFormatter::class;
			$wgLogActionsHandlers['pagetranslation/prioritylanguages'] = TranslatableBundleLogFormatter::class;
			$wgLogActionsHandlers['pagetranslation/associate'] = TranslatableBundleLogFormatter::class;
			$wgLogActionsHandlers['pagetranslation/dissociate'] = TranslatableBundleLogFormatter::class;
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

			if ( !in_array( 'messagebundle', $wgLogTypes ) ) {
				$wgLogTypes[] = 'messagebundle';
			}
			$wgLogActionsHandlers['messagebundle/moveok'] = TranslatableBundleLogFormatter::class;
			$wgLogActionsHandlers['messagebundle/movenok'] = TranslatableBundleLogFormatter::class;
			$wgLogActionsHandlers['messagebundle/deletefok'] = TranslatableBundleLogFormatter::class;
			$wgLogActionsHandlers['messagebundle/deletefnok'] = TranslatableBundleLogFormatter::class;
			$wgActionFilteredLogs['messagebundle'] = [
				'move' => [ 'moveok', 'movenok' ],
				'delete' => [ 'deletefok', 'deletefnok' ],
			];

			$wgLogActionsHandlers['import/translatable-bundle'] = TranslatableBundleLogFormatter::class;

			global $wgJobClasses;
			$wgJobClasses['RenderTranslationPageJob'] = RenderTranslationPageJob::class;
			$wgJobClasses['NonPrioritizedRenderTranslationPageJob'] = RenderTranslationPageJob::class;
			$wgJobClasses['MoveTranslatableBundleJob'] = MoveTranslatableBundleJob::class;
			$wgJobClasses['DeleteTranslatableBundleJob'] = DeleteTranslatableBundleJob::class;
			$wgJobClasses['UpdateTranslatablePageJob'] = UpdateTranslatablePageJob::class;

			// API modules
			global $wgAPIModules;
			$wgAPIModules['markfortranslation'] = [
				'class' => MarkForTranslationActionApi::class,
				'services' => [
					'Translate:TranslatablePageMarker',
					'Translate:MessageGroupMetadata',
				]
			];

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
			$hooks['BeforePageDisplay'][] = [ Hooks::class, 'onBeforePageDisplay' ];

			// Disable VE
			$hooks['VisualEditorBeforeEditor'][] = [ Hooks::class, 'onVisualEditorBeforeEditor' ];

			// Check syntax for \<translate>
			$hooks['MultiContentSave'][] = [ Hooks::class, 'tpSyntaxCheck' ];
			$hooks['EditFilterMergedContent'][] =
				[ Hooks::class, 'tpSyntaxCheckForEditContent' ];

			// Add transtag to page props for discovery
			$hooks['PageSaveComplete'][] = [ Hooks::class, 'addTranstagAfterSave' ];

			$hooks['RevisionRecordInserted'][] = [ Hooks::class, 'updateTranstagOnNullRevisions' ];

			// Register different ways to show language links
			$hooks['ParserFirstCallInit'][] = [ self::class, 'setupParserHooks' ];
			$hooks['LanguageLinks'][] = [ Hooks::class, 'addLanguageLinks' ];
			$hooks['SkinTemplateGetLanguageLink'][] = [ Hooks::class, 'formatLanguageLink' ];

			// Allow templates to query whether they are transcluded in a translatable/translated page
			$hooks['GetMagicVariableIDs'][] = [ Hooks::class, 'onGetMagicVariableIDs' ];
			$hooks['ParserGetVariableValueSwitch'][] = [ Hooks::class, 'onParserGetVariableValueSwitch' ];

			// Strip \<translate> tags etc. from source pages when rendering
			$hooks['ParserBeforeInternalParse'][] = [ Hooks::class, 'renderTagPage' ];
			// Strip \<translate> tags etc. from source pages when preprocessing
			$hooks['ParserBeforePreprocess'][] = [ Hooks::class, 'preprocessTagPage' ];
			$hooks['ParserOutputPostCacheTransform'][] =
				[ Hooks::class, 'onParserOutputPostCacheTransform' ];

			$hooks['BeforeParserFetchTemplateRevisionRecord'][] =
				[ Hooks::class, 'fetchTranslatableTemplateAndTitle' ];

			// Set the page content language
			$hooks['PageContentLanguage'][] = [ Hooks::class, 'onPageContentLanguage' ];

			// Prevent editing of certain pages in translations namespace
			$hooks['getUserPermissionsErrorsExpensive'][] =
				[ Hooks::class, 'onGetUserPermissionsErrorsExpensive' ];
			// Prevent editing of translation pages directly
			$hooks['getUserPermissionsErrorsExpensive'][] =
				[ Hooks::class, 'preventDirectEditing' ];

			// Our custom header for translation pages
			$hooks['ArticleViewHeader'][] = [ Hooks::class, 'translatablePageHeader' ];

			// Edit notice shown on translatable pages
			$hooks['TitleGetEditNotices'][] = [ Hooks::class, 'onTitleGetEditNotices' ];

			// Custom move page that can move all the associated pages too
			$hooks['SpecialPage_initList'][] = [ Hooks::class, 'replaceMovePage' ];
			// Locking during page moves
			$hooks['getUserPermissionsErrorsExpensive'][] =
				[ Hooks::class, 'lockedPagesCheck' ];
			// Disable action=delete
			$hooks['ArticleConfirmDelete'][] = [ Hooks::class, 'disableDelete' ];

			// Replace subpage logic behavior
			$hooks['SkinSubPageSubtitle'][] = [ Hooks::class, 'replaceSubtitle' ];

			// Replaced edit tab with translation tab for translation pages
			$hooks['SkinTemplateNavigation::Universal'][] = [ Hooks::class, 'translateTab' ];

			// Update translated page when translation unit is moved
			$hooks['PageMoveComplete'][] = [ Hooks::class, 'onMovePageTranslationUnits' ];

			// Update translated page when translation unit is deleted
			$hooks['ArticleDeleteComplete'][] = [ Hooks::class, 'onDeleteTranslationUnit' ];

			// Prevent editing of translation pages.
			$hooks['ReplaceTextFilterPageTitlesForEdit'][] = [ Hooks::class, 'onReplaceTextFilterPageTitlesForEdit' ];
			// Prevent renaming of translatable pages and their translation and translation units
			$hooks['ReplaceTextFilterPageTitlesForRename'][] =
				[ Hooks::class, 'onReplaceTextFilterPageTitlesForRename' ];

			// Auto-create translated categories when not empty
			$hooks['LinksUpdateComplete'][] = [ Hooks::class, 'onLinksUpdateComplete' ];
		}

		global $wgTranslateUseSandbox;
		if ( $wgTranslateUseSandbox ) {
			global $wgSpecialPages, $wgAvailableRights, $wgDefaultUserOptions;

			$wgSpecialPages['ManageTranslatorSandbox'] = [
				'class' => ManageTranslatorSandboxSpecialPage::class,
				'services' => [
					'Translate:TranslationStashReader',
					'UserOptionsLookup',
					'Translate:TranslateSandbox',
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
					'UserOptionsLookup',
					'LanguageFactory',
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

			global $wgLogTypes, $wgLogActionsHandlers;
			// log-name-translatorsandbox log-description-translatorsandbox
			if ( !in_array( 'translatorsandbox', $wgLogTypes ) ) {
				$wgLogTypes[] = 'translatorsandbox';
			}
			// logentry-translatorsandbox-promoted logentry-translatorsandbox-rejected
			$wgLogActionsHandlers['translatorsandbox/promoted'] = TranslateLogFormatter::class;
			$wgLogActionsHandlers['translatorsandbox/rejected'] = TranslateLogFormatter::class;

			// This is no longer used for new entries since 2016.07.
			// logentry-newusers-tsbpromoted
			$wgLogActionsHandlers['newusers/tsbpromoted'] = LogFormatter::class;

			$wgJobClasses['TranslateSandboxEmailJob'] = TranslateSandboxEmailJob::class;

			global $wgAPIModules;
			$wgAPIModules['translationstash'] = [
				'class' => TranslationStashActionApi::class,
				'services' => [
					'DBLoadBalancerFactory',
					'UserFactory',
					'Translate:MessageIndex'
				]
			];
			$wgAPIModules['translatesandbox'] = [
				'class' => TranslatorSandboxActionApi::class,
				'services' => [
					'UserFactory',
					'UserNameUtils',
					'UserOptionsManager',
					'WikiPageFactory',
					'UserOptionsLookup',
					'Translate:TranslateSandbox',
				],
				'args' => [
					static function () {
						return new ServiceOptions(
							TranslatorSandboxActionApi::CONSTRUCTOR_OPTIONS,
							MediaWikiServices::getInstance()->getMainConfig()
						);
					}
				]
			];
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
					'database' => false,
					'cutoff' => 0.75,
					'type' => 'ttmserver',
					'public' => false,
				];
			}
		}

		global $wgTranslateEnableMessageGroupSubscription;
		if ( $wgTranslateEnableMessageGroupSubscription ) {
			if ( !ExtensionRegistry::getInstance()->isLoaded( 'Echo' ) ) {
				throw new ConfigException(
					'Translate: Message group subscriptions (TranslateEnableMessageGroupSubscription) are ' .
					'enabled but Echo extension is not installed'
				);
			}
			MessageGroupSubscriptionHookHandler::registerHooks( $hooks );
			$wgJobClasses['MessageGroupSubscriptionNotificationJob'] = MessageGroupSubscriptionNotificationJob::class;
		}

		global $wgTranslateEnableEventLogging;
		if ( $wgTranslateEnableEventLogging ) {
			if ( !ExtensionRegistry::getInstance()->isLoaded( 'EventLogging' ) ) {
				throw new ConfigException(
					'Translate: Event logging (TranslateEnableEventLogging) is ' .
					'enabled but EventLogging extension is not installed'
				);
			}
		}

		global $wgTranslateEnableLuaIntegration;
		if ( $wgTranslateEnableLuaIntegration ) {
			if ( ExtensionRegistry::getInstance()->isLoaded( 'Scribunto' ) ) {
				$hooks[ 'ScribuntoExternalLibraries' ][] = static function ( string $engine, array &$extraLibraries ) {
					$scribuntoHookHandler = new ScribuntoHookHandler();
					$scribuntoHookHandler->onScribuntoExternalLibraries( $engine, $extraLibraries );
				};
			} else {
				wfLogWarning(
					'Translate: Lua integration (TranslateEnableLuaIntegration) is ' .
					'enabled but Scribunto extension is not installed'
				);
			}
		}

		static::registerHookHandlers( $hooks );
	}

	private static function registerHookHandlers( array $hooks ): void {
		if ( defined( 'MW_PHPUNIT_TEST' ) && MediaWikiServices::hasInstance() ) {
			// When called from a test case's setUp() method,
			// we can use HookContainer, but we cannot use SettingsBuilder.
			$hookContainer = MediaWikiServices::getInstance()->getHookContainer();
			foreach ( $hooks as $name => $handlers ) {
				foreach ( $handlers as $h ) {
					$hookContainer->register( $name, $h );
				}
			}
		} else {
			$settingsBuilder = SettingsBuilder::getInstance();
			$settingsBuilder->registerHookHandlers( $hooks );
		}
	}

	/**
	 * Prevents anyone from registering or logging in as FuzzyBot
	 * @inheritDoc
	 */
	public function onUserGetReservedNames( &$reservedUsernames ): void {
		$reservedUsernames[] = FuzzyBot::getName();
		$reservedUsernames[] = TranslateUserManager::getName();
	}

	/** Used for setting an AbuseFilter variable. */
	public static function onAbuseFilterAlterVariables(
		VariableHolder &$vars, Title $title, User $user
	): void {
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

	/** Computes the translate_source_text and translate_target_language AbuseFilter variables */
	public static function onAbuseFilterComputeVariable(
		string $method,
		VariableHolder $vars,
		array $parameters,
		?string &$result
	): bool {
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

	/** Register AbuseFilter variables provided by Translate. */
	public static function onAbuseFilterBuilder( array &$builderValues ): void {
		// The following messages are generated here:
		// * abusefilter-edit-builder-vars-translate-source-text
		// * abusefilter-edit-builder-vars-translate-target-language
		$builderValues['vars']['translate_source_text'] = 'translate-source-text';
		$builderValues['vars']['translate_target_language'] = 'translate-target-language';
	}

	/**
	 * Hook: ParserFirstCallInit
	 * Registers \<languages> tag with the parser.
	 */
	public static function setupParserHooks( Parser $parser ): void {
		// For nice language list in-page
		$parser->setHook( 'languages', [ Hooks::class, 'languages' ] );
	}

	/**
	 * Hook: PageContentLanguage
	 * Set the correct page content language for translation units.
	 * @param Title $title
	 * @param Language|StubUserLang|string &$pageLang
	 */
	public static function onPageContentLanguage( Title $title, &$pageLang ): void {
		$handle = new MessageHandle( $title );
		if ( $handle->isMessageNamespace() ) {
			$pageLang = $handle->getEffectiveLanguage();
		}
	}

	/**
	 * Hook: LanguageGetTranslatedLanguageNames
	 * Hook: TranslateSupportedLanguages
	 */
	public static function translateMessageDocumentationLanguage( array &$names, ?string $code ): void {
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

	/** Hook: SpecialSearchProfiles */
	public static function searchProfile( array &$profiles ): void {
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

	/** Hook: SpecialSearchProfileForm */
	public static function searchProfileForm(
		SpecialSearch $search,
		string &$form,
		string $profile,
		string $term,
		array $opts
	): void {
		if ( $profile !== 'translation' ) {
			return;
		}

		if ( Services::getInstance()->getTtmServerFactory()->getDefaultForQuerying() instanceof SearchableTtmServer ) {
			$href = SpecialPage::getTitleFor( 'SearchTranslations' )
				->getFullUrl( [ 'query' => $term ] );
			$form = Html::successBox(
				$search->msg( 'translate-searchprofile-note', $href )->parse(),
				'plainlinks'
			);
			$search->getOutput()->addModuleStyles( 'mediawiki.codex.messagebox.styles' );
			return;
		}

		if ( !$search->getSearchEngine()->supports( 'title-suffix-filter' ) ) {
			return;
		}

		$hidden = '';
		foreach ( $opts as $key => $value ) {
			$hidden .= Html::hidden( $key, $value );
		}

		$context = $search->getContext();
		$code = $context->getLanguage()->getCode();
		$selected = $context->getRequest()->getVal( 'languagefilter' );

		$languages = Utilities::getLanguageNames( $code );
		ksort( $languages );

		$selector = new XmlSelect( 'languagefilter', 'languagefilter' );
		$selector->setDefault( $selected );
		$selector->addOption( wfMessage( 'translate-search-nofilter' )->text(), '-' );
		foreach ( $languages as $code => $name ) {
			$selector->addOption( "$code - $name", $code );
		}

		$selector = $selector->getHTML();

		$label = Html::label(
			wfMessage( 'translate-search-languagefilter' )->text(),
			'languagefilter'
		) . "\u{00A0}";

		$form .= Html::rawElement(
			'fieldset',
			[ 'id' => 'mw-searchoptions' ],
			$hidden . $label . $selector
		);
	}

	/** Hook: SpecialSearchSetupEngine */
	public static function searchProfileSetupEngine(
		SpecialSearch $search,
		string $profile,
		SearchEngine $engine
	): void {
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

	/** Hook: ParserAfterTidy */
	public static function preventCategorization( Parser $parser, string &$html ): void {
		if ( $parser->getOptions()->getInterfaceMessage() ) {
			return;
		}
		$pageReference = $parser->getPage();
		if ( !$pageReference ) {
			return;
		}

		$linkTarget = TitleValue::newFromPage( $pageReference );
		$handle = new MessageHandle( $linkTarget );
		if ( $handle->isMessageNamespace() && !$handle->isDoc() ) {
			$parserOutput = $parser->getOutput();
			$names = $parserOutput->getCategoryNames();
			$parserCategories = [];
			foreach ( $names as $name ) {
				$parserCategories[$name] = $parserOutput->getCategorySortKey( $name );
			}
			$parserOutput->setExtensionData( 'translate-fake-categories', $parserCategories );
			$parserOutput->setCategories( [] );
		}
	}

	/** Hook: OutputPageParserOutput */
	public static function showFakeCategories( OutputPage $outputPage, ParserOutput $parserOutput ): void {
		$fakeCategories = $parserOutput->getExtensionData( 'translate-fake-categories' );
		if ( $fakeCategories ) {
			$outputPage->addCategoryLinks( $fakeCategories );
		}
	}

	/**
	 * Hook: MakeGlobalVariablesScript
	 * Adds $wgTranslateDocumentationLanguageCode to ResourceLoader configuration
	 * when Special:Translate is shown.
	 */
	public static function addConfig( array &$vars, OutputPage $out ): void {
		global $wgTranslateDocumentationLanguageCode,
			$wgTranslatePermissionUrl,
			$wgTranslateUseSandbox;

		$title = $out->getTitle();
		if ( $title->isSpecial( 'Translate' ) ||
			$title->isSpecial( 'TranslationStash' ) ||
			$title->isSpecial( 'SearchTranslations' )
		) {
			$user = $out->getUser();
			$vars['TranslateRight'] = $user->isAllowedAll( 'translate', 'edit' );
			$vars['TranslateMessageReviewRight'] = $user->isAllowed( 'translate-messagereview' );
			$vars['DeleteRight'] = $user->isAllowed( 'delete' );
			$vars['TranslateManageRight'] = $user->isAllowed( 'translate-manage' );
			$vars['wgTranslateDocumentationLanguageCode'] = $wgTranslateDocumentationLanguageCode;
			$vars['wgTranslatePermissionUrl'] = $wgTranslatePermissionUrl;
			$vars['wgTranslateUseSandbox'] = $wgTranslateUseSandbox;
		}
	}

	/** Hook: AdminLinks */
	public static function onAdminLinks( ALTree $tree ): void {
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
	 */
	public static function onMergeAccountFromTo( User $oldUser, User $newUser ): void {
		$dbw = MediaWikiServices::getInstance()->getDBLoadBalancer()->getMaintenanceConnectionRef( DB_PRIMARY );

		// Update the non-duplicate rows, we'll just delete
		// the duplicate ones later
		foreach ( self::USER_MERGE_TABLES as $table => $field ) {
			if ( $dbw->tableExists( $table, __METHOD__ ) ) {
				$dbw->newUpdateQueryBuilder()
					->update( $table )
					->ignore()
					->set( [ $field => $newUser->getId() ] )
					->where( [ $field => $oldUser->getId() ] )
					->caller( __METHOD__ )
					->execute();
			}
		}
	}

	/**
	 * Hook: DeleteAccount
	 * For UserMerge extension.
	 */
	public static function onDeleteAccount( User $oldUser ): void {
		$dbw = MediaWikiServices::getInstance()->getDBLoadBalancer()->getMaintenanceConnectionRef( DB_PRIMARY );

		// Delete any remaining rows that didn't get merged
		foreach ( self::USER_MERGE_TABLES as $table => $field ) {
			if ( $dbw->tableExists( $table, __METHOD__ ) ) {
				$dbw->newDeleteQueryBuilder()
					->deleteFrom( $table )
					->where( [ $field => $oldUser->getId() ] )
					->caller( __METHOD__ )
					->execute();
			}
		}
	}

	/** Hook: AbortEmailNotification */
	public static function onAbortEmailNotificationReview(
		User $editor,
		Title $title,
		RecentChange $rc
	): bool {
		return $rc->getAttribute( 'rc_log_type' ) !== 'translationreview';
	}

	/**
	 * Hook: TitleIsAlwaysKnown
	 * Make Special:MyLanguage links red if the target page doesn't exist.
	 * A bit hacky because the core code is not so flexible.
	 * @param Title $target Title object that is being checked
	 * @param bool|null &$isKnown Whether MediaWiki currently thinks this page is known
	 * @return bool True or no return value to continue or false to abort
	 */
	public static function onTitleIsAlwaysKnown( $target, &$isKnown ): bool {
		if ( !$target->inNamespace( NS_SPECIAL ) ) {
			return true;
		}

		[ $name, $subpage ] = MediaWikiServices::getInstance()
			->getSpecialPageFactory()->resolveAlias( $target->getDBkey() );
		if ( $name !== 'MyLanguage' || $subpage === null || $subpage === '' ) {
			return true;
		}

		$realTarget = Title::newFromText( $subpage );
		if ( !$realTarget || !$realTarget->exists() ) {
			$isKnown = false;

			return false;
		}

		return true;
	}

	/** @inheritDoc */
	public function onParserFirstCallInit( $parser ) {
		$parser->setFunctionHook( 'translation', [ $this, 'translateRenderParserFunction' ] );
	}

	public function translateRenderParserFunction( Parser $parser ): string {
		if ( $parser->getOptions()->getInterfaceMessage() ) {
			return '';
		}
		$pageReference = $parser->getPage();
		if ( !$pageReference ) {
			return '';
		}
		$linkTarget = TitleValue::newFromPage( $pageReference );
		$handle = new MessageHandle( $linkTarget );
		$code = $handle->getCode();
		if ( $this->languageNameUtils->isKnownLanguageTag( $code ) ) {
			return '/' . $code;
		}
		return '';
	}

	/**
	 * Runs the configured validator to ensure that the message meets the required criteria.
	 * Hook: EditFilterMergedContent
	 * @return bool true if message is valid, false otherwise.
	 */
	public static function validateMessage(
		IContextSource $context,
		Content $content,
		Status $status,
		string $summary,
		User $user
	): bool {
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

			if ( method_exists( $group, 'getMessageContent' ) ) {
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
		// tp:tag and tp:mark handling is in Hooks::updateTranstagOnNullRevisions.
		$tagsToCopy = [ RevTagStore::FUZZY_TAG, RevTagStore::TRANSVER_PROP ];

		$db = $this->loadBalancer->getConnection( DB_PRIMARY );
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

	/** @inheritDoc */
	public function onListDefinedTags( &$tags ): void {
		$tags[] = 'translate-translation-pages';
	}

	/** @inheritDoc */
	public function onChangeTagsListActive( &$tags ): void {
		if ( $this->config->get( 'EnablePageTranslation' ) ) {
			$tags[] = 'translate-translation-pages';
		}
	}

	public static function getLanguageJson( Context $context ): array {
		return [
			'supportedLanguages' => Utilities::getLanguageNames( $context->getLanguage() ),
			'undeterminedLanguageCode' => AggregateMessageGroup::UNDETERMINED_LANGUAGE_CODE
		];
	}
}
