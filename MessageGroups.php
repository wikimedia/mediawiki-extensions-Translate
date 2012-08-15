<?php
/**
 * This file contains a class for working with message groups.
 *
 * @file
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @copyright Copyright © 2008-2012, Niklas Laxström, Siebrand Mazeland
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

/**
 * Factory class for accessing message groups individually by id or
 * all of them as an list.
 * @todo Clean up the mixed static/member method interface.
 */
class MessageGroups {
	/// Initialises the list of groups (but not the groups itself if possible).
	public static function init() {
		static $loaded = false;
		if ( $loaded ) {
			return;
		}
		$loaded = true;

		global $wgTranslateCC, $wgTranslateEC, $wgTranslateAC;
		global $wgAutoloadClasses;

		$key = wfMemcKey( 'translate-groups' );
		$value = DependencyWrapper::getValueFromCache( self::getCache(), $key );

		if ( $value === null ) {
			wfDebug( __METHOD__ . "-nocache\n" );
			self::loadGroupDefinitions();
		} else {
			wfDebug( __METHOD__ . "-withcache\n" );
			$wgTranslateCC = $value['cc'];
			$wgTranslateAC = $value['ac'];
			$wgTranslateEC = $value['ec'];

			foreach ( $value['autoload'] as $class => $file ) {
				$wgAutoloadClasses[$class] = $file;
			}
		}
	}

	/**
	 * Manually reset group cache.
	 *
	 * Use when automatic dependency tracking fails.
	 */
	public static function clearCache() {
		$key = wfMemckey( 'translate-groups' );
		self::getCache()->delete( $key );
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
	 * @todo In conjuction with the above, reduce the number of global
	 * variables like wgTranslate#C and have the message groups specify
	 * their own cache dependencies.
	 */
	protected static function loadGroupDefinitions() {
		wfProfileIn( __METHOD__ );

		global $wgEnablePageTranslation, $wgTranslateGroupFiles;
		global $wgTranslateAC, $wgTranslateEC, $wgTranslateCC;
		global $wgAutoloadClasses;
		global $wgTranslateWorkflowStates;

		$deps = array();
		$deps[] = new GlobalDependency( 'wgEnablePageTranslation' );
		$deps[] = new GlobalDependency( 'wgTranslateGroupFiles' );
		$deps[] = new GlobalDependency( 'wgTranslateAC' );
		$deps[] = new GlobalDependency( 'wgTranslateEC' );
		$deps[] = new GlobalDependency( 'wgTranslateCC' );
		$deps[] = new GlobalDependency( 'wgTranslateExtensionDirectory' );
		$deps[] = new GlobalDependency( 'wgTranslateWorkflowStates' );

		if ( $wgEnablePageTranslation ) {
			wfProfileIn( __METHOD__ . '-pt' );
			$dbr = wfGetDB( DB_MASTER );

			$tables = array( 'page', 'revtag' );
			$vars   = array( 'page_id', 'page_namespace', 'page_title', );
			$conds  = array( 'page_id=rt_page', 'rt_type' => RevTag::getType( 'tp:mark' ) );
			$options = array( 'GROUP BY' => 'rt_page' );
			$res = $dbr->select( $tables, $vars, $conds, __METHOD__, $options );

			foreach ( $res as $r ) {
				$title = Title::makeTitle( $r->page_namespace, $r->page_title );
				$id = TranslatablePage::getMessageGroupIdFromTitle( $title );
				$wgTranslateCC[$id] = new WikiPageMessageGroup( $id, $title );
				$wgTranslateCC[$id]->setLabel( $title->getPrefixedText() );
			}
			wfProfileOut( __METHOD__ . '-pt' );
		}

		if ( $wgTranslateWorkflowStates ) {
			$wgTranslateCC['translate-workflow-states'] = new WorkflowStatesMessageGroup();
		}

		wfProfileIn( __METHOD__ . '-hook' );
		$autoload = array();
		wfRunHooks( 'TranslatePostInitGroups', array( &$wgTranslateCC, &$deps, &$autoload ) );
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
				$wgTranslateCC[$id] = $group;
			}
		}
		wfProfileOut( __METHOD__ . '-yaml' );

		wfProfileIn( __METHOD__ . '-agg' );
		$aggregateGroups = self::getAggregateGroups();
		foreach ( $aggregateGroups as $id => $group ) {
			$wgTranslateCC[$id] = $group;
		}
		wfProfileOut( __METHOD__ . '-agg' );

		$key = wfMemckey( 'translate-groups' );
		$value = array(
			'ac' => $wgTranslateAC,
			'ec' => $wgTranslateEC,
			'cc' => $wgTranslateCC,
			'autoload' => $autoload,
		);

