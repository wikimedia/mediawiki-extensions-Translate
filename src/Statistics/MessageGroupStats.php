<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Statistics;

use AggregateMessageGroup;
use FileBasedMessageGroup;
use MediaWiki\Deferred\DeferredUpdates;
use MediaWiki\Extension\Translate\LogNames;
use MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroups;
use MediaWiki\Extension\Translate\MessageLoading\MessageCollection;
use MediaWiki\Extension\Translate\MessageLoading\MessageHandle;
use MediaWiki\Extension\Translate\Services;
use MediaWiki\Extension\Translate\Utilities\Utilities;
use MediaWiki\Logger\LoggerFactory;
use MediaWiki\MediaWikiServices;
use MessageGroup;
use stdClass;
use Wikimedia\ObjectCache\WANObjectCache;
use Wikimedia\Rdbms\Database;
use Wikimedia\Rdbms\IDatabase;

/**
 * This class aims to provide efficient mechanism for fetching translation completion stats.
 * It abstracts MessageGroup statistics calculation and storing.
 * You can access stats easily per language or per group.
 * Stat array for each item is of format array( total, translate, fuzzy ).
 * @author Wikia (trac.wikia-code.com/browser/wikia/trunk/extensions/wikia/TranslationStatistics)
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 *
 * @ingroup Stats MessageGroups
 */
class MessageGroupStats {
	/** Name of the database table */
	private const TABLE = 'translate_groupstats';
	/** Cache key for storage of all language stats */
	private const LANGUAGE_STATS_KEY = 'translate-all-language-stats';

	public const TOTAL = 0; ///< Array index
	public const TRANSLATED = 1; ///< Array index
	public const FUZZY = 2; ///< Array index
	public const PROOFREAD = 3; ///< Array index

	/** If stats are not cached, do not attempt to calculate them on the fly */
	public const FLAG_CACHE_ONLY = 1;
	/** Ignore cached values. Useful for updating stale values. */
	public const FLAG_NO_CACHE = 2;
	/** Do not defer updates. Meant for jobs like RebuildMessageGroupStatsJob. */
	public const FLAG_IMMEDIATE_WRITES = 4;

	/** @var array[] */
	private static array $updates = [];
	/** @var string[]|null */
	private static ?array $languages = null;

	/**
	 * Returns empty stats array. Useful because the number of elements may change.
	 * @return int[]
	 */
	public static function getEmptyStats(): array {
		return [ 0, 0, 0, 0 ];
	}

	/**
	 * Returns empty stats array that indicates stats are incomplete or unknown.
	 * @return null[]
	 */
	private static function getUnknownStats(): array {
		return [ null, null, null, null ];
	}

	private static function isValidLanguage( string $languageCode ): bool {
		$languages = self::getLanguages();
		return in_array( $languageCode, $languages );
	}

	/**
	 * In case some code calls stats for dynamic groups. Calculating these numbers
	 * don't make sense for dynamic groups, and would just throw an exception.
	 */
	private static function isValidMessageGroup( ?MessageGroup $group ): bool {
		return $group && !MessageGroups::isDynamic( $group );
	}

	/**
	 * Returns stats for given group in given language.
	 * @param string $groupId
	 * @param string $languageCode
	 * @param int $flags Combination of FLAG_* constants.
	 * @return null[]|int[]
	 */
	public static function forItem( string $groupId, string $languageCode, int $flags = 0 ): array {
		$group = MessageGroups::getGroup( $groupId );
		if ( !self::isValidMessageGroup( $group ) || !self::isValidLanguage( $languageCode ) ) {
			return self::getUnknownStats();
		}

		$res = self::selectRowsIdLang( [ $groupId ], [ $languageCode ], $flags );
		$stats = self::extractResults( $res, [ $groupId ] );

		if ( !isset( $stats[$groupId][$languageCode] ) ) {
			$stats[$groupId][$languageCode] = self::forItemInternal( $stats, $group, $languageCode, $flags );
		}

		self::queueUpdates( $flags );

		return $stats[$groupId][$languageCode];
	}

