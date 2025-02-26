<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use AggregateMessageGroup;
use CachedMessageGroupLoader;
use InvalidArgumentException;
use MediaWiki\Extension\Translate\MessageLoading\MessageHandle;
use MediaWiki\Extension\Translate\MessageProcessing\StringMatcher;
use MediaWiki\Extension\Translate\Services;
use MediaWiki\Extension\Translate\Utilities\Utilities;
use MediaWiki\MediaWikiServices;
use MediaWiki\Title\Title;
use MessageGroup;
use MessageGroupLoader;
use RuntimeException;
use Wikimedia\ObjectCache\WANObjectCache;

/**
 * Factory class for accessing message groups individually by id or
 * all of them as a list.
 * @todo Clean up the mixed static/member method interface.
 *
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @copyright Copyright © 2008-2013, Niklas Laxström, Siebrand Mazeland
 * @license GPL-2.0-or-later
 */
class MessageGroups {
	/** @var MessageGroup[]|null Map of (group ID => MessageGroup) */
	private $groups;
	/** @var MessageGroupLoader[]|null */
	private $groupLoaders;
	private WANObjectCache $cache;
	/**
	 * Tracks the current cache version. Update this when there are incompatible changes
	 * with the last version of the cache to force a new key to be used. The older cache
	 * will automatically expire and be cleared off.
	 * @var int
	 */
	private const CACHE_VERSION = 4;

	/** Initialises the list of groups */
	protected function init(): void {
		if ( is_array( $this->groups ) ) {
			return; // groups already initialized
		}

		$groups = [];
		foreach ( $this->getGroupLoaders() as $loader ) {
			$groups += $loader->getGroups();
		}
		$this->initGroupsFromDefinitions( $groups );
	}

	/**
	 * Expand process cached groups to objects
	 *
	 * @param array $groups Map of (group ID => mixed)
	 */
	protected function initGroupsFromDefinitions( array $groups ): void {
		foreach ( $groups as $id => $mixed ) {
			if ( !is_object( $mixed ) ) {
				$groups[$id] = $mixed( $id );
			}
		}

		$this->groups = $groups;
	}

	/** Clear message group caches and populate message groups from uncached data */
	public function recache(): void {
		$cache = $this->getCache();
		$cache->touchCheckKey( $this->getCacheKey() );

		$groups = [];
		foreach ( $this->getGroupLoaders() as $loader ) {
			if ( $loader instanceof CachedMessageGroupLoader ) {
				$groups += $loader->recache();
			} else {
				$groups += $loader->getGroups();
			}
		}
		$this->initGroupsFromDefinitions( $groups );
	}

	/**
	 * Manually reset the process cache.
	 *
	 * This is helpful for long-running scripts where the process cache might get stale
	 * even though the global cache is updated.
	 */
	public function clearProcessCache(): void {
		$this->groups = null;
		$this->groupLoaders = null;
	}

	protected function getCache(): WANObjectCache {
		return $this->cache ?? MediaWikiServices::getInstance()->getMainWANObjectCache();
	}

	/** Override cache, for example during tests. */
	public function setCache( WANObjectCache $cache ) {
		$this->cache = $cache;
	}

	/** Returns the cache key. */
	public function getCacheKey(): string {
		return $this->getCache()->makeKey( 'translate-groups', 'v' . self::CACHE_VERSION );
	}

	/**
	 * Loads and returns group loaders. Group loaders must implement MessageGroupLoader
	 * and may additionally implement CachedMessageGroupLoader
	 * @return MessageGroupLoader[]
	 */
	protected function getGroupLoaders(): array {
		if ( $this->groupLoaders !== null ) {
			return $this->groupLoaders;
		}

		$services = Services::getInstance();
		$cache = $this->getCache();
		$dbProvider = MediaWikiServices::getInstance()->getDBLoadBalancerFactory();

		$groupLoaderInstances = $this->groupLoaders = [];

		// Initialize the dependencies
		$deps = [
			'database' => Utilities::getSafeReadDB(),
			'cache' => $cache
		];

		$services->getHookRunner()->onTranslateInitGroupLoaders( $groupLoaderInstances, $deps );

		foreach ( $groupLoaderInstances as $loader ) {
			if ( !$loader instanceof MessageGroupLoader ) {
				throw new InvalidArgumentException(
					"MessageGroupLoader - $loader must implement the " .
					'MessageGroupLoader interface.'
				);
			}

			$this->groupLoaders[] = $loader;
		}

		$factories = [
			$services->getAggregateGroupMessageGroupFactory(),
			$services->getFileBasedMessageGroupFactory(),
			$services->getHookDefinedMessageGroupFactory(),
			$services->getMessageBundleMessageGroupFactory(),
			$services->getTranslatablePageMessageGroupFactory()
		];

		foreach ( $factories as $factory ) {
			$this->groupLoaders[] = new CachedMessageGroupFactoryLoader(
				$cache,
				$dbProvider,
				$factory
			);
		}

		// TODO: To decide if we should load this like we load other message group factories.
		// See Ie95f25e18816f48d1d663ae392ecf5f9bbcf0b56 for related discussion
		$this->groupLoaders[] = $services->getWorkflowStatesMessageGroupLoader();

		return $this->groupLoaders;
	}

