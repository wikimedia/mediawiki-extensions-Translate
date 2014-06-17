<?php
/**
 * This file contains a class for working with message groups.
 *
 * @file
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @copyright Copyright © 2008-2013, Niklas Laxström, Siebrand Mazeland
 * @license GPL-2.0+
 */

/**
 * Factory class for accessing message groups individually by id or
 * all of them as an list.
 * @todo Clean up the mixed static/member method interface.
 */
class MessageGroups {
	/**
	 * @var Array Cache for message group priorities
	 */
	protected static $prioritycache = null;

	protected static $groups = null;

	/// Initialises the list of groups (but not the groups itself if possible).
	public static function init() {
		if ( is_array( self::$groups ) ) {
			return;
		}

		wfProfileIn( __METHOD__ );
		self::$groups = array();

		global $wgAutoloadClasses;

		$key = wfMemcKey( 'translate-groups' );
		$value = DependencyWrapper::getValueFromCache( self::getCache(), $key );

		if ( $value === null ) {
			wfDebug( __METHOD__ . "-nocache\n" );
			self::loadGroupDefinitions();
		} else {
			wfDebug( __METHOD__ . "-withcache\n" );
			self::$groups = $value['cc'];

			foreach ( $value['autoload'] as $class => $file ) {
				$wgAutoloadClasses[$class] = $file;
			}
		}
		wfProfileOut( __METHOD__ );
	}

	/**
	 * Manually reset group cache.
	 *
	 * Use when automatic dependency tracking fails.
	 */
	public static function clearCache() {
		$key = wfMemckey( 'translate-groups' );
		self::getCache()->delete( $key );
		self::$groups = null;
	}

	/**
	 * Returns a cacher object.
	 * @return BagOStuff
	 */
	protected static function getCache() {
		return wfGetCache( CACHE_ANYTHING );
	}

	/**
	 * This constructs the list of all groups from multiple different
	 * sources. When possible, a cache dependency is created to automatically
	 * recreate the cache when configuration changes.
	 * @todo Reduce the ways of which messages can be added. Target is just
	 * to have three ways: Yaml files, translatable pages and with the hook.
	 */
	protected static function loadGroupDefinitions() {
		wfProfileIn( __METHOD__ );

		global $wgEnablePageTranslation, $wgTranslateGroupFiles;
		global $wgTranslateCC, $wgAutoloadClasses, $wgTranslateWorkflowStates;

		$deps = array();
		$deps[] = new GlobalDependency( 'wgEnablePageTranslation' );
		$deps[] = new GlobalDependency( 'wgTranslateGroupFiles' );
		$deps[] = new GlobalDependency( 'wgTranslateCC' );
		$deps[] = new GlobalDependency( 'wgTranslateWorkflowStates' );

		self::$groups = $wgTranslateCC;

		if ( $wgEnablePageTranslation ) {
			wfProfileIn( __METHOD__ . '-pt' );
			$dbr = wfGetDB( DB_MASTER );

			$tables = array( 'page', 'revtag' );
			$vars = array( 'page_id', 'page_namespace', 'page_title' );
			$conds = array( 'page_id=rt_page', 'rt_type' => RevTag::getType( 'tp:mark' ) );
			$options = array( 'GROUP BY' => 'rt_page' );
			$res = $dbr->select( $tables, $vars, $conds, __METHOD__, $options );

			foreach ( $res as $r ) {
				$title = Title::newFromRow( $r );
				$id = TranslatablePage::getMessageGroupIdFromTitle( $title );
				self::$groups[$id] = new WikiPageMessageGroup( $id, $title );
				self::$groups[$id]->setLabel( $title->getPrefixedText() );
			}
			wfProfileOut( __METHOD__ . '-pt' );
		}

		if ( $wgTranslateWorkflowStates ) {
			self::$groups['translate-workflow-states'] = new WorkflowStatesMessageGroup();
		}

		wfProfileIn( __METHOD__ . '-hook' );
		$autoload = array();
		wfRunHooks( 'TranslatePostInitGroups', array( &self::$groups, &$deps, &$autoload ) );
		wfProfileOut( __METHOD__ . '-hook' );

		wfProfileIn( __METHOD__ . '-yaml' );
		foreach ( $wgTranslateGroupFiles as $configFile ) {
			wfDebug( $configFile . "\n" );
			$deps[] = new FileDependency( realpath( $configFile ) );
			$fgroups = TranslateYaml::parseGroupFile( $configFile );

			foreach ( $fgroups as $id => $conf ) {
				if ( !empty( $conf['AUTOLOAD'] ) && is_array( $conf['AUTOLOAD'] ) ) {
					$dir = dirname( $configFile );
					foreach ( $conf['AUTOLOAD'] as $class => $file ) {
						// For this request and for caching.
						$wgAutoloadClasses[$class] = "$dir/$file";
						$autoload[$class] = "$dir/$file";
					}
				}
				$group = MessageGroupBase::factory( $conf );
				self::$groups[$id] = $group;
			}
		}
		wfProfileOut( __METHOD__ . '-yaml' );

		wfProfileIn( __METHOD__ . '-agg' );
		$aggregateGroups = self::getAggregateGroups();
		foreach ( $aggregateGroups as $id => $group ) {
			self::$groups[$id] = $group;
		}
		wfProfileOut( __METHOD__ . '-agg' );

		$key = wfMemckey( 'translate-groups' );
		$value = array(
			'cc' => self::$groups,
			'autoload' => $autoload,
		);

		wfProfileIn( __METHOD__ . '-save' );
		$wrapper = new DependencyWrapper( $value, $deps );
		$wrapper->storeToCache( self::getCache(), $key, 60 * 60 * 2 );
		wfProfileOut( __METHOD__ . '-save' );

		wfDebug( __METHOD__ . "-end\n" );
		wfProfileOut( __METHOD__ );
	}