	/**
	 * Returns stats for all groups in given language.
	 * @param string $languageCode
	 * @param int $flags Combination of FLAG_* constants.
	 * @return array[]
	 */
	public static function forLanguage( string $languageCode, int $flags = 0 ): array {
		if ( !self::isValidLanguage( $languageCode ) ) {
			$stats = [];
			$groups = MessageGroups::singleton()->getGroups();
			$ids = array_keys( $groups );
			foreach ( $ids as $id ) {
				$stats[$id] = self::getUnknownStats();
			}

			return $stats;
		}

		$stats = self::forLanguageInternal( $languageCode, [], $flags );
		$flattened = [];
		foreach ( $stats as $group => $languages ) {
			$flattened[$group] = $languages[$languageCode];
		}

		self::queueUpdates( $flags );

		return $flattened;
	}

	/**
	 * Returns stats for all languages in given group.
	 * @param string $groupId
	 * @param int $flags Combination of FLAG_* constants.
	 * @return array[]
	 */
	public static function forGroup( string $groupId, int $flags = 0 ): array {
		$group = MessageGroups::getGroup( $groupId );
		if ( !self::isValidMessageGroup( $group ) ) {
			$languages = self::getLanguages();
			$stats = [];
			foreach ( $languages as $code ) {
				$stats[$code] = self::getUnknownStats();
			}

			return $stats;
		}

		$stats = self::forGroupInternal( $group, [], $flags );

		self::queueUpdates( $flags );

		return $stats[$groupId];
	}

	/**
	 * Recalculate stats for all groups associated with the message.
	 *
	 * Hook: TranslateEventTranslationReview
	 * @param MessageHandle $handle
	 */
	public static function clear( MessageHandle $handle ): void {
		$code = $handle->getCode();
		if ( !self::isValidLanguage( $code ) ) {
			return;
		}
		$groups = self::getSortedGroupsForClearing( $handle->getGroupIds() );
		self::internalClearGroups( $code, $groups, 0 );
	}

	/**
	 * Recalculate stats for given group(s).
	 *
	 * @param string|string[] $id Message group ids.
	 * @param int $flags Combination of FLAG_* constants.
	 */
	public static function clearGroup( $id, int $flags = 0 ): void {
		$languages = self::getLanguages();
		$groups = self::getSortedGroupsForClearing( (array)$id );

		// Do one language at a time, to save memory
		foreach ( $languages as $code ) {
			self::internalClearGroups( $code, $groups, $flags );
		}
	}

	/**
	 * Fetch aggregated statistics for all languages across groups. The stats are cached
	 * in the WANObjectCache, and recalculated on the fly if the values are stale.
	 * The statistics may lag behind the actuals due to extra and missing values
	 * @return array[] ( Language Code => Language Stats )
	 */
	public static function getApproximateLanguageStats(): array {
		$cache = MediaWikiServices::getInstance()->getMainWANObjectCache();
		return $cache->getWithSetCallback(
			self::LANGUAGE_STATS_KEY,
			WANObjectCache::TTL_INDEFINITE,
			function ( $oldValue, &$ttl, array &$setOpts ) {
				$dbr = Utilities::getSafeReadDB();
				$setOpts += Database::getCacheSetOptions( $dbr );

				return self::getAllLanguageStats();
			},
			[
				'checkKeys' => [ self::LANGUAGE_STATS_KEY ],
				'pcTTL' => $cache::TTL_PROC_SHORT,
			]
		);
	}

	private static function getAllLanguageStats(): array {
		$dbr = Utilities::getSafeReadDB();
		$res = $dbr->newSelectQueryBuilder()
			->table( self::TABLE )
			->select( [
				'tgs_lang',
				'tgs_translated' => 'SUM(tgs_translated)',
				'tgs_fuzzy' => 'SUM(tgs_fuzzy)',
				'tgs_total' => 'SUM(tgs_total)',
				'tgs_proofread' => 'SUM(tgs_proofread)'
			] )
			->groupBy( 'tgs_lang' )
			->caller( __METHOD__ )
			->fetchResultSet();

		$allLanguages = self::getLanguages();
		$languagesCodes = array_flip( $allLanguages );

		$allStats = [];
		foreach ( $res as $row ) {
			$allStats[ $row->tgs_lang ] = self::extractNumbers( $row );
			unset( $languagesCodes[ $row->tgs_lang ] );
		}

		// Fill empty stats for missing language codes
		foreach ( array_keys( $languagesCodes ) as $code ) {
			$allStats[ $code ] = self::getEmptyStats();
		}

		return $allStats;
	}

