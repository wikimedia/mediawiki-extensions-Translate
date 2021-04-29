<?php
/**
 * This file contains a class to interact and store Translatable wiki pages.
 *
 * @file
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 */

use MediaWiki\MediaWikiServices;
use Wikimedia\Rdbms\IDatabase;

/**
 * Handles DB operations for Translatable pages, and the related cache.
 * @since 2019.05
 */
class TranslatablePageMessageGroupStore extends MessageGroupLoader
	implements CachedMessageGroupLoader
{

	private const CACHE_KEY = 'wikipage';
	private const CACHE_VERSION = 2;

	/** @var Wikimedia\Rdbms\IDatabase */
	protected $db;
	/** @var MessageGroupWANCache */
	protected $cache;
	/**
	 * List of groups
	 * @var array|null
	 */
	protected $groups;

	public function __construct( IDatabase $db, MessageGroupWANCache $cache ) {
		$this->db = $db;
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
			/** @var DependencyWrapper $wrapper */
			$wrapper = $this->cache->getValue();
			$this->groups = $this->initGroupsFromTitle( $wrapper->getValue() );
		}

		return $this->groups;
	}

	/**
	 * Clear and refill the cache with the latest values
	 */
	public function recache() {
		$this->clearProcessCache();
		$this->cache->touchKey();

		/** @var DependencyWrapper $wrapper */
		$wrapper = $this->cache->getValue( 'recache' );
		$this->groups = $this->initGroupsFromTitle( $wrapper->getValue() );
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
		$options = [ 'GROUP BY' => 'rt_page,page_id,page_namespace,page_title' ];
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
