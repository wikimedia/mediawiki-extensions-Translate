<?php
/**
 * This file contains a class to load aggregate message groups and handle
 * the related cache.
 *
 * @file
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 */

use MediaWiki\MediaWikiServices;
use Wikimedia\Rdbms\IDatabase;

/**
 * Loads AggregateMessageGroup, and handles the related cache.
 * @since 2019.05
 */
class AggregateMessageGroupLoader extends MessageGroupLoader
	implements CachedMessageGroupLoader {

	private const CACHE_KEY = 'aggregate';
	private const CACHE_VERSION = 2;

	/** @var MessageGroupWANCache */
	protected $cache;
	/** @var IDatabase */
	protected $db;
	/** @var AggregateMessageGroup[]|null */
	protected $groups;

	public function __construct( IDatabase $db, MessageGroupWANCache $cache ) {
		$this->db = $db;
		$this->cache = $cache;
		$this->cache->configure(
			[
				'key' => self::CACHE_KEY,
				'version' => self::CACHE_VERSION,
				'regenerator' => [ $this, 'getCacheData' ],
			]
		);
	}

	/**
	 * Fetches the AggregateMessageGroups
	 *
	 * @return AggregateMessageGroup[] Key is the MessageGroup ID and value is the corresponding
	 * AggregateMessageGroup
	 */
	public function getGroups() {
		if ( $this->groups === null ) {
			$this->groups = $this->initGroupsFromConf( $this->cache->getValue() );
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
			$deps['database'],
			$deps['cache']
		);
	}

	/**
	 * Return an instance of this class using the parameters, if passed,
	 * else initialize the necessary dependencies and return an instance.
	 *
	 * @param IDatabase|null $db
	 * @param WANObjectCache|null $cache
	 * @return self
	 */
	public static function getInstance( IDatabase $db = null, WANObjectCache $cache = null ) {
		return new self(
			$db ?? TranslateUtils::getSafeReadDB(),
			new MessageGroupWANCache(
				$cache ?? MediaWikiServices::getInstance()->getMainWANObjectCache()
			)
		);
	}

	/**
	 * Get all the aggregate messages groups defined in translate_metadata table
	 * and return their configuration
	 * @return array[]
	 */
	public function getCacheData(): array {
		$tables = [ 'translate_metadata' ];
		$field = 'tmd_group';
		$conds = [ 'tmd_key' => 'subgroups' ];
		$groupIds = $this->db->selectFieldValues( $tables, $field, $conds, __METHOD__ );
		TranslateMetadata::preloadGroups( $groupIds, __METHOD__ );

		$groups = [];
		foreach ( $groupIds as $id ) {
			$conf = [];
			$conf['BASIC'] = [
				'id' => $id,
				'label' => TranslateMetadata::get( $id, 'name' ),
				'description' => TranslateMetadata::get( $id, 'description' ),
				'meta' => 1,
				'class' => AggregateMessageGroup::class,
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
	public function loadAggregateGroups(): array {
		// get the data from the database everytime
		// @phan-suppress-next-line PhanTypeMismatchReturn type is guaranteed via getCacheData above
		return $this->initGroupsFromConf( $this->getCacheData() );
	}

	/**
	 * @param array[] $groups
	 * @return MessageGroup[]
	 */
	protected function initGroupsFromConf( array $groups ): array {
		return array_map( [ MessageGroupBase::class, 'factory' ], $groups );
	}

	/**
	 * Clear and refill the cache with the latest values
	 */
	public function recache() {
		$this->clearProcessCache();
		$this->cache->touchKey();

		$cacheData = $this->cache->getValue( 'recache' );
		$this->groups = $this->initGroupsFromConf( $cacheData );
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
}
