<?php
/**
 * This file contains a class to interact and store Translatable wiki pages.
 *
 * @file
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 */

class TranslatablePageMessageGroupStore extends MessageGroupLoader
		implements DbMessageGroupLoader, CachedMessageGroupLoader
{

	const CACHE_KEY = 'wikipage';
	const CACHE_VERSION = 1;

	/**
	 * @var Wikimedia\Rdbms\IDatabase
	 */
	protected $db;

	/**
	 * @var MessageGroupWANCache
	 */
	protected $cache;

	/**
	 * List of groups
	 * @var array?
	 */
	protected $groups;

	public function setDatabase( Wikimedia\Rdbms\IDatabase $db ) {
		$this->db = $db;
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

	/**
	 * Return the WikiPageMessageGroups
	 * If local variable is set, use that otherwise fetch from the cache.
	 *
	 * @return WikiPageMessageGroup[]
	 */
	public function getGroups() {
		if ( $this->groups === null ) {
			/** @var DependencyWrapper $cacheData */
			$cacheData = $this->cache->getValue();
			$this->groups = $this->initGroupsFromTitle( $cacheData->getValue() );
		}

		return $this->groups;
	}

	/**
	 * Clear and refill the cache with the latest values
	 *
	 * @return void
	 */
	public function recache() {
		$this->clearProcessCache();
		$this->cache->touchKey();

		$cacheData = $this->cache->getValue( 'recache' );
		$this->groups = $this->initGroupsFromTitle( $cacheData->getValue() );
	}

	/**
	 * Clear values from the cache
	 *
	 * @return void
	 */
	public function clearCache() {
		$this->clearProcessCache();
		$this->cache->delete();
	}

	/**
	 * Clears the process cache, mainly the cached groups property.
	 *
	 * @return void
	 */
	protected function clearProcessCache() {
		$this->groups = null;
	}

	/**
	 * Get the data that needs to be stored in the cache.
	 *
	 * @return DependencyWrapper
	 */
	public function getCacheData() {
		global $wgEnablePageTranslation;

		$groupTitles = $deps = [];
		$deps = new GlobalDependency( 'wgEnablePageTranslation' );

		if ( $wgEnablePageTranslation ) {
			$groupTitles = $this->getTranslatablePageTitles();
		}

		$wrapper = new DependencyWrapper( $groupTitles, $deps );
		$wrapper->initialiseDeps();
		return $wrapper;
	}

	/**
	 * Check if the values in the cache have expired
	 *
	 * @param DependencyWrapper $cacheData
	 * @return bool True if expired, false otherwise.
	 */
	public function isExpired( DependencyWrapper $cacheData ) {
		if ( $cacheData->isExpired() ) {
			return true;
		}
		return false;
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

	/**
	 * Fetch page titles marked for translation from the database to store in the
	 * cache
	 *
	 * @return string[]
	 */
	protected function getTranslatablePageTitles() {
		$groupTitles = [];
		$tables = [ 'page', 'revtag' ];
		$vars = [ 'page_id', 'page_namespace', 'page_title' ];
		$conds = [ 'page_id=rt_page', 'rt_type' => RevTag::getType( 'tp:mark' ) ];
		$options = [ 'GROUP BY' => 'rt_page' ];
		$res = $this->db->select( $tables, $vars, $conds, __METHOD__, $options );

		foreach ( $res as $r ) {
			$title = Title::newFromRow( $r );
			$groupTitles[] = $title->getPrefixedText();
		}

		return $groupTitles;
	}

	/**
	 * Convert page titles to WikiPageMessageGroup objects.
	 * Called after the values have been retrieved from the cache.
	 *
	 * @param string[] $titles
	 * @return WikiPageMessageGroup[]
	 */
	protected function initGroupsFromTitle( $titles ) {
		$groups = [];
		foreach ( $titles as $title ) {
			$title = Title::newFromText( $title );
			$id = TranslatablePage::getMessageGroupIdFromTitle( $title );
			$groups[$id] = new WikiPageMessageGroup( $id, $title );
		}

		return $groups;
	}
}