	/**
	 * Helper for clear and clearGroup that caches already loaded statistics.
	 * @param string $code
	 * @param MessageGroup[] $groups
	 * @param int $flags Combination of FLAG_* constants.
	 */
	private static function internalClearGroups( string $code, array $groups, int $flags ): void {
		$stats = [];
		foreach ( $groups as $group ) {
			// $stats is modified by reference
			self::forItemInternal( $stats, $group, $code, $flags );
		}
		self::queueUpdates( 0 );
	}

	/**
	 * Get sorted message groups ids that can be used for efficient clearing.
	 *
	 * To optimize performance, we first need to process all non-aggregate groups.
	 * Because aggregate groups are flattened (see self::expandAggregates), we can
	 * process them any order and allow use of cache, except for the aggregate groups
	 * itself.
	 * @param string[] $ids
	 * @return MessageGroup[]
	 */
	private static function getSortedGroupsForClearing( array $ids ): array {
		$groups = array_map( [ MessageGroups::class, 'getGroup' ], $ids );
		// Sanity: Remove any invalid groups
		$groups = array_filter( $groups );

		$sorted = [];
		$aggregates = [];
		foreach ( $groups as $group ) {
			if ( $group instanceof AggregateMessageGroup ) {
				$aggregates[$group->getId()] = $group;
			} else {
				$sorted[$group->getId()] = $group;
			}
		}

		return array_merge( $sorted, $aggregates );
	}

	/**
	 * Get list of supported languages for statistics.
	 * @return string[]
	 */
	public static function getLanguages(): array {
		if ( self::$languages === null ) {
			$languages = array_keys( Utilities::getLanguageNames( 'en' ) );
			sort( $languages );
			self::$languages = $languages;
		}

		return self::$languages;
	}

	/**
	 * Use this to extract results returned from selectRowsIdLang. You must pass the
	 * message group ids you want to retrieve. Entries that do not match are not returned.
	 * @param iterable $res Database result object
	 * @param string[] $ids List of message group ids
	 * @param array[] $stats Optional array to append results to.
	 * @return array[]
	 */
	private static function extractResults( iterable $res, array $ids, array $stats = [] ): array {
		// Map the internal ids back to real ids
		$idMap = array_combine( array_map( [ self::class, 'getDatabaseIdForGroupId' ], $ids ), $ids );

		foreach ( $res as $row ) {
			if ( !isset( $idMap[$row->tgs_group] ) ) {
				// Stale entry, ignore for now
				// TODO: Schedule for purge
				continue;
			}

			$realId = $idMap[$row->tgs_group];
			$stats[$realId][$row->tgs_lang] = self::extractNumbers( $row );
		}

		return $stats;
	}

	/** Returns an array of needed database fields. */
	private static function extractNumbers( stdClass $row ): array {
		return [
			self::TOTAL => (int)$row->tgs_total,
			self::TRANSLATED => (int)$row->tgs_translated,
			self::FUZZY => (int)$row->tgs_fuzzy,
			self::PROOFREAD => (int)$row->tgs_proofread,
		];
	}

	/**
	 * @param string $languageCode
	 * @param array[] $stats
	 * @param int $flags Combination of FLAG_* constants.
	 * @return array[]
	 */
	private static function forLanguageInternal( string $languageCode, array $stats, int $flags ): array {
		$groups = MessageGroups::singleton()->getGroups();

		$ids = array_keys( $groups );
		$res = self::selectRowsIdLang( null, [ $languageCode ], $flags );
		$stats = self::extractResults( $res, $ids, $stats );

		foreach ( $groups as $id => $group ) {
			if ( isset( $stats[$id][$languageCode] ) ) {
				continue;
			}
			$stats[$id][$languageCode] = self::forItemInternal( $stats, $group, $languageCode, $flags );
		}

		return $stats;
	}