	/**
	 * Fetch a message group by id.
	 *
	 * @param string $id Message group id.
	 * @return MessageGroup|null if it doesn't exist.
	 */
	public static function getGroup( string $id ): ?MessageGroup {
		$groups = self::singleton()->getGroups();
		$id = self::normalizeId( $id );

		if ( isset( $groups[$id] ) ) {
			return $groups[$id];
		}

		if ( $id !== '' && $id[0] === '!' ) {
			$dynamic = self::getDynamicGroups();
			if ( isset( $dynamic[$id] ) ) {
				return new $dynamic[$id];
			}
		}

		return null;
	}

	/** Fixes the id and resolves aliases. */
	public static function normalizeId( ?string $id ): string {
		/* Translatable pages use spaces, but MW occasionally likes to
		 * normalize spaces to underscores */
		$id = (string)$id;

		if ( str_starts_with( $id, 'page-' ) ) {
			$id = strtr( $id, '_', ' ' );
		}

		global $wgTranslateGroupAliases;
		if ( isset( $wgTranslateGroupAliases[$id] ) ) {
			$id = $wgTranslateGroupAliases[$id];
		}

		return $id;
	}

	public static function exists( string $id ): bool {
		return (bool)self::getGroup( $id );
	}

	/** Check if a particular aggregate group label exists */
	public static function labelExists( string $name ): bool {
		$groups = self::getAllGroups();
		foreach ( $groups as $group ) {
			if ( $group instanceof AggregateMessageGroup ) {
				if ( $group->getLabel() === $name ) {
					return true;
				}
			}

		}

		return false;
	}

	/**
	 * Get all enabled message groups.
	 * @return MessageGroup[] Map of (string => MessageGroup)
	 */
	public static function getAllGroups(): array {
		return self::singleton()->getGroups();
	}

	/**
	 * We want to de-emphasize time sensitive groups like news for 2009.
	 * They can still exist in the system, but should not appear in front
	 * of translators looking to do some useful work.
	 *
	 * @param MessageGroup|string $group Message group ID
	 * @return string Message group priority
	 */
	public static function getPriority( $group ): string {
		if ( $group instanceof MessageGroup ) {
			$id = $group->getId();
		} else {
			$id = self::normalizeId( $group );
		}

		return Services::getInstance()->getMessageGroupReviewStore()->getGroupPriority( $id ) ?? '';
	}

	/**
	 * Sets the message group priority.
	 *
	 * @param MessageGroup|string $group Message group
	 * @param string $priority Priority (empty string to unset)
	 */
	public static function setPriority( $group, string $priority = '' ): void {
		if ( $group instanceof MessageGroup ) {
			$id = $group->getId();
		} else {
			$id = self::normalizeId( $group );
		}

		$priority = $priority === '' ? null : $priority;
		Services::getInstance()->getMessageGroupReviewStore()->setGroupPriority( $id, $priority );
	}

	public static function isDynamic( MessageGroup $group ): bool {
		$id = $group->getId();

		return ( $id[0] ?? null ) === '!';
	}

