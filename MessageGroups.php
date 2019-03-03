<?php
/**
 * This file contains a class for working with message groups.
 *
 * @file
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @copyright Copyright © 2008-2013, Niklas Laxström, Siebrand Mazeland
 * @license GPL-2.0-or-later
 */
use \MediaWiki\MediaWikiServices;

/**
 * Factory class for accessing message groups individually by id or
 * all of them as an list.
 * @todo Clean up the mixed static/member method interface.
 */
class MessageGroups {
	/**
	 * @var string[]|null Cache for message group priorities
	 */
	protected static $prioritycache;

	/**
	 * @var MessageGroup[]|null Map of (group ID => MessageGroup)
	 */
	protected $groups;

	/**
	 * @var WANObjectCache|null
	 */
	protected $cache;

	/**
	 * Tracks the current cache verison. Update this when there are incompatible changes
	 * with the last version of the cache to force a new key to be used. The older cache
	 * will automatically expire and be cleared off.
	 * @var int
	 */
	const CACHE_VERSION = 2;

	/**
	 * Initialises the list of groups
	 */
	protected function init() {
		if ( is_array( $this->groups ) ) {
			return; // groups already initialized
		}

		$value = $this->getCachedGroupDefinitions();
		$groups = $value['cc'];

		$this->initGroupsFromDefinitions( $groups );
	}

	/**
	 * @param bool|string $recache Either "recache" or false
	 * @return array
	 */
	protected function getCachedGroupDefinitions( $recache = false ) {
		global $wgAutoloadClasses, $wgVersion;

		$regenerator = function () {
			global $wgAutoloadClasses;

			$groups = $deps = $autoload = [];
			// This constructs the list of all groups from multiple different sources.
			// When possible, a cache dependency is created to automatically recreate
			// the cache when configuration changes.
			Hooks::run( 'TranslatePostInitGroups', [ &$groups, &$deps, &$autoload ] );
			// Register autoloaders for this request, both values modified by reference
			self::appendAutoloader( $autoload, $wgAutoloadClasses );

			$value = [
				'ts' => wfTimestamp( TS_MW ),
				'cc' => $groups,
				'autoload' => $autoload
			];
			$wrapper = new DependencyWrapper( $value, $deps );
			$wrapper->initialiseDeps();

			return $wrapper; // save the new value to cache
		};

		$cache = $this->getCache();
		/** @var DependencyWrapper $wrapper */
		$wrapper = $cache->getWithSetCallback(
			self::getCacheKey(),
			$cache::TTL_DAY,
			$regenerator,
			[
				'lockTSE' => 30, // avoid stampedes (mutex)
				'checkKeys' => [ self::getCacheKey() ],
				'touchedCallback' => function ( $value ) {
					return ( $value instanceof DependencyWrapper && $value->isExpired() )
						? time() // treat value as if it just expired (for "lockTSE")
						: null;
				},
				'minAsOf' => $recache ? INF : $cache::MIN_TIMESTAMP_NONE, // "miss" on recache
			]
		);

		// B/C for "touchedCallback" param not existing
		if ( version_compare( $wgVersion, '1.33', '<' ) && $wrapper->isExpired() ) {
			$wrapper = $regenerator();
			$cache->set( self::getCacheKey(), $wrapper, $cache::TTL_DAY );
		}

		$value = $wrapper->getValue();
		self::appendAutoloader( $value['autoload'], $wgAutoloadClasses );

		return $value;
	}

	/**
	 * Expand process cached groups to objects
	 *
	 * @param array $groups Map of (group ID => mixed)
	 */
	protected function initGroupsFromDefinitions( $groups ) {
		foreach ( $groups as $id => $mixed ) {
			if ( !is_object( $mixed ) ) {
				$groups[$id] = call_user_func( $mixed, $id );
			}
		}

		$this->groups = $groups;
	}

