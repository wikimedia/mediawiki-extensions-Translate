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
use MediaWiki\Extension\Translate\PageTranslation\TranslatablePageParser;
use MediaWiki\Extension\Translate\Statistics\TranslationStatsDataProvider;
use MediaWiki\Extension\Translate\Statistics\TranslatorActivity;
use MediaWiki\Extension\Translate\Statistics\TranslatorActivityQuery;
use MediaWiki\Extension\Translate\Synchronization\GroupSynchronizationCache;
use MediaWiki\Extension\Translate\TranslatorSandbox\TranslationStashReader;
use MediaWiki\Extension\Translate\TranslatorSandbox\TranslationStashStorage;
use MediaWiki\Extension\Translate\TtmServer\TtmServerFactory;
use MediaWiki\Extension\Translate\Utilities\Json\JsonCodec;
use MediaWiki\Extension\Translate\Utilities\ParsingPlaceholderFactory;
use MediaWiki\MediaWikiServices;

/** @phpcs-require-sorted-array */
return [
	'Translate:GroupSynchronizationCache' => function (
		MediaWikiServices $services
	): GroupSynchronizationCache {
		return new GroupSynchronizationCache( $services->get( 'Translate:PersistentCache' ) );
	},

	'Translate:JsonCodec' => function (): JsonCodec {
		return new JsonCodec();
	},

	'Translate:ParsingPlaceholderFactory' => function (): ParsingPlaceholderFactory {
		return new ParsingPlaceholderFactory();
	},

	'Translate:PersistentCache' => function ( MediaWikiServices $services ): PersistentCache {
		return new PersistentDatabaseCache(
			$services->getDBLoadBalancer(),
			// TODO: Since we have a similar interface, see if we can load the JsonCodec
			// from the core here if available
			$services->get( 'Translate:JsonCodec' )
		 );
	},

	'Translate:TranslatablePageParser' => function ( MediaWikiServices $services ): TranslatablePageParser
	{
		return new TranslatablePageParser(
			$services->get( 'Translate:ParsingPlaceholderFactory' )
		);
	},

	'Translate:TranslationStashReader' => function ( MediaWikiServices $services ): TranslationStashReader
	{
		$db = $services->getDBLoadBalancer()->getConnectionRef( DB_REPLICA );
		return new TranslationStashStorage( $db );
	},

	'Translate:TranslationStatsDataProvider' => function ( MediaWikiServices $services ): TranslationStatsDataProvider
	{
		return new TranslationStatsDataProvider(
			new ServiceOptions(
				TranslationStatsDataProvider::CONSTRUCTOR_OPTIONS,
				$services->getMainConfig()
			),
			$services->getObjectFactory()
		);
	},

	'Translate:TranslatorActivity' => function ( MediaWikiServices $services ): TranslatorActivity {
		$query = new TranslatorActivityQuery(
			$services->getMainConfig(),
			$services->getDBLoadBalancer()
		);

		return new TranslatorActivity(
			$services->getMainObjectStash(),
			$query,
			JobQueueGroup::singleton(),
			$services->getLanguageNameUtils()
		);
	},

	'Translate:TtmServerFactory' => function ( MediaWikiServices $services ): TtmServerFactory {
		$config = $services->getMainConfig();

		$default = $config->get( 'TranslateTranslationDefaultService' );
		if ( $default === false ) {
			$default = null;
		}

		return new TtmServerFactory( $config->get( 'TranslateTranslationServices' ), $default );
	}
];
