<?php
/**
 * List of services in this extension with construction instructions.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */

use MediaWiki\Config\ServiceOptions;
use MediaWiki\Extension\Translate\Cache\PersistentCache;
use MediaWiki\Extension\Translate\Cache\PersistentDatabaseCache;
use MediaWiki\Extension\Translate\FileFormatSupport\FileFormatFactory;
use MediaWiki\Extension\Translate\HookRunner;
use MediaWiki\Extension\Translate\LogNames;
use MediaWiki\Extension\Translate\MessageBundleTranslation\MessageBundleDependencyPurger;
use MediaWiki\Extension\Translate\MessageBundleTranslation\MessageBundleMessageGroupFactory;
use MediaWiki\Extension\Translate\MessageBundleTranslation\MessageBundleStore;
use MediaWiki\Extension\Translate\MessageBundleTranslation\MessageBundleTranslationLoader;
use MediaWiki\Extension\Translate\MessageGroupConfiguration\FileBasedMessageGroupFactory;
use MediaWiki\Extension\Translate\MessageGroupConfiguration\HookDefinedMessageGroupFactory;
use MediaWiki\Extension\Translate\MessageGroupConfiguration\MessageGroupConfigurationParser;
use MediaWiki\Extension\Translate\MessageGroupProcessing\AggregateGroupManager;
use MediaWiki\Extension\Translate\MessageGroupProcessing\AggregateGroupMessageGroupFactory;
use MediaWiki\Extension\Translate\MessageGroupProcessing\CsvTranslationImporter;
use MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroupReviewStore;
use MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroups;
use MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroupSubscription;
use MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroupSubscriptionHookHandler;
use MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroupSubscriptionStore;
use MediaWiki\Extension\Translate\MessageGroupProcessing\RevTagStore;
use MediaWiki\Extension\Translate\MessageGroupProcessing\SubpageListBuilder;
use MediaWiki\Extension\Translate\MessageGroupProcessing\TranslatableBundleExporter;
use MediaWiki\Extension\Translate\MessageGroupProcessing\TranslatableBundleFactory;
use MediaWiki\Extension\Translate\MessageGroupProcessing\TranslatableBundleImporter;
use MediaWiki\Extension\Translate\MessageGroupProcessing\TranslatableBundleStatusStore;
use MediaWiki\Extension\Translate\MessageGroupProcessing\TranslatablePageStore;
use MediaWiki\Extension\Translate\MessageGroupProcessing\WorkflowStatesMessageGroupLoader;
use MediaWiki\Extension\Translate\MessageLoading\CDBMessageIndex;
use MediaWiki\Extension\Translate\MessageLoading\DatabaseMessageIndex;
use MediaWiki\Extension\Translate\MessageLoading\HashMessageIndex;
use MediaWiki\Extension\Translate\MessageLoading\MessageIndex;
use MediaWiki\Extension\Translate\MessageLoading\MessageIndexStore;
use MediaWiki\Extension\Translate\MessageProcessing\MessageGroupMetadata;
use MediaWiki\Extension\Translate\PageTranslation\TranslatableBundleDeleter;
use MediaWiki\Extension\Translate\PageTranslation\TranslatableBundleMover;
use MediaWiki\Extension\Translate\PageTranslation\TranslatablePageMarker;
use MediaWiki\Extension\Translate\PageTranslation\TranslatablePageMessageGroupFactory;
use MediaWiki\Extension\Translate\PageTranslation\TranslatablePageParser;
use MediaWiki\Extension\Translate\PageTranslation\TranslatablePageStateStore;
use MediaWiki\Extension\Translate\PageTranslation\TranslatablePageView;
use MediaWiki\Extension\Translate\PageTranslation\TranslationUnitStoreFactory;
use MediaWiki\Extension\Translate\Statistics\MessageGroupStatsTableFactory;
use MediaWiki\Extension\Translate\Statistics\MessagePrefixStats;
use MediaWiki\Extension\Translate\Statistics\ProgressStatsTableFactory;
use MediaWiki\Extension\Translate\Statistics\TranslationStatsDataProvider;
use MediaWiki\Extension\Translate\Statistics\TranslatorActivity;
use MediaWiki\Extension\Translate\Statistics\TranslatorActivityQuery;
use MediaWiki\Extension\Translate\Synchronization\ExternalMessageSourceStateComparator;
use MediaWiki\Extension\Translate\Synchronization\ExternalMessageSourceStateImporter;
use MediaWiki\Extension\Translate\Synchronization\GroupSynchronizationCache;
use MediaWiki\Extension\Translate\TranslatorInterface\EntitySearch;
use MediaWiki\Extension\Translate\TranslatorSandbox\TranslateSandbox;
use MediaWiki\Extension\Translate\TranslatorSandbox\TranslationStashReader;
use MediaWiki\Extension\Translate\TranslatorSandbox\TranslationStashStorage;
use MediaWiki\Extension\Translate\TtmServer\TtmServerFactory;
use MediaWiki\Extension\Translate\Utilities\ConfigHelper;
use MediaWiki\Extension\Translate\Utilities\ParsingPlaceholderFactory;
use MediaWiki\Extension\Translate\Utilities\StringComparators\SimpleStringComparator;
use MediaWiki\Logger\LoggerFactory;
use MediaWiki\MediaWikiServices;