	/**
	 * Immediately update the cache.
	 *
	 * @since 2015.04
	 */
	public function recache() {
		// Purge the value from all datacenters
		$cache = $this->getCache();
		$cache->touchCheckKey( self::getCacheKey() );
		// Reload the cache value and update the local datacenter
		$value = $this->getCachedGroupDefinitions( 'recache' );
		$groups = $value['cc'];

		$this->clearProcessCache();
		$this->initGroupsFromDefinitions( $groups );
	}

	/**
	 * Manually reset group cache.
	 *
	 * Use when automatic dependency tracking fails.
	 */
	public static function clearCache() {
		$self = self::singleton();

		$cache = $self->getCache();
		$cache->delete( self::getCacheKey(), 1 );

		$self->clearProcessCache();
	}

	/**
	 * Manually reset the process cache.
	 *
	 * This is helpful for long running scripts where the process cache might get stale
	 * even though the global cache is updated.
	 * @since 2016.08
	 */
	public function clearProcessCache() {
		$this->groups = null;
	}

	/**
	 * Returns a cacher object.
	 *
	 * @return WANObjectCache
	 */
	protected function getCache() {
		if ( $this->cache === null ) {
			return MediaWikiServices::getInstance()->getMainWANObjectCache();
		} else {
			return $this->cache;
		}
	}

	/**
	 * Override cache, for example during tests.
	 *
	 * @param WANObjectCache|null $cache
	 */
	public function setCache( WANObjectCache $cache = null ) {
		$this->cache = $cache;
	}

	/**
	 * Returns the cache key.
	 *
	 * @return string
	 */
	protected static function getCacheKey() {
		$self = self::singleton();
		$cache = $self->getCache();

		return $cache->makeKey( 'translate-groups', 'v' . self::CACHE_VERSION );
	}

	/**
	 * Safely merges first array to second array, throwing warning on duplicates and removing
	 * duplicates from the first array.
	 * @param array &$additions Things to append
	 * @param array &$to Where to append
	 */
	protected static function appendAutoloader( array &$additions, array &$to ) {
		foreach ( $additions as $class => $file ) {
			if ( isset( $to[$class] ) && $to[$class] !== $file ) {
				$msg = "Autoload conflict for $class: {$to[$class]} !== $file";
				trigger_error( $msg, E_USER_WARNING );
				continue;
			}

			$to[$class] = $file;
		}
	}

	/**
	 * Hook: TranslatePostInitGroups
	 * @param array &$groups
	 * @param array &$deps
	 * @param array &$autoload
	 */
	public static function getTranslatablePages( array &$groups, array &$deps, array &$autoload ) {
		global $wgEnablePageTranslation;

		$deps[] = new GlobalDependency( 'wgEnablePageTranslation' );

		if ( !$wgEnablePageTranslation ) {
			return;
		}

		$db = TranslateUtils::getSafeReadDB();

		$tables = [ 'page', 'revtag' ];
		$vars = [ 'page_id', 'page_namespace', 'page_title' ];
		$conds = [ 'page_id=rt_page', 'rt_type' => RevTag::getType( 'tp:mark' ) ];
		$options = [ 'GROUP BY' => 'rt_page' ];
		$res = $db->select( $tables, $vars, $conds, __METHOD__, $options );

		foreach ( $res as $r ) {
			$title = Title::newFromRow( $r );
			$id = TranslatablePage::getMessageGroupIdFromTitle( $title );
			$groups[$id] = new WikiPageMessageGroup( $id, $title );
		}
	}

