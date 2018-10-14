<?php
/**
 * This file aims to provide efficient mechanism for fetching translation completion stats.
 *
 * @file
 * @author Wikia (trac.wikia-code.com/browser/wikia/trunk/extensions/wikia/TranslationStatistics)
 * @author Niklas Laxström
 * @copyright Copyright © 2012-2013 Niklas Laxström
 * @license GPL-2.0-or-later
 */

use MediaWiki\MediaWikiServices;
use Wikimedia\Rdbms\IDatabase;

/**
 * This class abstract MessageGroup statistics calculation and storing.
 * You can access stats easily per language or per group.
 * Stat array for each item is of format array( total, translate, fuzzy ).
 *
 * @ingroup Stats MessageGroups
 */
class MessageGroupStats {
	/// Name of the database table
	const TABLE = 'translate_groupstats';

	const TOTAL = 0; ///< Array index
	const TRANSLATED = 1; ///< Array index
	const FUZZY = 2; ///< Array index
	const PROOFREAD = 3; ///< Array index

	/**
	 * @var float|null
	 */
	protected static $timeStart = null;

	/**
	 * @var float|null
	 */
	protected static $limit = null;

	/**
	 * @var array[]
	 */
	protected static $updates = [];

	/**
	 * Set the maximum time statistics are calculated.
	 * If the time limit is exceeded, the missing
	 * entries will be null.
	 * @param float $limit time in seconds
	 */
	public static function setTimeLimit( $limit ) {
		self::$timeStart = microtime( true );
		self::$limit = $limit;
	}

	/**
	 * Returns empty stats array. Useful because the number of elements
	 * may change.
	 * @return int[]
	 * @since 2012-09-21
	 */
	public static function getEmptyStats() {
		return [ 0, 0, 0, 0 ];
	}

	/**
	 * Returns empty stats array that indicates stats are incomplete or
	 * unknown.
	 * @return null[]
	 * @since 2013-01-02
	 */
	protected static function getUnknownStats() {
		return [ null, null, null, null ];
	}

	/**
	 * Returns stats for given group in given language.
	 * @param string $id Group id
	 * @param string $code Language code
	 * @return null[]|int[]
	 */
	public static function forItem( $id, $code ) {
		$res = self::selectRowsIdLang( [ $id ], $code );
		$stats = self::extractResults( $res, [ $id ] );

		/* In case some code calls this for dynamic groups, return the default
		 * values for unknown/incomplete stats. Calculating these numbers don't
		 * make sense for dynamic groups, and would just throw an exception. */
		$group = MessageGroups::getGroup( $id );
		if ( MessageGroups::isDynamic( $group ) ) {
			$stats[$id][$code] = self::getUnknownStats();
		}

		if ( !isset( $stats[$id][$code] ) ) {
			$stats[$id][$code] = self::forItemInternal( $stats, $group, $code );
		}

		self::queueUpdates();

		return $stats[$id][$code];
	}

	/**
	 * Returns stats for all groups in given language.
	 * @param string $code Language code
	 * @return array
	 */
	public static function forLanguage( $code ) {
		$stats = self::forLanguageInternal( $code );
		$flattened = [];
		foreach ( $stats as $group => $languages ) {
			$flattened[$group] = $languages[$code];
		}

		self::queueUpdates();

		return $flattened;
	}

	/**
	 * Returns stats for all languages in given group.
	 * @param string $id Group id
	 * @return array
	 */
	public static function forGroup( $id ) {
		$group = MessageGroups::getGroup( $id );
		if ( $group === null ) {
			return [];
		}
		$stats = self::forGroupInternal( $group );

		self::queueUpdates();

		return $stats[$id];
	}

	/**
	 * Returns stats for all group in all languages.
	 * Might be slow, might use lots of memory.
	 * Returns two dimensional array indexed by group and language.
	 * @return array
	 */
	public static function forEverything() {
		$groups = MessageGroups::singleton()->getGroups();
		$stats = [];
		foreach ( $groups as $g ) {
			$stats = self::forGroupInternal( $g, $stats );
		}

		self::queueUpdates();

		return $stats;
	}