	/**
	 * Returns a list of message groups that share (certain) messages
	 * with this group.
	 */
	public static function getSharedGroups( MessageGroup $group ): array {
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
				break;
			}
		}

		return $ids;
	}

	/**
	 * Returns a list of parent message groups. If message group exists
	 * in multiple places in the tree, multiple lists are returned.
	 */
	public static function getParentGroups( MessageGroup $targetGroup ): array {
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
		$pathFinder = static function ( &$paths, $group, $targetId, $prefix = '' )
		use ( &$pathFinder ) {
			if ( $group instanceof AggregateMessageGroup ) {
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
				/** @var MessageGroup $rootGroup */
				if ( $rootGroup->getId() === $group->getId() ) {
					// Yay we found a top-level group
					$pathFinder( $paths, $rootGroup, $targetId, $id );
					break; // No we have one or more paths appended into $paths
				}
			}
		}

		// And finally explode the strings
		return array_map( static function ( string $pathString ): array {
			return explode( '|', $pathString );
		}, $paths );
	}

	public static function singleton(): self {
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
	public function getGroups(): array {
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
	public static function getGroupsById( array $ids, bool $skipMeta = false ): array {
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
	 */
	public static function expandWildcards( $ids ): array {
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
			if ( $matcher->matches( $id ) ) {
				$all[] = $id;
			}
		}

		return $all;
	}

	/** Contents on these groups changes on a whim. */
	public static function getDynamicGroups(): array {
		return [
			'!recent' => 'RecentMessageGroup',
			'!additions' => 'RecentAdditionsMessageGroup',
			'!sandbox' => 'SandboxMessageGroup',
		];
	}

	/**
	 * Get only groups of specific type (class).
	 * @phan-template T
	 * @param string $type Class name of wanted type
	 * @phan-param class-string<T> $type
	 * @return MessageGroup[] Map of (group ID => MessageGroupBase)
	 * @phan-return array<T&MessageGroup>
	 * @since 2012-04-30
	 */
	public static function getGroupsByType( string $type ): array {
		$groups = self::getAllGroups();
		foreach ( $groups as $id => $group ) {
			if ( !$group instanceof $type ) {
				unset( $groups[$id] );
			}
		}

		// @phan-suppress-next-line PhanTypeMismatchReturn
		return $groups;
	}

	/**
	 * Returns a tree of message groups. First group in each subgroup is
	 * the aggregate group. Groups can be nested infinitely, though in practice
	 * other code might not handle more than two (or even one) nesting levels.
	 * One group can exist multiple times in different parts of the tree.
	 * In other words: [Group1, Group2, [AggGroup, Group3, Group4]]
	 *
	 * @return array Map of (group ID => MessageGroup or recursive array)
	 */
	public static function getGroupStructure(): array {
		$groups = self::getAllGroups();

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

		uasort( $tree, [ self::class, 'groupLabelSort' ] );

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
		array_walk_recursive(
			$tree,
			static function ( MessageGroup $group ) use ( &$used ) {
				$used[$group->getId()] = true;
			}
		);
		$unused = array_diff_key( $groups, $used );
		if ( $unused ) {
			foreach ( $unused as $index => $group ) {
				if ( !$group instanceof AggregateMessageGroup ) {
					unset( $unused[$index] );
				}
			}

			// Only list the aggregate groups, other groups cannot cause cycles
			$participants = implode( ', ', array_keys( $unused ) );
			throw new RuntimeException( "Found cyclic aggregate message groups: $participants" );
		}

		return $tree;
	}

	/** Sorts groups by label value */
	public static function groupLabelSort( MessageGroup $a, MessageGroup $b ): int {
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
	 * @return array
	 */
	public static function subGroups(
		AggregateMessageGroup $parent,
		array &$childIds = [],
		string $fname = 'caller'
	): array {
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
			throw new RuntimeException( "Found cyclic aggregate message groups: $path" );
		}

		// We don't care about the ids.
		$tree = array_values( $parent->getGroups() );
		usort( $tree, [ self::class, 'groupLabelSort' ] );
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
	 */
	public static function haveSingleSourceLanguage( array $groups ): string {
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
	 * Filters out messages that should not be translated under normal
	 * conditions.
	 *
	 * @param MessageHandle $handle Handle for the translation target.
	 * @param string $targetLanguage
	 * @return bool
	 */
	public static function isTranslatableMessage( MessageHandle $handle, string $targetLanguage ): bool {
		static $cache = [];

		if ( !$handle->isValid() ) {
			return false;
		}

		$group = $handle->getGroup();
		$groupId = $group->getId();
		$cacheKey = "$groupId:$targetLanguage";

		if ( !isset( $cache[$cacheKey] ) ) {
			$supportedLanguages = Utilities::getLanguageNames( 'en' );
			$inclusionList = $group->getTranslatableLanguages() ?? $supportedLanguages;

			$included = isset( $inclusionList[$targetLanguage] );
			$excluded = Services::getInstance()->getMessageGroupMetadata()->isExcluded( $groupId, $targetLanguage );

			$cache[$cacheKey] = [
				'relevant' => $included && !$excluded,
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

	/** @internal For testing */
	public function overrideGroupsForTesting( array $groups ): void {
		$this->groups = $groups;
	}
}

class_alias( MessageGroups::class, 'MessageGroups' );
