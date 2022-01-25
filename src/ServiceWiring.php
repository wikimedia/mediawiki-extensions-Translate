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
use MediaWiki\Extension\Translate\PageTranslation\TranslatablePageMover;
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

	'Translate:EntitySearch' => static function ( MediaWikiServices $services ): EntitySearch {
		// BC for MW <= 1.36
		if ( method_exists( $services, 'getCollationFactory' ) ) {
			$collation = $services->getCollationFactory()->makeCollation( 'uca-default-u-kn' );
		} else {
			$collation = Collation::factory( 'uca-default-u-kn' );
		}

		return new EntitySearch(
			$services->getMainWANObjectCache(),
			$collation,
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
			TranslateUtils::getJobQueueGroup(),
			LoggerFactory::getInstance( 'Translate.GroupSynchronization' ),
			MessageIndex::singleton()
		);
	},

	'Translate:GroupSynchronizationCache' => static function (
		MediaWikiServices $services
	): GroupSynchronizationCache {
		return new GroupSynchronizationCache( $services->get( 'Translate:PersistentCache' ) );
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

	'Translate:TranslatablePageMover' => static function ( MediaWikiServices $services ): TranslatablePageMover
	{
		return new TranslatablePageMover(
			$services->getMovePageFactory(),
			TranslateUtils::getJobQueueGroup(),
			$services->getLinkBatchFactory(),
			$services->getMainConfig()->get( 'TranslatePageMoveLimit' )
		);
	},

	'Translate:TranslatablePageParser' => static function ( MediaWikiServices $services ): TranslatablePageParser
	{
		return new TranslatablePageParser(
			$services->get( 'Translate:ParsingPlaceholderFactory' )
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
			TranslateUtils::getJobQueueGroup()
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