	/**
	 * Clears the cache for all groups associated with the message.
	 *
	 * Hook: TranslateEventTranslationReview
	 * @param MessageHandle $handle
	 */
	public static function clear( MessageHandle $handle ) {
		$code = $handle->getCode();
		$dbids = array_map( 'self::getDatabaseIdForGroupId', $handle->getGroupIds() );

		$dbw = wfGetDB( DB_MASTER );
		$conds = [ 'tgs_group' => $dbids, 'tgs_lang' => $code ];
		$dbw->delete( self::TABLE, $conds, __METHOD__ );
		wfDebugLog( 'messagegroupstats', 'Cleared ' . serialize( $conds ) );
	}

	public static function clearGroup( $id ) {
		// T206904: Fix countable error for group id
		// NOTE: An empty string ('') is not a valid group id
		if ( $id === [] ) {
			return;
		}

		$dbids = array_map( 'self::getDatabaseIdForGroupId', (array)$id );

		$dbw = wfGetDB( DB_MASTER );
		$conds = [ 'tgs_group' => $dbids ];
		$dbw->delete( self::TABLE, $conds, __METHOD__ );
		wfDebugLog( 'messagegroupstats', 'Cleared ' . serialize( $conds ) );
	}

	public static function clearLanguage( $code ) {
		if ( !count( $code ) ) {
			return;
		}
		$dbw = wfGetDB( DB_MASTER );
		$conds = [ 'tgs_lang' => $code ];
		$dbw->delete( self::TABLE, $conds, __METHOD__ );
		wfDebugLog( 'messagegroupstats', 'Cleared ' . serialize( $conds ) );
	}

	/**
	 * Purges all cached stats.
	 */
	public static function clearAll() {
		$dbw = wfGetDB( DB_MASTER );
		$dbw->delete( self::TABLE, '*' );
		wfDebugLog( 'messagegroupstats', 'Cleared everything :(' );
	}

	/**
	 * Use this to extract results returned from selectRowsIdLang. You must pass the
	 * message group ids you want to retrieve. Entries that do not match are not returned.
	 *
	 * @param Traversable $res Database result object
	 * @param string[] $ids List of message group ids
	 * @param array[] $stats Optional array to append results to.
	 * @return array[]
	 */
	protected static function extractResults( $res, array $ids, array $stats = [] ) {
		// Map the internal ids back to real ids
		$idmap = array_combine( array_map( 'self::getDatabaseIdForGroupId', $ids ), $ids );

		foreach ( $res as $row ) {
			if ( !isset( $idmap[$row->tgs_group] ) ) {
				// Stale entry, ignore for now
				// TODO: Schedule for purge
				continue;
			}

			$realId = $idmap[$row->tgs_group];
			$stats[$realId][$row->tgs_lang] = self::extractNumbers( $row );
		}

		return $stats;
	}

	public static function update( MessageHandle $handle, array $changes = [] ) {
		$dbids = array_map( 'self::getDatabaseIdForGroupId', $handle->getGroupIds() );

		$dbw = wfGetDB( DB_MASTER );
		$conds = [
			'tgs_group' => $dbids,
			'tgs_lang' => $handle->getCode(),
		];

		$values = [];
		foreach ( [ 'total', 'translated', 'fuzzy', 'proofread' ] as $type ) {
			if ( isset( $changes[$type] ) ) {
				$values[] = "tgs_$type=tgs_$type" .
					self::stringifyNumber( $changes[$type] );
			}
		}

		$dbw->update( self::TABLE, $values, $conds, __METHOD__ );
	}

	/**
	 * Returns an array of needed database fields.
	 * @param stdClass $row
	 * @return array
	 */
	protected static function extractNumbers( $row ) {
		return [
			self::TOTAL => (int)$row->tgs_total,
			self::TRANSLATED => (int)$row->tgs_translated,
			self::FUZZY => (int)$row->tgs_fuzzy,
			self::PROOFREAD => (int)$row->tgs_proofread,
		];
	}