	/**
	 * Hook: TranslatePostInitGroups
	 * @param array &$groups
	 * @param array &$deps
	 * @param array &$autoload
	 */
	public static function getConfiguredGroups( array &$groups, array &$deps, array &$autoload ) {
		global $wgTranslateGroupFiles;

		$deps[] = new GlobalDependency( 'wgTranslateGroupFiles' );

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

				$groups[$id] = MessageGroupBase::factory( $conf );
			}
		}
	}

	/**
	 * Hook: TranslatePostInitGroups
	 * @param array &$groups
	 * @param array &$deps
	 * @param array &$autoload
	 */
	public static function getWorkflowGroups( array &$groups, array &$deps, array &$autoload ) {
		global $wgTranslateWorkflowStates;

		$deps[] = new GlobalDependency( 'wgTranslateWorkflowStates' );

		if ( $wgTranslateWorkflowStates ) {
			$groups['translate-workflow-states'] = new WorkflowStatesMessageGroup();
		}
	}

	/**
	 * Hook: TranslatePostInitGroups
	 * @param array &$groups
	 * @param array &$deps
	 * @param array &$autoload
	 */
	public static function getAggregateGroups( array &$groups, array &$deps, array &$autoload ) {
		$groups += self::loadAggregateGroups();
	}

	/**
	 * Hook: TranslatePostInitGroups
	 * @param array &$groups
	 * @param array &$deps
	 * @param array &$autoload
	 */
	public static function getCCGroups( array &$groups, array &$deps, array &$autoload ) {
		global $wgTranslateCC;

		if ( $wgTranslateCC !== [] ) {
			wfDeprecated( '$wgTranslateCC' );
		}

		$deps[] = new GlobalDependency( 'wgTranslateCC' );

		$groups += $wgTranslateCC;
	}

	/**
	 * Fetch a message group by id.
	 *
	 * @param string $id Message group id.
	 * @return MessageGroup|null if it doesn't exist.
	 */
	public static function getGroup( $id ) {
		$groups = self::singleton()->getGroups();
		$id = self::normalizeId( $id );

		if ( isset( $groups[$id] ) ) {
			return $groups[$id];
		}

		if ( (string)$id !== '' && $id[0] === '!' ) {
			$dynamic = self::getDynamicGroups();
			if ( isset( $dynamic[$id] ) ) {
				return new $dynamic[$id];
			}
		}

		return null;
	}

	/**
	 * Fixes the id and resolves aliases.
	 *
	 * @param string $id
	 * @return string
	 * @since 2016.01
	 */
	public static function normalizeId( $id ) {
		/* Translatable pages use spaces, but MW occasionally likes to
		 * normalize spaces to underscores */
		if ( strpos( $id, 'page-' ) === 0 ) {
			$id = strtr( $id, '_', ' ' );
		}

		global $wgTranslateGroupAliases;
		if ( isset( $wgTranslateGroupAliases[$id] ) ) {
			$id = $wgTranslateGroupAliases[$id];
		}

		return $id;
	}

	/**
	 * @param string $id
	 * @return bool
	 */
	public static function exists( $id ) {
		return (bool)self::getGroup( $id );
	}

	/**
	 * Check if a particular aggregate group label exists
	 * @param string $name
	 * @return bool
	 */
	public static function labelExists( $name ) {
		$groups = self::loadAggregateGroups();
		$labels = array_map( function ( $g ) {
			/** @var MessageGroup $g */
			return $g->getLabel();
		}, $groups );
		return (bool)in_array( $name, $labels, true );
	}

	/**
	 * Get all enabled message groups.
	 * @return MessageGroup[] Map of (string => MessageGroup)
	 */
	public static function getAllGroups() {
		return self::singleton()->getGroups();
	}

	/**
	 * We want to de-emphasize time sensitive groups like news for 2009.
	 * They can still exist in the system, but should not appear in front
	 * of translators looking to do some useful work.
	 *
	 * @param MessageGroup|string $group Message group ID
	 * @return string Message group priority
	 * @since 2011-12-12
	 */
	public static function getPriority( $group ) {
		if ( !isset( self::$prioritycache ) ) {
			self::$prioritycache = [];
			// Abusing this table originally intented for other purposes
			$db = wfGetDB( DB_REPLICA );
			$table = 'translate_groupreviews';
			$fields = [ 'tgr_group', 'tgr_state' ];
			$conds = [ 'tgr_lang' => '*priority' ];
			$res = $db->select( $table, $fields, $conds, __METHOD__ );
			foreach ( $res as $row ) {
				self::$prioritycache[$row->tgr_group] = $row->tgr_state;
			}
		}

		if ( $group instanceof MessageGroup ) {
			$id = $group->getId();
		} else {
			$id = self::normalizeId( $group );
		}

		return self::$prioritycache[$id] ?? '';
	}

	/**
	 * Sets the message group priority.
	 *
	 * @param MessageGroup|string $group Message group
	 * @param string $priority Priority (empty string to unset)
	 * @since 2013-03-01
	 */
	public static function setPriority( $group, $priority = '' ) {
		if ( $group instanceof MessageGroup ) {
			$id = $group->getId();
		} else {
			$id = self::normalizeId( $group );
		}

		self::$prioritycache[$id] = $priority;

		$dbw = wfGetDB( DB_MASTER );
		$table = 'translate_groupreviews';
		$row = [
			'tgr_group' => $id,
			'tgr_lang' => '*priority',
			'tgr_state' => $priority,
		];

		if ( $priority === '' ) {
			unset( $row['tgr_state'] );
			$dbw->delete( $table, $row, __METHOD__ );
		} else {
			$index = [ 'tgr_group', 'tgr_lang' ];
			$dbw->replace( $table, [ $index ], $row, __METHOD__ );
		}
	}

	/**
	 * @since 2011-12-28
	 * @param MessageGroup $group
	 * @return bool
	 */
	public static function isDynamic( MessageGroup $group ) {
		$id = $group->getId();

		return (string)$id !== '' && $id[0] === '!';
	}

	/**
	 * Returns a list of message groups that share (certain) messages
	 * with this group.
	 * @since 2011-12-25; renamed in 2012-12-10 from getParentGroups.
	 * @param MessageGroup $group
	 * @return string[]
	 */
	public static function getSharedGroups( MessageGroup $group ) {
		// Take the first message, get a handle for it and check
		// if that message belongs to other groups. Those are the
		// parent aggregate groups. Ideally we loop over all keys,
		// but this should be enough.
		$keys = array_keys( $group->getDefinitions() );
		$title = Title::makeTitle( $group->getNamespace(), $keys[0] );
		$handle = new MessageHandle( $title );
		$ids = $handle->getGroupIds();
		foreach ( $ids as $index => $id ) {
			if ( $id === $group->getId() ) {
				unset( $ids[$index] );
			}
		}

		return $ids;
	}

	/**
	 * Returns a list of parent message groups. If message group exists
	 * in multiple places in the tree, multiple lists are returned.
	 * @since 2012-12-10
	 * @param MessageGroup $targetGroup
	 * @return array[]
	 */
	public static function getParentGroups( MessageGroup $targetGroup ) {
		$ids = self::getSharedGroups( $targetGroup );
		if ( $ids === [] ) {
			return [];
		}

		$targetId = $targetGroup->getId();

		/* Get the group structure. We will be using this to find which
		 * of our candidates are top-level groups. Prefilter it to only
		 * contain aggregate groups. */
		$structure = self::getGroupStructure();
		foreach ( $structure as $index => $group ) {
			if ( $group instanceof MessageGroup ) {
				unset( $structure[$index] );
			} else {
				$structure[$index] = array_shift( $group );
			}
		}

		/* Now that we have all related groups, use them to find all paths
		 * from top-level groups to target group with any number of subgroups
		 * in between. */
		$paths = [];

		/* This function recursively finds paths to the target group */
		$pathFinder = function ( &$paths, $group, $targetId, $prefix = '' )
		use ( &$pathFinder ) {
			if ( $group instanceof AggregateMessageGroup ) {
				/**
				 * @var MessageGroup $subgroup
				 */
				foreach ( $group->getGroups() as $subgroup ) {
					$subId = $subgroup->getId();
					if ( $subId === $targetId ) {
						$paths[] = $prefix;
						continue;
					}

					$pathFinder( $paths, $subgroup, $targetId, "$prefix|$subId" );
				}
			}
		};

		// Iterate over the top-level groups only
		foreach ( $ids as $id ) {
			// First, find a top level groups
			$group = self::getGroup( $id );

			// Quick escape for leaf groups
			if ( !$group instanceof AggregateMessageGroup ) {
				continue;
			}

			foreach ( $structure as $rootGroup ) {
				/**
				 * @var MessageGroup $rootGroup
				 */
				if ( $rootGroup->getId() === $group->getId() ) {
					// Yay we found a top-level group
					$pathFinder( $paths, $rootGroup, $targetId, $id );
					break; // No we have one or more paths appended into $paths
				}
			}
		}

		// And finally explode the strings
		foreach ( $paths as $index => $pathString ) {
			$paths[$index] = explode( '|', $pathString );
		}

		return $paths;
	}

	/**
	 * Constructor function.
	 * @return self
	 */
	public static function singleton() {
		static $instance;
		if ( !$instance instanceof self ) {
			$instance = new self();
		}

		return $instance;
	}

	/**
	 * Get all enabled non-dynamic message groups.
	 *
	 * @return MessageGroup[] Map of (group ID => MessageGroup)
	 */
	public function getGroups() {
		$this->init();

		return $this->groups;
	}

	/**
	 * Get message groups for corresponding message group ids.
	 *
	 * @param string[] $ids Group IDs
	 * @param bool $skipMeta Skip aggregate message groups
	 * @return MessageGroup[]
	 * @since 2012-02-13
	 */
	public static function getGroupsById( array $ids, $skipMeta = false ) {
		$groups = [];
		foreach ( $ids as $id ) {
			$group = self::getGroup( $id );

			if ( $group !== null ) {
				if ( $skipMeta && $group->isMeta() ) {
					continue;
				} else {
					$groups[$id] = $group;
				}
			} else {
				wfDebug( __METHOD__ . ": Invalid message group id: $id\n" );
			}
		}

		return $groups;
	}

	/**
	 * If the list of message group ids contains wildcards, this function will match
	 * them against the list of all supported message groups and return matched
	 * message group ids.
	 * @param string[]|string $ids
	 * @return string[]
	 * @since 2012-02-13
	 */
	public static function expandWildcards( $ids ) {
		$all = [];

		$ids = (array)$ids;
		foreach ( $ids as $index => $id ) {
			// Fast path, no wildcards
			if ( strcspn( $id, '*?' ) === strlen( $id ) ) {
				$g = self::getGroup( $id );
				if ( $g ) {
					$all[] = $g->getId();
				}
				unset( $ids[$index] );
			}
		}

		if ( $ids === [] ) {
			return $all;
		}

		// Slow path for the ones with wildcards
		$matcher = new StringMatcher( '', $ids );
		foreach ( self::getAllGroups() as $id => $_ ) {
			if ( $matcher->match( $id ) ) {
				$all[] = $id;
			}
		}

		return $all;
	}

	/**
	 * Contents on these groups changes on a whim.
	 * @since 2011-12-28
	 * @return array
	 */
	public static function getDynamicGroups() {
		return [
			'!recent' => 'RecentMessageGroup',
			'!additions' => 'RecentAdditionsMessageGroup',
			'!sandbox' => 'SandboxMessageGroup',
		];
	}

	/**
	 * Get only groups of specific type (class).
	 * @param string $type Class name of wanted type
	 * @return MessageGroupBase[] Map of (group ID => MessageGroupBase)
	 * @since 2012-04-30
	 */
	public static function getGroupsByType( $type ) {
		$groups = self::getAllGroups();
		foreach ( $groups as $id => $group ) {
			if ( !$group instanceof $type ) {
				unset( $groups[$id] );
			}
		}

		return $groups;
	}

	/**
	 * Returns a tree of message groups. First group in each subgroup is
	 * the aggregate group. Groups can be nested infinitely, though in practice
	 * other code might not handle more than two (or even one) nesting levels.
	 * One group can exist multiple times in differents parts of the tree.
	 * In other words: [Group1, Group2, [AggGroup, Group3, Group4]]
	 *
	 * @throws MWException If cyclic structure is detected.
	 * @return array Map of (group ID => MessageGroup or recursive array)
	 */
	public static function getGroupStructure() {
		$groups = self::getAllGroups();

		// Determine the top level groups of the tree
		$tree = $groups;
		/**
		 * @var MessageGroup $o
		 */
		foreach ( $groups as $id => $o ) {
			if ( !$o->exists() ) {
				unset( $groups[$id], $tree[$id] );
				continue;
			}

			if ( $o instanceof AggregateMessageGroup ) {
				/**
				 * @var AggregateMessageGroup $o
				 */
				foreach ( $o->getGroups() as $sid => $so ) {
					unset( $tree[$sid] );
				}
			}
		}

		// Work around php bug: https://bugs.php.net/bug.php?id=50688
		// Triggered by ApiQueryMessageGroups for example
		Wikimedia\suppressWarnings();
		usort( $tree, [ __CLASS__, 'groupLabelSort' ] );
		Wikimedia\restoreWarnings();

		/* Now we have two things left in $tree array:
		 * - solitaries: top-level non-aggregate message groups
		 * - top-level aggregate message groups */
		foreach ( $tree as $index => $group ) {
			if ( $group instanceof AggregateMessageGroup ) {
				$tree[$index] = self::subGroups( $group );
			}
		}

		/* Essentially we are done now. Cyclic groups can cause part of the
		 * groups not be included at all, because they have all unset each
		 * other in the first loop. So now we check if there are groups left
		 * over. */
		$used = [];
		// Hack to allow passing by reference
		array_walk_recursive( $tree, [ __CLASS__, 'collectGroupIds' ], [ &$used ] );
		$unused = array_diff( array_keys( $groups ), array_keys( $used ) );
		if ( count( $unused ) ) {
			foreach ( $unused as $index => $id ) {
				if ( !$groups[$id] instanceof AggregateMessageGroup ) {
					unset( $unused[$index] );
				}
			}

			// Only list the aggregate groups, other groups cannot cause cycles
			$participants = implode( ', ', $unused );
			throw new MWException( "Found cyclic aggregate message groups: $participants" );
		}

		return $tree;
	}

	/**
	 * See getGroupStructure, just collects ids into array
	 * @param MessageGroup $value
	 * @param string $key
	 * @param bool $used
	 */
	public static function collectGroupIds( MessageGroup $value, $key, $used ) {
		$used[0][$value->getId()] = true;
	}

	/**
	 * Sorts groups by label value
	 * @param MessageGroup $a
	 * @param MessageGroup $b
	 * @return int
	 */
	public static function groupLabelSort( $a, $b ) {
		$al = $a->getLabel();
		$bl = $b->getLabel();

		return strcasecmp( $al, $bl );
	}

	/**
	 * Like getGroupStructure but start from one root which must be an
	 * AggregateMessageGroup.
	 *
	 * @param AggregateMessageGroup $parent
	 * @param string[] &$childIds Flat list of child group IDs [returned]
	 * @param string $fname Calling method name; used to identify recursion [optional]
	 * @throws MWException
	 * @return array
	 * @since Public since 2012-11-29
	 */
	public static function subGroups(
		AggregateMessageGroup $parent,
		array &$childIds = [],
		$fname = 'caller'
) {
		static $recursionGuard = [];

		$pid = $parent->getId();
		if ( isset( $recursionGuard[$pid] ) ) {
			$tid = $pid;
			$path = [ $tid ];
			do {
				$tid = $recursionGuard[$tid];
				$path[] = $tid;
				// Until we have gone full cycle
			} while ( $tid !== $pid );
			$path = implode( ' > ', $path );
			throw new MWException( "Found cyclic aggregate message groups: $path" );
		}

		// We don't care about the ids.
		$tree = array_values( $parent->getGroups() );
		usort( $tree, [ __CLASS__, 'groupLabelSort' ] );
		// Expand aggregate groups (if any left) after sorting to form a tree
		foreach ( $tree as $index => $group ) {
			if ( $group instanceof AggregateMessageGroup ) {
				$sid = $group->getId();
				$recursionGuard[$pid] = $sid;
				$tree[$index] = self::subGroups( $group, $childIds, __METHOD__ );
				unset( $recursionGuard[$pid] );

				$childIds[$sid] = 1;
			}
		}

		// Parent group must be first item in the array
		array_unshift( $tree, $parent );

		if ( $fname !== __METHOD__ ) {
			// Move the IDs from the keys to the value for final return
			$childIds = array_values( $childIds );
		}

		return $tree;
	}

	/**
	 * Checks whether all the message groups have the same source language.
	 * @param array $groups A list of message groups objects.
	 * @return string Language code if the languages are the same, empty string otherwise.
	 * @since 2013.09
	 */
	public static function haveSingleSourceLanguage( array $groups ) {
		$seen = '';

		foreach ( $groups as $group ) {
			$language = $group->getSourceLanguage();
			if ( $seen === '' ) {
				$seen = $language;
			} elseif ( $language !== $seen ) {
				return '';
			}
		}

		return $seen;
	}

	/**
	 * Get all the aggregate messages groups defined in translate_metadata table.
	 *
	 * @return MessageGroup[]
	 */
	protected static function loadAggregateGroups() {
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
			$group = MessageGroupBase::factory( $conf );

			$groups[$id] = $group;
		}

		return $groups;
	}

	/**
	 * Filters out messages that should not be translated under normal
	 * conditions.
	 *
	 * @param MessageHandle $handle Handle for the translation target.
	 * @return bool
	 * @since 2013.10
	 */
	public static function isTranslatableMessage( MessageHandle $handle ) {
		static $cache = [];

		if ( !$handle->isValid() ) {
			return false;
		}

		$group = $handle->getGroup();
		$groupId = $group->getId();
		$language = $handle->getCode();
		$cacheKey = "$groupId:$language";

		if ( !isset( $cache[$cacheKey] ) ) {
			$allowed = true;
			$discouraged = false;

			$whitelist = $group->getTranslatableLanguages();
			if ( is_array( $whitelist ) && !isset( $whitelist[$language] ) ) {
				$allowed = false;
			}

			if ( self::getPriority( $group ) === 'discouraged' ) {
				$discouraged = true;
			} else {
				$priorityLanguages = TranslateMetadata::get( $groupId, 'prioritylangs' );
				if ( $priorityLanguages ) {
					$map = array_flip( explode( ',', $priorityLanguages ) );
					if ( !isset( $map[$language] ) ) {
						$discouraged = true;
					}
				}
			}

			$cache[$cacheKey] = [
				'relevant' => $allowed && !$discouraged,
				'tags' => [],
			];

			$groupTags = $group->getTags();
			foreach ( [ 'ignored', 'optional' ] as $tag ) {
				if ( isset( $groupTags[$tag] ) ) {
					foreach ( $groupTags[$tag] as $key ) {
						// TODO: ucfirst should not be here
						$cache[$cacheKey]['tags'][ucfirst( $key )] = true;
					}
				}
			}
		}

		return $cache[$cacheKey]['relevant'] &&
			!isset( $cache[$cacheKey]['tags'][ucfirst( $handle->getKey() )] );
	}
}
