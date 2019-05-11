<?php
/**
 * This file contains a class to load aggregate message groups and handle
 * the related cache.
 *
 * @file
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 */

class AggregateMessageGroupLoader extends MessageGroupLoader
	implements CachedMessageGroupLoader, DbMessageGroupLoader {

	const CACHE_KEY = 'aggregate';
	const CACHE_VERSION = 1;

	/**
	 * @var MessageGroupWANCache
	 */
	protected $cache;

	/**
	 * @var \Wikimedia\Rdbms\IDatabase
	 */
	protected $db;

	/**
	 * List of groups
	 * @var array|null
	 */
	protected $groups;

	/**
	 * Fetches the AggregateMessageGroups
	 *
	 * @return AggregateMessageGroup[]
	 */
	public function getGroups() {
		if ( $this->groups === null ) {
			$cacheData = $this->cache->getValue();
			$this->groups = $this->initGroupsFromConf( $cacheData );
		}

		return $this->groups;
	}

	/**
	 * Hook: TranslateInitGroupLoaders
	 *
	 * @param array &$groupLoader
	 * @return AggregateMessageGroupLoader
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
			]
		);
	}

	public function setDatabase( \Wikimedia\Rdbms\IDatabase $db ) {
		$this->db = $db;
	}

	/**
	 * Get all the aggregate messages groups defined in translate_metadata table
	 * and return their configuration
	 * @return array
	 */
	public function getCacheData() {
		$dbr = TranslateUtils::getSafeReadDB();
		$tables = [ 'translate_metadata' ];
		$field = 'tmd_group';
		$conds = [ 'tmd_key' => 'subgroups' ];
		$groupIds = $dbr->selectFieldValues( $tables, $field, $conds, __METHOD__ );
		TranslateMetadata::preloadGroups( $groupIds );

		$groups = [];
		foreach ( $groupIds as $id ) {
			$conf = [];
			$conf['BASIC'] = [
				'id' => $id,
				'label' => TranslateMetadata::get( $id, 'name' ),
				'description' => TranslateMetadata::get( $id, 'description' ),
				'meta' => 1,
				'class' => 'AggregateMessageGroup',
				'namespace' => NS_TRANSLATIONS,
			];
			$conf['GROUPS'] = TranslateMetadata::getSubgroups( $id );
			$groups[$id] = $conf;
		}

		return $groups;
	}

	/**
	 * Loads AggregateMessageGroup from the database
	 *
	 * @return AggregateMessageGroup[]
	 */
	public function loadAggregateGroups() {
		// get the data from the database everytime
		$groupConf = $this->getCacheData();
		return $this->initGroupsFromConf( $groupConf );
	}

	protected function initGroupsFromConf( $groups ) {
		foreach ( $groups as $id => $conf ) {
			$groups[ $id ] = MessageGroupBase::factory( $conf );
		}

		return $groups;
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
		$this->groups = $this->initGroupsFromConf( $cacheData );
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
}
