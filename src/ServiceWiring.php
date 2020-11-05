<?php
/**
 * List of services in this extension with construction instructions.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */

use MediaWiki\Config\ServiceOptions;
use MediaWiki\Extensions\Translate\PageTranslation\TranslatablePageParser;
use MediaWiki\Extensions\Translate\Statistics\TranslationStatsDataProvider;
use MediaWiki\Extensions\Translate\Statistics\TranslatorActivity;
use MediaWiki\Extensions\Translate\Statistics\TranslatorActivityQuery;
use MediaWiki\Extensions\Translate\Synchronization\GroupSynchronizationCache;
use MediaWiki\Extensions\Translate\TranslatorSandbox\TranslationStashReader;
use MediaWiki\Extensions\Translate\TranslatorSandbox\TranslationStashStorage;
use MediaWiki\Extensions\Translate\Utilities\ParsingPlaceholderFactory;
use MediaWiki\MediaWikiServices;

/** @phpcs-require-sorted-array */
return [
	'Translate:GroupSynchronizationCache' => function (): GroupSynchronizationCache {
		return new GroupSynchronizationCache( ObjectCache::getInstance( CACHE_DB ) );
	},

	'Translate:ParsingPlaceholderFactory' => function (): ParsingPlaceholderFactory {
		return new ParsingPlaceholderFactory();
	},

	'Translate:TranslatablePageParser' => function ( MediaWikiServices $services )
	: TranslatablePageParser
	{
		return new TranslatablePageParser(
			$services->get( 'Translate:ParsingPlaceholderFactory' )
		);
	},

	'Translate:TranslationStashReader' => function ( MediaWikiServices $services )
	: TranslationStashReader
	{
		$db = $services->getDBLoadBalancer()->getConnectionRef( DB_REPLICA );
		return new TranslationStashStorage( $db );
	},

	'Translate:TranslationStatsDataProvider' => function ( MediaWikiServices $services )
	: TranslationStatsDataProvider
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
];
