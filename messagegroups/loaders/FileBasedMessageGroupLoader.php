<?php
/**
 * This file contains a class to load file based message groups and handle
 * the related cache.
 *
 * @file
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 */

use MediaWiki\MediaWikiServices;

/**
 * Loads FileBasedMessageGroup, and handles the related cache.
 * @since 2019.05
 */
class FileBasedMessageGroupLoader extends MessageGroupLoader
	implements CachedMessageGroupLoader {

	private const CACHE_KEY = 'filebased';
	private const CACHE_VERSION = 2;

	/**
	 * List of groups
	 * @var array|null
	 */
	protected $groups;
	/** @var MessageGroupWANCache */
	protected $cache;

	public function __construct( MessageGroupWANCache $cache ) {
		$this->cache = $cache;
		$this->cache->configure(
			[
				'key' => self::CACHE_KEY,
				'version' => self::CACHE_VERSION,
				'regenerator' => [ $this, 'getCacheData' ],
				'touchedCallback' => [ $this, 'isExpired' ]
			]
		);
	}

	/**
	 * Fetches FileBasedMessageGroups
	 *
	 * @return MessageGroup[] Key is the MessageGroup Id and value is the corresponding
	 * FileBasedMessageGroup
	 */
	public function getGroups() {
		if ( $this->groups === null ) {
			/** @var DependencyWrapper $wrapper */
			$wrapper = $this->cache->getValue();
			$cacheData = $wrapper->getValue();
			$this->initFromCacheValue( $cacheData );
		}

		return $this->groups;
	}

	/**
	 * Hook: TranslateInitGroupLoaders
	 *
	 * @param array &$groupLoader
	 * @param array $deps Dependencies
	 */
	public static function registerLoader( array &$groupLoader, array $deps ) {
		$groupLoader[] = self::getInstance(
			$deps['cache']
		);
	}

	/**
	 * Return an instance of this class using the parameters, if passed,
	 * else initialize the necessary dependencies and return an instance.
	 *
	 * @param WANObjectCache|null $cache
	 * @return self
	 */
	public static function getInstance( WANObjectCache $cache = null ) {
		return new self(
			new MessageGroupWANCache(
				$cache ?? MediaWikiServices::getInstance()->getMainWANObjectCache()
			)
		);
	}

	/**
	 * Generates data to be stored in the cache.
	 *
	 * @return DependencyWrapper
	 */
	public function getCacheData() {
		global $wgTranslateGroupFiles;

		$autoload = $groups = $deps = $value = [];
		$deps[] = new GlobalDependency( 'wgTranslateGroupFiles' );

		// TODO: See if DI can be used here
		$parser = new MessageGroupConfigurationParser();
		foreach ( $wgTranslateGroupFiles as $configFile ) {
			$deps[] = new FileDependency( realpath( $configFile ) );

			$yaml = file_get_contents( $configFile );
			$fgroups = $parser->getHopefullyValidConfigurations(
				$yaml,
				static function ( $index, $config, $errmsg ) use ( $configFile ) {
					trigger_error( "Document $index in $configFile is invalid: $errmsg", E_USER_WARNING );
				}
			);

			foreach ( $fgroups as $id => $conf ) {
				if ( !empty( $conf['AUTOLOAD'] ) && is_array( $conf['AUTOLOAD'] ) ) {
					$dir = dirname( $configFile );
					$additions = array_map( static function ( $file ) use ( $dir ) {
						return "$dir/$file";
					}, $conf['AUTOLOAD'] );
					self::appendAutoloader( $additions, $autoload );
				}

				$groups[$id] = $conf;
			}
		}
		$value['groups'] = $groups;
		$value['autoload'] = $autoload;

		$wrapper = new DependencyWrapper( $value, $deps );
		$wrapper->initialiseDeps();
		return $wrapper;
	}

	/**
	 * Clear and refill the cache with the latest values
	 */
	public function recache() {
		$this->clearProcessCache();
		$this->cache->touchKey();

		/** @var DependencyWrapper $wrapper */
		$wrapper = $this->cache->getValue( 'recache' );
		$cacheData = $wrapper->getValue();
		$this->initFromCacheValue( $cacheData );
	}

	/**
	 * Clear values from the cache
	 */
	public function clearCache() {
		$this->clearProcessCache();
		$this->cache->delete();
	}

	/**
	 * Clears the process cache, mainly the cached groups property.
	 */
	protected function clearProcessCache() {
		$this->groups = null;
	}

	/**
	 * Initialize groups and autoload classes from the cache value.
	 *
	 * @param array $cacheData
	 */
	protected function initFromCacheValue( array $cacheData ) {
		global $wgAutoloadClasses;
		$this->groups = [];

		$autoload = $cacheData['autoload'];
		$groupConfs = $cacheData['groups'];

		foreach ( $groupConfs as $id => $conf ) {
			$this->groups[$id] = MessageGroupBase::factory( $conf );
		}

		self::appendAutoloader( $autoload, $wgAutoloadClasses );
	}

	/**
	 * Safely merges first array to second array, throwing warning on duplicates and removing
	 * duplicates from the first array.
	 * @param array $additions Things to append
	 * @param array &$to Where to append
	 */
	private static function appendAutoloader( array $additions, array &$to ) {
		foreach ( $additions as $class => $file ) {
			if ( isset( $to[$class] ) && $to[$class] !== $file ) {
				$msg = "Autoload conflict for $class: {$to[$class]} !== $file";
				trigger_error( $msg, E_USER_WARNING );
				continue;
			}

			$to[$class] = $file;
		}
	}
}