		$wrapper = new DependencyWrapper( $value, $deps );
		$wrapper->storeToCache( self::getCache(), $key, 60 * 60 * 2 );

		wfDebug( __METHOD__ . "-end\n" );
		wfProfileOut( __METHOD__ );
	}

	/**
	 * Fetch a message group by id.
	 * @param $id \string Message group id.
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

		global $wgTranslateEC, $wgTranslateAC, $wgTranslateCC;

		if ( in_array( $id, $wgTranslateEC ) ) {
			$creater = $wgTranslateAC[$id];
			if ( is_array( $creater ) ) {
				return call_user_func( $creater, $id );
			}
			return new $creater;
		} elseif ( isset( $wgTranslateCC[$id] ) ) {
			if ( is_callable( $wgTranslateCC[$id] ) ) {
				return call_user_func( $wgTranslateCC[$id], $id );
			}
			return $wgTranslateCC[$id];
		} elseif ( strval( $id ) !== '' && $id[0] === '!' ) {
			$dynamic = self::getDynamicGroups();
			if ( isset( $dynamic[$id] ) ) {
				return new $dynamic[$id];
			}
		}
	}

	/**
	 * @param $id
	 * @return bool
	 */
	public static function exists( $id ) {
		return (bool) self::getGroup( $id );
	}

	/**
	 * Get all enabled message groups.
	 * @return \arrayof{String,MessageGroup}
	 */
	public static function getAllGroups() {
		return self::singleton()->getGroups();
	}

	/**
	 * We want to de-emphasize time sensitive groups like news for 2009.
	 * They can still exist in the system, but should not appear in front
	 * of translators looking to do some useful work.
	 *
	 * @param $group MessageGroup|string Message group ID
	 * @return string Message group priority
	 * @since 2011-12-12
	 */
	public static function getPriority( $group ) {
		static $groups = null;
		if ( $groups === null ) {
			$groups = array();
			// Abusing this table originally intented for other purposes
			$db = wfGetDB( DB_SLAVE );
			$table = 'translate_groupreviews';
			$fields = array( 'tgr_group', 'tgr_state' );
			$conds = array( 'tgr_lang' => '*priority' );
			$res = $db->select( $table, $fields, $conds, __METHOD__ );
			foreach ( $res as $row ) {
				$groups[$row->tgr_group] = $row->tgr_state;
			}
		}

		if ( $group instanceof MessageGroup ) {
			$id = $group->getId();
		} else {
			$id = $group;
		}
		return isset( $groups[$id] ) ? $groups[$id] : '';
	}

	/// @since 2011-12-28
	public static function isDynamic( MessageGroup $group ) {
		$id = $group->getId();
		return strval( $id ) !== '' && $id[0] === '!';
	}

	/**
	 * Returns the message groups this message group is part of.
	 * @since 2011-12-25
	 * @return array
	 */
	public static function getParentGroups( MessageGroup $group ) {
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

	/// @todo Make protected.
	public $classes;
	private function __construct() {
		self::init();
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
	 * Get all enabled message groups.
	 * @return \array
	 */
	public function getGroups() {
		if ( $this->classes === null ) {
			$this->classes = array();
			global $wgTranslateEC, $wgTranslateCC;

			$all = array_merge( $wgTranslateEC, array_keys( $wgTranslateCC ) );
			sort( $all );

			foreach ( $all as $id ) {
				$g = self::getGroup( $id );
				$this->classes[$g->getId()] = $g;
			}
		}

		return $this->classes;
	}

	/**
	 * Get message groups for corresponding message group ids.
	 *
	 * @param $ids array Group IDs
	 * @param $skipMeta bool Skip aggregate message groups
	 * @return array
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
	 * @param $ids \list{String}|String
	 * @return \list{String}
	 * @since 2012-02-13
	 */
	public static function expandWildcards( $ids ) {
		$all = array();

		$matcher = new StringMatcher( '', (array) $ids );
		foreach ( self::singleton()->getGroups() as $id => $_ ) {
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
		foreach ( $groups as $id => $o ) {
			if ( !$o->exists() ) {
				unset( $groups[$id], $tree[$id] );
				continue;
			}

			if ( $o instanceof AggregateMessageGroup ) {
				foreach ( $o->getGroups() as $sid => $so ) {
					unset( $tree[$sid] );
				}
			}
		}

		/* Now we have two things left in $tree array:
		 * - solitaries: top-level non-aggregate message groups
		 * - top-level aggregate message groups */
		usort( $tree, array( __CLASS__, 'groupLabelSort' ) );
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

	/// Helper for getGroupStructure
	protected static function subGroups( AggregateMessageGroup $parent ) {
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

}