	/**
	 * @param string $code Language code
	 * @param array[] $stats
	 * @return array[]
	 */
	protected static function forLanguageInternal( $code, array $stats = [] ) {
		$groups = MessageGroups::singleton()->getGroups();

		$ids = array_keys( $groups );
		$res = self::selectRowsIdLang( null, $code );
		$stats = self::extractResults( $res, $ids, $stats );

		foreach ( $groups as $id => $group ) {
			if ( isset( $stats[$id][$code] ) ) {
				continue;
			}
			$stats[$id][$code] = self::forItemInternal( $stats, $group, $code );
		}

		return $stats;
	}

	/**
	 * @param AggregateMessageGroup $agg
	 * @return mixed
	 */
	protected static function expandAggregates( AggregateMessageGroup $agg ) {
		$flattened = [];

		/** @var MessageGroup|AggregateMessageGroup $group */
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
	 * @return array[]
	 */
	protected static function forGroupInternal( $group, array $stats = [] ) {
		$id = $group->getId();
		$res = self::selectRowsIdLang( [ $id ], null );
		$stats = self::extractResults( $res, [ $id ], $stats );

		# Go over each language filling missing entries
		$languages = array_keys( TranslateUtils::getLanguageNames( 'en' ) );
		// This is for calculating things in correct order
		sort( $languages );
		foreach ( $languages as $code ) {
			if ( isset( $stats[$id][$code] ) ) {
				continue;
			}
			$stats[$id][$code] = self::forItemInternal( $stats, $group, $code );
		}

		// This is for sorting the values added later in correct order
		foreach ( array_keys( $stats ) as $key ) {
			ksort( $stats[$key] );
		}

		return $stats;
	}

	/**
	 * Fetch rows from the database. Use extractResults to process this value.
	 *
	 * @param null|string[] $ids List of message group ids
	 * @param null|string[] $codes List of language codes
	 * @return Traversable Database result object
	 */
	protected static function selectRowsIdLang( $ids = null, $codes = null ) {
		$conds = [];
		if ( $ids !== null ) {
			$dbids = array_map( 'self::getDatabaseIdForGroupId', $ids );
			$conds['tgs_group'] = $dbids;
		}

		if ( $codes !== null ) {
			$conds['tgs_lang'] = $codes;
		}

		$dbr = TranslateUtils::getSafeReadDB();
		$res = $dbr->select( self::TABLE, '*', $conds, __METHOD__ );

		return $res;
	}

	/**
	 * @param array[] &$stats
	 * @param MessageGroup $group
	 * @param string $code Language code
	 *
	 * @return null[]|int[]
	 */
	protected static function forItemInternal( &$stats, $group, $code ) {
		$id = $group->getId();

		if ( self::$timeStart !== null && ( microtime( true ) - self::$timeStart ) > self::$limit ) {
			$stats[$id][$code] = self::getUnknownStats();
			return $stats[$id][$code];
		}

		if ( $group instanceof AggregateMessageGroup ) {
			$aggregates = self::getEmptyStats();

			$expanded = self::expandAggregates( $group );
			if ( $expanded === [] ) {
				return $aggregates;
			}

			$subGroupIds = array_keys( $expanded );
			$res = self::selectRowsIdLang( $subGroupIds, $code );
			$stats = self::extractResults( $res, $subGroupIds, $stats );

			foreach ( $expanded as $sid => $subgroup ) {
				# Discouraged groups may belong to another group, usually if there
				# is an aggregate group for all translatable pages. In that case
				# calculate and store the statistics, but don't count them as part of
				# the aggregate group, so that the numbers in Special:LanguageStats
				# add up. The statistics for discouraged groups can still be viewed
				# through Special:MessageGroupStats.
				if ( !isset( $stats[$sid][$code] ) ) {
					$stats[$sid][$code] = self::forItemInternal( $stats, $subgroup, $code );
				}

				$include = Hooks::run( 'Translate:MessageGroupStats:isIncluded', [ $sid, $code ] );
				if ( $include ) {
					$aggregates = self::multiAdd( $aggregates, $stats[$sid][$code] );
				}
			}
			$stats[$id][$code] = $aggregates;
		} else {
			$aggregates = self::calculateGroup( $group, $code );
		}

		// Don't add nulls to the database, causes annoying warnings
		if ( $aggregates[self::TOTAL] === null ) {
			return $aggregates;
		}

		self::$updates[] = [
			'tgs_group' => self::getDatabaseIdForGroupId( $id ),
			'tgs_lang' => $code,
			'tgs_total' => $aggregates[self::TOTAL],
			'tgs_translated' => $aggregates[self::TRANSLATED],
			'tgs_fuzzy' => $aggregates[self::FUZZY],
			'tgs_proofread' => $aggregates[self::PROOFREAD],
		];

		return $aggregates;
	}

	public static function multiAdd( &$a, $b ) {
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
	 * @param string $code Language code
	 * @return int[] ( total, translated, fuzzy, proofread )
	 */
	protected static function calculateGroup( $group, $code ) {
		global $wgTranslateDocumentationLanguageCode;
		// Calculate if missing and store in the db
		$collection = $group->initCollection( $code );

		if ( $code === $wgTranslateDocumentationLanguageCode ) {
			$ffs = $group->getFFS();
			if ( $ffs instanceof GettextFFS ) {
				$template = $ffs->read( 'en' );
				$infile = [];
				foreach ( $template['TEMPLATE'] as $key => $data ) {
					if ( isset( $data['comments']['.'] ) ) {
						$infile[$key] = '1';
					}
				}
				$collection->setInFile( $infile );
			}
		}

		$collection->filter( 'ignored' );
		$collection->filter( 'optional' );
		// Store the count of real messages for later calculation.
		$total = count( $collection );

		// Count fuzzy first.
		$collection->filter( 'fuzzy' );
		$fuzzy = $total - count( $collection );

		// Count the completed translations.
		$collection->filter( 'hastranslation', false );
		$translated = count( $collection );

		// Count how many of the completed translations
		// have been proofread
		$collection->filter( 'reviewer', false );
		$proofread = count( $collection );

		return [
			self::TOTAL => $total,
			self::TRANSLATED => $translated,
			self::FUZZY => $fuzzy,
			self::PROOFREAD => $proofread,
		];
	}

	/**
	 * Converts input to "+2" "-4" type of string.
	 * @param int $number
	 * @return string
	 */
	protected static function stringifyNumber( $number ) {
		$number = (int)$number;

		return $number < 0 ? "$number" : "+$number";
	}

	protected static function queueUpdates() {
		if ( wfReadOnly() ) {
			return;
		}

		if ( !count( self::$updates ) ) {
			return;
		}

		$lb = MediaWikiServices::getInstance()->getDBLoadBalancer();
		$dbw = $lb->getLazyConnectionRef( DB_MASTER ); // avoid connecting yet
		$table = self::TABLE;
		$updates = &self::$updates;

		self::queueWithLock(
			$dbw,
			'updates',
			__METHOD__,
			function ( IDatabase $dbw, $method ) use( $table, &$updates ) {
				$dbw->insert(
					$table,
					$updates,
					$method,
					[ 'IGNORE' ]
				);

				$updates = [];
			}
		);
	}

	protected static function queueWithLock( IDatabase $dbw, $key, $method, $callback ) {
		$fname = __METHOD__;
		DeferredUpdates::addCallableUpdate( function () use ( $dbw, $key, $method, $callback, $fname ) {
			$lockName = 'MessageGroupStats:' . $key;
			if ( !$dbw->lock( $lockName, $fname, 1 ) ) {
				return; // raced out
			}

			$dbw->commit( $fname, 'flush' );
			call_user_func( $callback, $dbw, $method );
			$dbw->commit( $fname, 'flush' );

			$dbw->unlock( $lockName, $fname );
		} );
	}

	public static function getDatabaseIdForGroupId( $id ) {
		// The column is 100 bytes long, but we don't need to use it all
		if ( strlen( $id ) <= 72 ) {
			return $id;
		}

		$hash = hash( 'sha256', $id, /*asHex*/false );
		$dbid = substr( $id, 0, 50 ) . '||' . substr( $hash, 0, 20 );
		return $dbid;
	}
}