	/** @return MessageGroup[] */
	private static function expandAggregates( AggregateMessageGroup $agg ): array {
		$flattened = [];

		foreach ( $agg->getGroups() as $group ) {
			if ( $group instanceof AggregateMessageGroup ) {
				$flattened += self::expandAggregates( $group );
			} else {
				$flattened[$group->getId()] = $group;
			}
		}

		return $flattened;
	}

	/**
	 * @param MessageGroup $group
	 * @param array[] $stats
	 * @param int $flags Combination of FLAG_* constants.
	 * @return array[]
	 */
	private static function forGroupInternal( MessageGroup $group, array $stats, int $flags ): array {
		$id = $group->getId();

		$res = self::selectRowsIdLang( [ $id ], null, $flags );
		$stats = self::extractResults( $res, [ $id ], $stats );

		// Go over each language filling missing entries
		$languages = self::getLanguages();
		foreach ( $languages as $code ) {
			if ( isset( $stats[$id][$code] ) ) {
				continue;
			}
			$stats[$id][$code] = self::forItemInternal( $stats, $group, $code, $flags );
		}

		// This is for sorting the values added later in correct order
		foreach ( array_keys( $stats ) as $key ) {
			ksort( $stats[$key] );
		}

		return $stats;
	}

	/**
	 * Fetch rows from the database. Use extractResults to process this value.
	 * @param ?string[] $ids List of message group ids
	 * @param ?string[] $codes List of language codes
	 * @param int $flags Combination of FLAG_* constants.
	 * @return iterable Database result object
	 */
	private static function selectRowsIdLang( ?array $ids, ?array $codes, int $flags ): iterable {
		if ( $flags & self::FLAG_NO_CACHE ) {
			return [];
		}

		$conditions = [];
		if ( $ids !== null ) {
			$dbids = array_map( [ self::class, 'getDatabaseIdForGroupId' ], $ids );
			$conditions['tgs_group'] = $dbids;
		}

		if ( $codes !== null ) {
			$conditions['tgs_lang'] = $codes;
		}

		$dbr = Utilities::getSafeReadDB();
		return $dbr->newSelectQueryBuilder()
			->select( '*' )
			->from( self::TABLE )
			->where( $conditions )
			->caller( __METHOD__ )
			->fetchResultSet();
	}

	/**
	 * @param array[] &$stats
	 * @param MessageGroup $group
	 * @param string $languageCode
	 * @param int $flags Combination of FLAG_* constants.
	 * @return null[]|int[]
	 */
	private static function forItemInternal(
		array &$stats,
		MessageGroup $group,
		string $languageCode,
		int $flags
	): array {
		$id = $group->getId();

		if ( $flags & self::FLAG_CACHE_ONLY ) {
			$stats[$id][$languageCode] = self::getUnknownStats();
			return $stats[$id][$languageCode];
		}

		// It may happen that caches are requested repeatedly for a group before we get a chance
		// to write the values to the database. Check for queued updates first. This has the
		// benefit of avoiding duplicate rows for inserts. Ideally this would be checked before we
		// query the database for missing values. This code is somewhat ugly as it needs to
		// reverse engineer the values from the row format.
		$databaseGroupId = self::getDatabaseIdForGroupId( $id );
		$uniqueKey = "$databaseGroupId|$languageCode";
		$queuedValue = self::$updates[$uniqueKey] ?? null;
		if ( $queuedValue && !( $flags & self::FLAG_NO_CACHE ) ) {
			return [
				self::TOTAL => $queuedValue['tgs_total'],
				self::TRANSLATED => $queuedValue['tgs_translated'],
				self::FUZZY => $queuedValue['tgs_fuzzy'],
				self::PROOFREAD => $queuedValue['tgs_proofread'],
			];
		}

		if ( $group instanceof AggregateMessageGroup ) {
			$aggregates = self::calculateAggregateGroup( $stats, $group, $languageCode, $flags );
		} else {
			$aggregates = self::calculateGroup( $group, $languageCode );
		}
		// Cache for use in subsequent forItemInternal calls
		$stats[$id][$languageCode] = $aggregates;

		// Don't add nulls to the database, causes annoying warnings
		if ( $aggregates[self::TOTAL] === null ) {
			return $aggregates;
		}

		self::$updates[$uniqueKey] = [
			'tgs_group' => $databaseGroupId,
			'tgs_lang' => $languageCode,
			'tgs_total' => $aggregates[self::TOTAL],
			'tgs_translated' => $aggregates[self::TRANSLATED],
			'tgs_fuzzy' => $aggregates[self::FUZZY],
			'tgs_proofread' => $aggregates[self::PROOFREAD],
		];

		// For big and lengthy updates, attempt some interim saves. This might not have
		// any effect, because writes to the database may be deferred.
		if ( count( self::$updates ) % 100 === 0 ) {
			self::queueUpdates( $flags );
		}

		return $aggregates;
	}

