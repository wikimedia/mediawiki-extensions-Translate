<?php
/**
 * List of services in this extension with construction instructions.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */

use MediaWiki\Extensions\Translate\Statistics\TranslatorActivity;
use MediaWiki\Extensions\Translate\Statistics\TranslatorActivityQuery;
use MediaWiki\Extensions\Translate\Synchronization\GroupSynchronizationCache;
use MediaWiki\Extensions\Translate\Utilities\ParsingPlaceholderFactory;
use MediaWiki\MediaWikiServices;

return [
	'Translate:GroupSynchronizationCache' => function (): GroupSynchronizationCache {
		return new GroupSynchronizationCache( ObjectCache::getInstance( CACHE_DB ) );
	},

	'Translate:ParsingPlaceholderFactory' => function (): ParsingPlaceholderFactory {
		return new ParsingPlaceholderFactory();
	},

	'Translate:TranslatorActivity' => function ( MediaWikiServices $services ): TranslatorActivity {
		$query = new TranslatorActivityQuery(
			$services->getMainConfig(),
			$services->getDBLoadBalancer()
		);

		$languageValidator = function ( string $language ): bool {
			return Language::isKnownLanguageTag( $language );
		};

		return new TranslatorActivity(
			$services->getMainObjectStash(),
			$query,
			JobQueueGroup::singleton(),
			$languageValidator
		);
	},

];
