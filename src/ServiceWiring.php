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
use MediaWiki\Extension\Translate\MessageBundleTranslation\MessageBundleStore;
use MediaWiki\Extension\Translate\MessageGroupProcessing\CsvTranslationImporter;
use MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroupReview;
use MediaWiki\Extension\Translate\MessageGroupProcessing\RevTagStore;
use MediaWiki\Extension\Translate\MessageGroupProcessing\SubpageListBuilder;
use MediaWiki\Extension\Translate\MessageGroupProcessing\TranslatableBundleFactory;
use MediaWiki\Extension\Translate\MessageGroupProcessing\TranslatablePageStore;
use MediaWiki\Extension\Translate\PageTranslation\TranslatableBundleMover;
use MediaWiki\Extension\Translate\PageTranslation\TranslatablePageParser;
use MediaWiki\Extension\Translate\PageTranslation\TranslationUnitStoreFactory;
use MediaWiki\Extension\Translate\Statistics\ProgressStatsTableFactory;
use MediaWiki\Extension\Translate\Statistics\TranslationStatsDataProvider;
use MediaWiki\Extension\Translate\Statistics\TranslatorActivity;
use MediaWiki\Extension\Translate\Statistics\TranslatorActivityQuery;
use MediaWiki\Extension\Translate\Synchronization\ExternalMessageSourceStateImporter;
use MediaWiki\Extension\Translate\Synchronization\GroupSynchronizationCache;
use MediaWiki\Extension\Translate\TranslatorInterface\EntitySearch;
use MediaWiki\Extension\Translate\TranslatorSandbox\TranslationStashReader;
use MediaWiki\Extension\Translate\TranslatorSandbox\TranslationStashStorage;
use MediaWiki\Extension\Translate\TtmServer\TtmServerFactory;
use MediaWiki\Extension\Translate\Utilities\ConfigHelper;
use MediaWiki\Extension\Translate\Utilities\ParsingPlaceholderFactory;
use MediaWiki\Logger\LoggerFactory;
use MediaWiki\MediaWikiServices;

/** @phpcs-require-sorted-array */
return [
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

	'Translate:ExternalMessageSourceStateImporter' => static function (
		MediaWikiServices $services
	): ExternalMessageSourceStateImporter {
		return new ExternalMessageSourceStateImporter(
			$services->getMainConfig(),
			$services->get( 'Translate:GroupSynchronizationCache' ),
			$services->getJobQueueGroup(),
			LoggerFactory::getInstance( 'Translate.GroupSynchronization' ),
			MessageIndex::singleton()
		);
	},

	'Translate:GroupSynchronizationCache' => static function (
		MediaWikiServices $services
	): GroupSynchronizationCache {
		return new GroupSynchronizationCache( $services->get( 'Translate:PersistentCache' ) );
	},

	'Translate:MessageBundleStore' => static function ( MediaWikiServices $services ): MessageBundleStore {
		return new MessageBundleStore(
			new RevTagStore(),
			$services->getJobQueueGroup(),
			$services->getLanguageNameUtils(),
			$services->get( 'Translate:MessageIndex' )
		);
	},

	'Translate:MessageGroupReview' => static function ( MediaWikiServices $services ): MessageGroupReview {
		return new MessageGroupReview(
			$services->getDBLoadBalancer(),
			$services->getHookContainer()
		);
	},

	'Translate:MessageIndex' => static function ( MediaWikiServices $services ): MessageIndex {
		$params = $services->getMainConfig()->get( 'TranslateMessageIndex' );
		if ( is_string( $params ) ) {
			$params = (array)$params;
		}

		$class = array_shift( $params );
		// @phan-suppress-next-line PhanTypeExpectedObjectOrClassName
		return new $class( $params );
	},

	'Translate:ParsingPlaceholderFactory' => static function (): ParsingPlaceholderFactory {
		return new ParsingPlaceholderFactory();
	},

	'Translate:PersistentCache' => static function ( MediaWikiServices $services ): PersistentCache {
		return new PersistentDatabaseCache(
			$services->getDBLoadBalancer(),
			$services->getJsonCodec()
		 );
	},

	'Translate:ProgressStatsTableFactory' => static function ( MediaWikiServices $services ): ProgressStatsTableFactory
	{
		return new ProgressStatsTableFactory(
			$services->getLinkRenderer(),
			$services->get( 'Translate:ConfigHelper' )
		);
	},

	'Translate:SubpageListBuilder' => static function ( MediaWikiServices $services ): SubpageListBuilder
	{
		return new SubpageListBuilder(
			$services->get( 'Translate:TranslatableBundleFactory' ),
			$services->getLinkBatchFactory()
		);
	},

	'Translate:TranslatableBundleFactory' => static function ( MediaWikiServices $services ): TranslatableBundleFactory
	{
		return new TranslatableBundleFactory(
			$services->get( 'Translate:TranslatablePageStore' ),
			$services->get( 'Translate:MessageBundleStore' )
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
			$services->getMainConfig()->get( 'TranslatePageMoveLimit' )
		);
	},

	'Translate:TranslatablePageParser' => static function ( MediaWikiServices $services ): TranslatablePageParser
	{
		return new TranslatablePageParser(
			$services->get( 'Translate:ParsingPlaceholderFactory' )
		);
	},

	'Translate:TranslatablePageStore' => static function ( MediaWikiServices $services ): TranslatablePageStore
	{
		return new TranslatablePageStore(
			$services->get( 'Translate:MessageIndex' ),
			$services->getJobQueueGroup(),
			new RevTagStore(),
			$services->getDBLoadBalancer()
		);
	},

	'Translate:TranslationStashReader' => static function ( MediaWikiServices $services ): TranslationStashReader
	{
		$db = $services->getDBLoadBalancer()->getConnectionRef( DB_REPLICA );
		return new TranslationStashStorage( $db );
	},

	'Translate:TranslationStatsDataProvider' => static function (
		MediaWikiServices $services
	): TranslationStatsDataProvider {
		return new TranslationStatsDataProvider(
			new ServiceOptions(
				TranslationStatsDataProvider::CONSTRUCTOR_OPTIONS,
				$services->getMainConfig()
			),
			$services->getObjectFactory()
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
	}
];