	private static function calculateAggregateGroup(
		array &$stats,
		AggregateMessageGroup $group,
		string $code,
		int $flags
	): array {
		$aggregates = self::getEmptyStats();

		$expanded = self::expandAggregates( $group );
		$subGroupIds = array_keys( $expanded );

		// Performance: if we have per-call cache of stats, do not query them again.
		foreach ( $subGroupIds as $index => $sid ) {
			if ( isset( $stats[$sid][$code] ) ) {
				unset( $subGroupIds[ $index ] );
			}
		}

		if ( $subGroupIds !== [] ) {
			$res = self::selectRowsIdLang( $subGroupIds, [ $code ], $flags );
			$stats = self::extractResults( $res, $subGroupIds, $stats );
		}

		$messageGroupMetadata = Services::getInstance()->getMessageGroupMetadata();
		foreach ( $expanded as $sid => $subgroup ) {
			// Discouraged groups may belong to another group, usually if there
			// is an aggregate group for all translatable pages. In that case
			// calculate and store the statistics, but don't count them as part of
			// the aggregate group, so that the numbers in Special:LanguageStats
			// add up. The statistics for discouraged groups can still be viewed
			// through Special:MessageGroupStats.
			if ( !isset( $stats[$sid][$code] ) ) {
				$stats[$sid][$code] = self::forItemInternal( $stats, $subgroup, $code, $flags );
			}

			if ( !$messageGroupMetadata->isExcluded( $sid, $code ) ) {
				$aggregates = self::multiAdd( $aggregates, $stats[$sid][$code] );
			}
		}

		return $aggregates;
	}

	public static function multiAdd( array $a, array $b ): array {
		if ( $a[0] === null || $b[0] === null ) {
			return array_fill( 0, count( $a ), null );
		}
		foreach ( $a as $i => &$v ) {
			$v += $b[$i];
		}

		return $a;
	}

	/**
	 * @param MessageGroup $group
	 * @param string $languageCode
	 * @return int[] ( total, translated, fuzzy, proofread )
	 */
	private static function calculateGroup( MessageGroup $group, string $languageCode ): array {
		global $wgTranslateDocumentationLanguageCode;
		// Calculate if missing and store in the db
		$collection = $group->initCollection( $languageCode );

		if (
			$languageCode === $wgTranslateDocumentationLanguageCode
			&& $group instanceof FileBasedMessageGroup
		) {
			$cache = $group->getMessageGroupCache( $group->getSourceLanguage() );
			if ( $cache->exists() ) {
				$template = $cache->getExtra()['TEMPLATE'] ?? [];
				$infile = [];
				foreach ( $template as $key => $data ) {
					if ( isset( $data['comments']['.'] ) ) {
						$infile[$key] = '1';
					}
				}
				$collection->setInFile( $infile );
			}
		}

		return self::getStatsForCollection( $collection );
	}