/** @phpcs-require-sorted-array */
return [
	'Translate:AggregateGroupManager' => static function (
		MediaWikiServices $services
	): AggregateGroupManager {
		return new AggregateGroupManager(
			$services->getTitleFactory(),
			$services->get( 'Translate:MessageGroupMetadata' )
		);
	},

	'Translate:AggregateGroupMessageGroupFactory' => static function (
		MediaWikiServices $services
	): AggregateGroupMessageGroupFactory {
		return new AggregateGroupMessageGroupFactory(
			$services->get( 'Translate:MessageGroupMetadata' )
		);
	},

	'Translate:ConfigHelper' => static function (): ConfigHelper {
		return new ConfigHelper();
	},

	'Translate:CsvTranslationImporter' => static function ( MediaWikiServices $services ): CsvTranslationImporter {
		return new CsvTranslationImporter( $services->getWikiPageFactory() );
	},

	'Translate:EntitySearch' => static function ( MediaWikiServices $services ): EntitySearch {
		return new EntitySearch(
			$services->getMainWANObjectCache(),
			$services->getCollationFactory()->makeCollation( 'uca-default-u-kn' ),
			MessageGroups::singleton(),
			$services->getNamespaceInfo(),
			$services->get( 'Translate:MessageIndex' ),
			$services->getTitleParser(),
			$services->getTitleFormatter()
		);
	},

	'Translate:ExternalMessageSourceStateComparator' => static function (
		MediaWikiServices $services
	): ExternalMessageSourceStateComparator {
		return new ExternalMessageSourceStateComparator(
			new SimpleStringComparator(),
			$services->getRevisionLookup(),
			$services->getPageStore()
		);
	},

	'Translate:ExternalMessageSourceStateImporter' => static function (
		MediaWikiServices $services
	): ExternalMessageSourceStateImporter {
		return new ExternalMessageSourceStateImporter(
			$services->get( 'Translate:GroupSynchronizationCache' ),
			$services->getJobQueueGroup(),
			LoggerFactory::getInstance( LogNames::GROUP_SYNCHRONIZATION ),
			$services->get( 'Translate:MessageIndex' ),
			$services->getTitleFactory(),
			$services->get( 'Translate:MessageGroupSubscription' ),
			new ServiceOptions(
				ExternalMessageSourceStateImporter::CONSTRUCTOR_OPTIONS,
				$services->getMainConfig()
			)
		);
	},

	'Translate:FileBasedMessageGroupFactory' => static function (
		MediaWikiServices $services
	): FileBasedMessageGroupFactory {
		return new FileBasedMessageGroupFactory(
			new MessageGroupConfigurationParser(),
			$services->getContentLanguageCode()->toString(),
			new ServiceOptions(
				FileBasedMessageGroupFactory::SERVICE_OPTIONS,
				$services->getMainConfig()
			),
		);
	},

	'Translate:FileFormatFactory' => static function ( MediaWikiServices $services ): FileFormatFactory {
		return new FileFormatFactory( $services->getObjectFactory() );
	},

	'Translate:GroupSynchronizationCache' => static function (
		MediaWikiServices $services
	): GroupSynchronizationCache {
		return new GroupSynchronizationCache( $services->get( 'Translate:PersistentCache' ) );
	},

	'Translate:HookDefinedMessageGroupFactory' => static function (
		MediaWikiServices $services
	): HookDefinedMessageGroupFactory {
		return new HookDefinedMessageGroupFactory( $services->get( 'Translate:HookRunner' ) );
	},

	'Translate:HookRunner' => static function (
		MediaWikiServices $services
	): HookRunner {
		return new HookRunner( $services->getHookContainer() );
	},

	'Translate:MessageBundleDependencyPurger' => static function (
		MediaWikiServices $services
	): MessageBundleDependencyPurger {
		return new MessageBundleDependencyPurger( $services->get( 'Translate:TranslatableBundleFactory' ) );
	},

	'Translate:MessageBundleMessageGroupFactory' => static function (
		MediaWikiServices $services
	): MessageBundleMessageGroupFactory {
		return new MessageBundleMessageGroupFactory(
			$services->get( 'Translate:MessageGroupMetadata' ),
			new ServiceOptions(
				MessageBundleMessageGroupFactory::SERVICE_OPTIONS,
				$services->getMainConfig()
			),
		);
	},

	'Translate:MessageBundleStore' => static function ( MediaWikiServices $services ): MessageBundleStore {
		return new MessageBundleStore(
			$services->get( 'Translate:RevTagStore' ),
			$services->getJobQueueGroup(),
			$services->getLanguageNameUtils(),
			$services->get( 'Translate:MessageIndex' ),
			$services->get( 'Translate:MessageGroupMetadata' )
		);
	},

	'Translate:MessageBundleTranslationLoader' => static function (
		MediaWikiServices $services
	): MessageBundleTranslationLoader {
		return new MessageBundleTranslationLoader( $services->getLanguageFallback() );
	},

	'Translate:MessageGroupMetadata' => static function ( MediaWikiServices $services ): MessageGroupMetadata {
		return new MessageGroupMetadata( $services->getConnectionProvider() );
	},

	'Translate:MessageGroupReviewStore' => static function ( MediaWikiServices $services ): MessageGroupReviewStore {
		return new MessageGroupReviewStore(
			$services->getConnectionProvider(),
			$services->get( 'Translate:HookRunner' )
		);
	},

	'Translate:MessageGroupStatsTableFactory' => static function (
		MediaWikiServices $services
	): MessageGroupStatsTableFactory {
		return new MessageGroupStatsTableFactory(
			$services->get( 'Translate:ProgressStatsTableFactory' ),
			$services->getLinkRenderer(),
			$services->get( 'Translate:MessageGroupReviewStore' ),
			$services->get( 'Translate:MessageGroupMetadata' ),
			$services->getMainConfig()->get( 'TranslateWorkflowStates' ) !== false
		);
	},

	'Translate:MessageGroupSubscription' => static function (
		MediaWikiServices $services
	): MessageGroupSubscription {
		return new MessageGroupSubscription(
			$services->get( 'Translate:MessageGroupSubscriptionStore' ),
			$services->getJobQueueGroup(),
			$services->getUserIdentityLookup(),
			LoggerFactory::getInstance( LogNames::GROUP_SUBSCRIPTION ),
			new ServiceOptions(
				MessageGroupSubscription::CONSTRUCTOR_OPTIONS,
				$services->getMainConfig()
			)
		);
	},

	'Translate:MessageGroupSubscriptionHookHandler' => static function (
		MediaWikiServices $services
	): ?MessageGroupSubscriptionHookHandler {
		if ( !$services->getExtensionRegistry()->isLoaded( 'Echo' ) ) {
			return null;
		}
		return new MessageGroupSubscriptionHookHandler(
			$services->get( 'Translate:MessageGroupSubscription' ),
			$services->getUserFactory()
		);
	},

	'Translate:MessageGroupSubscriptionStore' => static function (
		MediaWikiServices $services
	): MessageGroupSubscriptionStore {
		return new MessageGroupSubscriptionStore( $services->getConnectionProvider() );
	},

	'Translate:MessageIndex' => static function ( MediaWikiServices $services ): MessageIndex {
		$params = (array)$services->getMainConfig()->get( 'TranslateMessageIndex' );
		$class = array_shift( $params );

		$implementationMap = [
			// Aliases for BC
			'HashMessageIndex' => HashMessageIndex::class,
			'CDBMessageIndex' => CDBMessageIndex::class,
			'DatabaseMessageIndex' => DatabaseMessageIndex::class,
			// Recommended values
			'hash' => HashMessageIndex::class,
			'cdb' => CDBMessageIndex::class,
			'database' => DatabaseMessageIndex::class,
		];

		/** @var MessageIndexStore $store */
		$messageIndexStoreClass = $implementationMap[$class] ?? $implementationMap['database'];
		return new MessageIndex(
			new $messageIndexStoreClass,
			$services->getMainWANObjectCache(),
			$services->getJobQueueGroup(),
			$services->get( 'Translate:HookRunner' ),
			LoggerFactory::getInstance( LogNames::MAIN ),
			$services->getMainObjectStash(),
			$services->getConnectionProvider(),
			new ServiceOptions( MessageIndex::SERVICE_OPTIONS, $services->getMainConfig() ),
		);
	},

	'Translate:MessagePrefixStats' => static function ( MediaWikiServices $services ): MessagePrefixStats {
		return new MessagePrefixStats( $services->getTitleParser() );
	},

	'Translate:ParsingPlaceholderFactory' => static function (): ParsingPlaceholderFactory {
		return new ParsingPlaceholderFactory();
	},

	'Translate:PersistentCache' => static function ( MediaWikiServices $services ): PersistentCache {
		return new PersistentDatabaseCache(
			$services->getConnectionProvider(),
			$services->getJsonCodec()
		);
	},

	'Translate:ProgressStatsTableFactory' => static function ( MediaWikiServices $services ): ProgressStatsTableFactory
	{
		return new ProgressStatsTableFactory(
			$services->getLinkRenderer(),
			$services->get( 'Translate:ConfigHelper' ),
			$services->get( 'Translate:MessageGroupMetadata' )
		);
	},

	'Translate:RevTagStore' => static function ( MediaWikiServices $services ): RevTagStore {
		return new RevTagStore( $services->getConnectionProvider() );
	},

	'Translate:SubpageListBuilder' => static function ( MediaWikiServices $services ): SubpageListBuilder
	{
		return new SubpageListBuilder(
			$services->get( 'Translate:TranslatableBundleFactory' ),
			$services->getLinkBatchFactory()
		);
	},

	'Translate:TranslatableBundleDeleter' => static function ( MediaWikiServices $services ): TranslatableBundleDeleter
	{
		return new TranslatableBundleDeleter(
			$services->getMainObjectStash(),
			$services->getJobQueueGroup(),
			$services->get( 'Translate:SubpageListBuilder' ),
			$services->get( 'Translate:TranslatableBundleFactory' )
		);
	},

	'Translate:TranslatableBundleExporter' => static function (
		MediaWikiServices $services
	): TranslatableBundleExporter {
		return new TranslatableBundleExporter(
			$services->get( 'Translate:SubpageListBuilder' ),
			$services->getWikiExporterFactory(),
			$services->getConnectionProvider()
		);
	},

	'Translate:TranslatableBundleFactory' => static function ( MediaWikiServices $services ): TranslatableBundleFactory
	{
		return new TranslatableBundleFactory(
			$services->get( 'Translate:TranslatablePageStore' ),
			$services->get( 'Translate:MessageBundleStore' )
		);
	},

	'Translate:TranslatableBundleImporter' => static function (
		MediaWikiServices $services
	): TranslatableBundleImporter {
		return new TranslatableBundleImporter(
			$services->getWikiImporterFactory(),
			$services->get( 'Translate:TranslatablePageParser' ),
			$services->getRevisionLookup(),
			$services->getNamespaceInfo(),
			$services->getTitleFactory(),
			$services->getFormatterFactory()
		);
	},

	'Translate:TranslatableBundleMover' => static function ( MediaWikiServices $services ): TranslatableBundleMover
	{
		return new TranslatableBundleMover(
			$services->getMovePageFactory(),
			$services->getJobQueueGroup(),
			$services->getLinkBatchFactory(),
			$services->get( 'Translate:TranslatableBundleFactory' ),
			$services->get( 'Translate:SubpageListBuilder' ),
			$services->getConnectionProvider(),
			$services->getObjectCacheFactory(),
			$services->getMainConfig()->get( 'TranslatePageMoveLimit' )
		);
	},

	'Translate:TranslatableBundleStatusStore' =>
		static function ( MediaWikiServices $services ): TranslatableBundleStatusStore {
			return new TranslatableBundleStatusStore(
				$services->getConnectionProvider()->getPrimaryDatabase(),
				$services->getCollationFactory()->makeCollation( 'uca-default-u-kn' ),
				$services->getDBLoadBalancer()->getMaintenanceConnectionRef( DB_PRIMARY )
			);
		},

	'Translate:TranslatablePageMarker' => static function ( MediaWikiServices $services ): TranslatablePageMarker {
		return new TranslatablePageMarker(
			$services->getConnectionProvider(),
			$services->getJobQueueGroup(),
			$services->getLinkRenderer(),
			MessageGroups::singleton(),
			$services->get( 'Translate:MessageIndex' ),
			$services->getTitleFormatter(),
			$services->getTitleParser(),
			$services->get( 'Translate:TranslatablePageParser' ),
			$services->get( 'Translate:TranslatablePageStore' ),
			$services->get( 'Translate:TranslatablePageStateStore' ),
			$services->get( 'Translate:TranslationUnitStoreFactory' ),
			$services->get( 'Translate:MessageGroupMetadata' ),
			$services->getWikiPageFactory(),
			$services->get( 'Translate:TranslatablePageView' ),
			$services->get( 'Translate:MessageGroupSubscription' ),
			$services->getFormatterFactory()
		);
	},

	'Translate:TranslatablePageMessageGroupFactory' => static function (
		MediaWikiServices $services
	): TranslatablePageMessageGroupFactory {
		return new TranslatablePageMessageGroupFactory(
			new ServiceOptions(
				TranslatablePageMessageGroupFactory::SERVICE_OPTIONS,
				$services->getMainConfig()
			),
		);
	},

	'Translate:TranslatablePageParser' => static function ( MediaWikiServices $services ): TranslatablePageParser
	{
		return new TranslatablePageParser(
			$services->get( 'Translate:ParsingPlaceholderFactory' )
		);
	},

	'Translate:TranslatablePageStateStore' => static function (
		MediaWikiServices $services
	): TranslatablePageStateStore {
		return new TranslatablePageStateStore(
			$services->get( 'Translate:PersistentCache' ),
			$services->getPageStore()
		);
	},

	'Translate:TranslatablePageStore' => static function ( MediaWikiServices $services ): TranslatablePageStore
	{
		return new TranslatablePageStore(
			$services->get( 'Translate:MessageIndex' ),
			$services->getJobQueueGroup(),
			$services->get( 'Translate:RevTagStore' ),
			$services->getConnectionProvider(),
			$services->get( 'Translate:TranslatableBundleStatusStore' ),
			$services->get( 'Translate:TranslatablePageParser' ),
			$services->get( 'Translate:MessageGroupMetadata' )
		);
	},

	'Translate:TranslatablePageView' => static function ( MediaWikiServices $services ): TranslatablePageView {
		return new TranslatablePageView(
			$services->getConnectionProvider(),
			$services->get( 'Translate:TranslatablePageStateStore' ),
			new ServiceOptions(
				TranslatablePageView::SERVICE_OPTIONS,
				$services->getMainConfig()
			)
		);
	},

	'Translate:TranslateSandbox' => static function ( MediaWikiServices $services ): TranslateSandbox
	{
		return new TranslateSandbox(
			$services->getUserFactory(),
			$services->getConnectionProvider(),
			$services->getPermissionManager(),
			$services->getAuthManager(),
			$services->getUserGroupManager(),
			$services->getActorStore(),
			$services->getUserOptionsManager(),
			$services->getJobQueueGroup(),
			$services->get( 'Translate:HookRunner' ),
			new ServiceOptions(
				TranslateSandbox::CONSTRUCTOR_OPTIONS,
				$services->getMainConfig()
			)
		);
	},

	'Translate:TranslationStashReader' => static function ( MediaWikiServices $services ): TranslationStashReader
	{
		return new TranslationStashStorage( $services->getConnectionProvider()->getPrimaryDatabase() );
	},

	'Translate:TranslationStatsDataProvider' => static function (
		MediaWikiServices $services
	): TranslationStatsDataProvider {
		return new TranslationStatsDataProvider(
			new ServiceOptions(
				TranslationStatsDataProvider::CONSTRUCTOR_OPTIONS,
				$services->getMainConfig()
			),
			$services->getObjectFactory(),
			$services->getConnectionProvider()
		);
	},

	'Translate:TranslationUnitStoreFactory' => static function (
		MediaWikiServices $services
	): TranslationUnitStoreFactory {
		return new TranslationUnitStoreFactory( $services->getDBLoadBalancer() );
	},

	'Translate:TranslatorActivity' => static function ( MediaWikiServices $services ): TranslatorActivity {
		$query = new TranslatorActivityQuery(
			$services->getMainConfig(),
			$services->getDBLoadBalancer()
		);

		return new TranslatorActivity(
			$services->getMainObjectStash(),
			$query,
			$services->getJobQueueGroup()
		);
	},

	'Translate:TtmServerFactory' => static function ( MediaWikiServices $services ): TtmServerFactory {
		$config = $services->getMainConfig();

		$default = $config->get( 'TranslateTranslationDefaultService' );
		if ( $default === false ) {
			$default = null;
		}

		return new TtmServerFactory( $config->get( 'TranslateTranslationServices' ), $default );
	},

	'Translate:WorkflowStatesMessageGroupLoader' => static function (
		MediaWikiServices $services
	): WorkflowStatesMessageGroupLoader {
		return new WorkflowStatesMessageGroupLoader(
			new ServiceOptions(
				WorkflowStatesMessageGroupLoader::CONSTRUCTOR_OPTIONS,
				$services->getMainConfig()
			),
		);
	},
];