	/**
	 * Fetch a message group by id.
	 * @param string $id Message group id.
	 * @return MessageGroup|null if it doesn't exist.
	 */
	public static function getGroup( $id ) {
		// BC with page| which is now page-
		$id = strtr( $id, '|', '-' );
		/* Translatable pages use spaces, but MW occasionally likes to
		 * normalize spaces to underscores */
		if ( strpos( $id, 'page-' ) === 0 ) {
			$id = strtr( $id, '_', ' ' );
		}
		self::init();

		if ( isset( self::$groups[$id] ) ) {
			if ( is_callable( self::$groups[$id] ) ) {
				return call_user_func( self::$groups[$id], $id );
			}

			return self::$groups[$id];
		} elseif ( strval( $id ) !== '' && $id[0] === '!' ) {
			$dynamic = self::getDynamicGroups();
			if ( isset( $dynamic[$id] ) ) {
				return new $dynamic[$id];
			}
		}

		return null;
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
		$groups = MessageGroups::getAggregateGroups();
		$labels = array_map( function ( $g ) {
			return $g->getLabel();
		}, $groups );
		return (bool)in_array( $name, $labels, true );
	}

	/**
	 * Get all enabled message groups.
	 * @return array ( string => MessageGroup )
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
			self::$prioritycache = array();
			// Abusing this table originally intented for other purposes
			$db = wfGetDB( DB_MASTER );
			$table = 'translate_groupreviews';
			$fields = array( 'tgr_group', 'tgr_state' );
			$conds = array( 'tgr_lang' => '*priority' );
			$res = $db->select( $table, $fields, $conds, __METHOD__ );
			foreach ( $res as $row ) {
				self::$prioritycache[$row->tgr_group] = $row->tgr_state;
			}
		}

		if ( $group instanceof MessageGroup ) {
			$id = $group->getId();
		} else {
			$id = $group;
		}

		return isset( self::$prioritycache[$id] ) ? self::$prioritycache[$id] : '';
	}

	/**
	 * Sets the message group priority.
	 * @see MessageGroups::getPriority
	 *
	 * @param MessageGroup|string $group Message group
	 * @param string $priority Priority (empty string to unset)
	 * @since 2013-03-01
	 */
	public static function setPriority( $group, $priority = '' ) {
		if ( $group instanceof MessageGroup ) {
			$id = $group->getId();
		} else {
			$id = $group;
		}

		self::$prioritycache[$id] = $priority;

		$dbw = wfGetDB( DB_MASTER );
		$table = 'translate_groupreviews';
		$row = array(
			'tgr_group' => $id,
			'tgr_lang' => '*priority',
			'tgr_state' => $priority,
		);

		if ( $priority === '' ) {
			unset( $row['tgr_state'] );
			$dbw->delete( $table, $row, __METHOD__ );
		} else {
			$index = array( 'tgr_group', 'tgr_lang' );
			$dbw->replace( $table, array( $index ), $row, __METHOD__ );
		}
	}