	private static function queueUpdates( int $flags ): void {
		$mwInstance = MediaWikiServices::getInstance();
		if ( self::$updates === [] || $mwInstance->getReadOnlyMode()->isReadOnly() ) {
			return;
		}

		$lb = $mwInstance->getDBLoadBalancer();
		$dbw = $lb->getConnection( DB_PRIMARY ); // avoid connecting yet
		$callers = wfGetAllCallers( 50 );
		$functionName = __METHOD__;
		$callback = static function ( IDatabase $dbw, $method ) use ( $callers, $mwInstance ) {
			// This path should only be hit during web requests
			if ( count( self::$updates ) > 100 ) {
				$groups = array_unique( array_column( self::$updates, 'tgs_group' ) );
				LoggerFactory::getInstance( LogNames::MAIN )->warning(
					"Huge translation update of {count} rows for group(s) {groups}",
					[
						'count' => count( self::$updates ),
						'groups' => implode( ', ', $groups ),
						'callers' => $callers,
					]
				);
			}

			$dbw->newReplaceQueryBuilder()
				->replaceInto( self::TABLE )
				->uniqueIndexFields( [ 'tgs_group', 'tgs_lang' ] )
				->rows( array_values( self::$updates ) )
				->caller( $method )
				->execute();
			self::$updates = [];

			$mwInstance->getMainWANObjectCache()->touchCheckKey( self::LANGUAGE_STATS_KEY );
		};
		$updateOp = static function () use ( $dbw, $functionName, $callback ) {
			// Maybe another deferred update already processed these
			if ( self::$updates === [] ) {
				return;
			}

			$lockName = 'MessageGroupStats:updates';
			if ( !$dbw->lock( $lockName, $functionName, 1 ) ) {
				$groups = array_unique( array_column( self::$updates, 'tgs_group' ) );
				LoggerFactory::getInstance( LogNames::MAIN )->warning(
					'Message group stats update of {count} rows failed for group(s) {groups} due to lock',
					[
						'count' => count( self::$updates ),
						'groups' => implode( ', ', $groups ),
					]
				);

				return; // raced out
			}

			$dbw->commit( $functionName, 'flush' );
			$callback( $dbw, $functionName );
			$dbw->commit( $functionName, 'flush' );

			$dbw->unlock( $lockName, $functionName );
		};

		if ( $flags & self::FLAG_IMMEDIATE_WRITES ) {
			$updateOp();
		} else {
			DeferredUpdates::addCallableUpdate( $updateOp );
		}
	}

	public static function getDatabaseIdForGroupId( string $id ): string {
		// The column is 100 bytes long, but we don't need to use it all
		if ( strlen( $id ) <= 72 ) {
			return $id;
		}

		$hash = hash( 'sha256', $id, /*asHex*/false );
		return substr( $id, 0, 50 ) . '||' . substr( $hash, 0, 20 );
	}

	/** @return int[] */
	public static function getStatsForCollection( MessageCollection $collection ): array {
		$collection->filter( MessageCollection::FILTER_IGNORED, MessageCollection::EXCLUDE_MATCHING );
		$collection->filterUntranslatedOptional();
		// Store the count of real messages for later calculation.
		$total = count( $collection );

		// Count fuzzy first.
		$collection->filter( MessageCollection::FILTER_FUZZY, MessageCollection::EXCLUDE_MATCHING );
		$fuzzy = $total - count( $collection );

		// Count the completed translations.
		$collection->filter( MessageCollection::FILTER_HAS_TRANSLATION, MessageCollection::INCLUDE_MATCHING );
		$translated = count( $collection );

		// Count how many of the completed translations
		// have been proofread
		$collection->filter( MessageCollection::FILTER_REVIEWER, MessageCollection::INCLUDE_MATCHING );
		$proofread = count( $collection );

		return [
			self::TOTAL => $total,
			self::TRANSLATED => $translated,
			self::FUZZY => $fuzzy,
			self::PROOFREAD => $proofread,
		];
	}
}
