<?php
/**
 * This file contains a class to load file based message groups and handle
 * the related cache.
 *
 * @file
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 */

class FileBasedMessageGroupLoader extends MessageGroupLoader
	implements CachedMessageGroupLoader {

	const CACHE_KEY = 'filebased';
	const CACHE_VERSION = 1;

	/**
	 * List of groups
	 * @var array?
	 */
	protected $groups;

	/**
	 * @var MessageGroupWANCache
	 */
	protected $cache;

	public function getGroups() {
		if ( $this->groups === null ) {
			global $wgAutoloadClasses;
			$this->groups = [];

			/** @var DependencyWrapper $cacheData */
			$wrapper = $this->cache->getValue();
			$cacheData = $wrapper->getValue();
			$autoload = $cacheData['autoload'];
			$groupConfs = $cacheData['groups'];

			foreach ( $groupConfs as $id => $conf ) {
				$this->groups[$id] = MessageGroupBase::factory( $conf );
			}

			self::appendAutoloader( $autoload, $wgAutoloadClasses );
		}

		return $this->groups;
	}

	/**
	 * Hook: TranslateInitGroupLoaders
	 *
	 * @param array &$groupLoader
	 * @return void
	 */
	public static function registerLoader( array &$groupLoader ) {
		$groupLoader[] = new self();
	}

	public function setCache( MessageGroupWANCache $cache ) {
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

	public function getCacheData() {
		global $wgTranslateGroupFiles;

		$autoload = $groups = $deps = $value = [];
		$deps[] = new GlobalDependency( 'wgTranslateGroupFiles' );

		// TODO: See if we can use DI here.
		$parser = new MessageGroupConfigurationParser();
		foreach ( $wgTranslateGroupFiles as $configFile ) {
			$deps[] = new FileDependency( realpath( $configFile ) );

			$yaml = file_get_contents( $configFile );
			$fgroups = $parser->getHopefullyValidConfigurations(
				$yaml,
				function ( $index, $config, $errmsg ) use ( $configFile ) {
					trigger_error( "Document $index in $configFile is invalid: $errmsg", E_USER_WARNING );
				}
			);

			foreach ( $fgroups as $id => $conf ) {
				if ( !empty( $conf['AUTOLOAD'] ) && is_array( $conf['AUTOLOAD'] ) ) {
					$dir = dirname( $configFile );
					$additions = array_map( function ( $file ) use ( $dir ) {
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

	public function recache() {
		$this->clearProcessCache();
		$this->cache->touchKey();

		$this->cache->getValue( 'recache' );
		$this->getGroups();
	}

	public function clearCache() {
		$this->clearProcessCache();
		$this->cache->delete();
	}

	protected function clearProcessCache() {
		$this->groups = null;
	}
}