	/// @since 2011-12-28
	public static function isDynamic( MessageGroup $group ) {
		$id = $group->getId();

		return strval( $id ) !== '' && $id[0] === '!';
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
		if ( $ids === array() ) {
			return array();
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
		$paths = array();

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

	private function __construct() {
	}

	/**
	 * Constructor function.
	 * @return MessageGroups
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
	 * @return array
	 */
	public function getGroups() {
		self::init();
		// Expand groups to objects
		foreach ( self::$groups as $id => $mixed ) {
			if ( !is_object( $mixed ) ) {
				self::$groups[$id] = call_user_func( $mixed, $id );
			}
		}

		return self::$groups;
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
		$groups = array();
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
		$all = array();

		$matcher = new StringMatcher( '', (array)$ids );
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
	 */
	public static function getDynamicGroups() {
		return array(
			'!recent' => 'RecentMessageGroup',
			'!additions' => 'RecentAdditionsMessageGroup',
			'!sandbox' => 'SandboxMessageGroup',
		);
	}

	/**
	 * Get only groups of specific type (class).
	 * @param string $type Class name of wanted type
	 * @return MessageGroupBase[]
	 * @since 2012-04-30
	 */
	public static function getGroupsByType( $type ) {
		wfProfileIn( __METHOD__ );
		$groups = self::getAllGroups();
		foreach ( $groups as $id => $group ) {
			if ( !$group instanceof $type ) {
				unset( $groups[$id] );
			}
		}
		wfProfileOut( __METHOD__ );

		return $groups;
	}

	/**
	 * Returns a tree of message groups. First group in each subgroup is
	 * the aggregate group. Groups can be nested infinitely, though in practice
	 * other code might not handle more than two (or even one) nesting levels.
	 * One group can exist multiple times in differents parts of the tree.
	 * In other words: [Group1, Group2, [AggGroup, Group3, Group4]]
	 * @throws MWException If cyclic structure is detected.
	 * @return array
	 */
	public static function getGroupStructure() {
		$groups = self::getAllGroups();
		wfProfileIn( __METHOD__ );

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
		wfSuppressWarnings();
		usort( $tree, array( __CLASS__, 'groupLabelSort' ) );
		wfRestoreWarnings();

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
		$used = array();
		// Hack to allow passing by reference
		array_walk_recursive( $tree, array( __CLASS__, 'collectGroupIds' ), array( &$used ) );
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

		wfProfileOut( __METHOD__ );

		return $tree;
	}

	/// See getGroupStructure, just collects ids into array
	public static function collectGroupIds( $value, $key, $used ) {
		$used[0][$value->getId()] = true;
	}

	/// Sorts groups by label value
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
	 * @throws MWException
	 * @return array
	 * @since Public since 2012-11-29
	 */
	public static function subGroups( AggregateMessageGroup $parent ) {
		static $recursionGuard = array();

		$pid = $parent->getId();
		if ( isset( $recursionGuard[$pid] ) ) {
			$tid = $pid;
			$path = array( $tid );
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
		usort( $tree, array( __CLASS__, 'groupLabelSort' ) );
		// Expand aggregate groups (if any left) after sorting to form a tree
		foreach ( $tree as $index => $group ) {
			if ( $group instanceof AggregateMessageGroup ) {
				$sid = $group->getId();
				$recursionGuard[$pid] = $sid;
				$tree[$index] = self::subGroups( $group );
				unset( $recursionGuard[$pid] );
			}
		}

		// Parent group must be first item in the array
		array_unshift( $tree, $parent );

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
	 * @return array
	 * @since 2012-05-09 return value changed
	 */
	protected static function getAggregateGroups() {
		$dbw = wfGetDB( DB_MASTER );
		$tables = array( 'translate_metadata' );
		$fields = array( 'tmd_group', 'tmd_value' );
		$conds = array( 'tmd_key' => 'subgroups' );
		$res = $dbw->select( $tables, $fields, $conds, __METHOD__ );

		$groups = array();
		foreach ( $res as $row ) {
			$id = $row->tmd_group;

			$conf = array();
			$conf['BASIC'] = array(
				'id' => $id,
				'label' => TranslateMetadata::get( $id, 'name' ),
				'description' => TranslateMetadata::get( $id, 'description' ),
				'meta' => 1,
				'class' => 'AggregateMessageGroup',
				'namespace' => NS_TRANSLATIONS,
			);
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
	 * @return boolean
	 * @since 2013.10
	 */
	public static function isTranslatableMessage( MessageHandle $handle ) {
		static $cache = array();

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

			$cache[$cacheKey] = array(
				'relevant' => $allowed && !$discouraged,
				'tags' => array(),
			);

			$groupTags = $group->getTags();
			foreach ( array( 'ignored', 'optional' ) as $tag ) {
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
