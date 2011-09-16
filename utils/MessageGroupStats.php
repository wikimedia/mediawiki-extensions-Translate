<?php
/**
 * This file aims to provide efficient mechanism for fetching translation completion stats.
 *
 * @file
 * @author Wikia http://trac.wikia-code.com/browser/wikia/trunk/extensions/wikia/TranslationStatistics
 * @author Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 3 or later
 */

/**
 * This class abstract MessageGroup statistics calculation and storing.
 * You can access stats easily per language or per group.
 * Stat array for each item is of format array( total, translate, fuzzy ).
 */
class MessageGroupStats {
	/// Name of the database table
	const TABLE = 'translate_groupstats';

	/// @var float
	protected static $timeStart = null;
	/// @var float
	protected static $limit = null;

	/**
	 * Set the maximum time statistics are calculated.
	 * If the time limit is exceeded, the missing
	 * entries will be null.
	 * @param $limit float time in seconds
	 */
	public static function setTimeLimit( $limit ) {
		self::$timeStart = microtime( true );
		self::$limit = $limit;
	}

	/**
	 * Returns stats for given group in given language.
	 * @param $id string Group id
	 * @param $code string Language code
	 * @return Array
	 */
	public static function forItem( $id, $code ) {
		$stats = array();
		$res = self::selectRowsIdLang( $id, $code );
		$stats = self::extractResults( $res, $stats );

		$group = MessageGroups::getGroup( $id );

		if ( !isset( $stats[$id][$code] ) ) {
			$stats[$id][$code] = self::forItemInternal( $stats, $group, $code );
		}

		return $stats[$id][$code];
	}

	/**
	 * Returns stats for all groups in given language.
	 * @param $code string Language code
	 * @return Array
	 */
	public static function forLanguage( $code ) {
		$stats = self::forLanguageInternal( $code );
		$flattened = array();
		foreach ( $stats as $group => $languages ) {
			$flattened[$group] = $languages[$code];
		}
		return $flattened;
	}

	/**
	 * Returns stats for all languages in given group.
	 * @param $code string Group id
	 * @return Array
	 */
	public static function forGroup( $id ) {
		$group = MessageGroups::getGroup( $id );
		if ( $group === null ) {
			return array();
		}
		$stats = self::forGroupInternal( $group );
		return $stats[$id];
	}

	/**
	 * Returns stats for all group in all languages.
	 * Might be slow, might use lots of memory.
	 * Returns two dimensional array indexed by group and language.
	 * @return Array
	 */
	public static function forEverything() {
		$groups = MessageGroups::singleton()->getGroups();
		$stats = array();
		foreach ( $groups as $g ) {
			$stats = self::forGroupInternal( $g, $stats );
		}
		return $stats;
	}

	public static function clear( MessageHandle $handle ) {
		$dbw = wfGetDB( DB_MASTER );
		$conds = array(
			'tgs_group' => $handle->getGroupIds(),
			'tgs_lang' => $handle->getCode(),
		);

		$dbw->delete( self::TABLE, $conds, __METHOD__ );
	}

	public static function clearGroup( $id ) {
		if ( !count( $id ) ) return;
		$dbw = wfGetDB( DB_MASTER );
		$conds = array( 'tgs_group' => $id );
		$dbw->delete( self::TABLE, $conds, __METHOD__ );
	}

	public static function clearLanguage( $code ) {
		if ( !count( $code ) ) return;
		$dbw = wfGetDB( DB_MASTER );
		$conds = array( 'tgs_lang' => $code );
		$dbw->delete( self::TABLE, $conds, __METHOD__ );
	}

	/**
	 * Purges all cached stats.
	 */
	public static function clearAll() {
		$dbw = wfGetDB( DB_MASTER );
		$dbw->delete( self::TABLE, '*' );
	}

	protected static function extractResults( $res, $stats = array() ) {
		foreach ( $res as $row ) {
			$stats[$row->tgs_group][$row->tgs_lang] = self::extractNumbers( $row );
		}
		return $stats;
	}

	public static function update( MessageHandle $handle, $changes = array() ) {
		$dbw = wfGetDB( DB_MASTER );
		$conds = array(
			'tgs_group' => $handle->getGroupIds(),
			'tgs_lang' => $handle->getCode(),
		);

		$values = array();
		foreach ( array( 'total', 'translated', 'fuzzy' ) as $type ) {
			if ( !isset( $changes[$type] ) ) {
				$values[] = "tgs_$type=tgs_$type" .
					self::stringifyNumber( $changes[$type] );
			}
		}

		$dbw->update( self::TABLE, $values, $conds, __METHOD__ );
	}

	/**
	 * Returns an array of needed database fields.
	 */
	protected static function extractNumbers( $row ) {
		return array(
			$row->tgs_total,
			$row->tgs_translated,
			$row->tgs_fuzzy
		);
	}
 
	protected static function forLanguageInternal( $code, $stats = array() ) {
		$res = self::selectRowsIdLang( null, $code );
		$stats = self::extractResults( $res, $stats );

		$groups = MessageGroups::singleton()->getGroups();
		foreach ( $groups as $id => $group ) {
			if ( isset( $stats[$id][$code] ) ) continue;
			$stats[$id][$code] = self::forItemInternal( $stats, $group, $code );
		}
		return $stats;
	}

	protected static function forGroupInternal( $group, $stats = array() ) {
		$id = $group->getId();
		$res = self::selectRowsIdLang( $id, null );
		$stats = self::extractResults( $res, $stats );

		# Go over each language filling missing entries
		$languages = array_keys( Language::getLanguageNames() );
		foreach ( $languages as $code ) {
			if ( isset( $stats[$id][$code] ) ) continue;
			$stats[$id][$code] = self::forItemInternal( $stats, $group, $code );
		}
		
		return $stats;
	}

	protected static function selectRowsIdLang( $ids = null, $codes = null ) {
		$conds = array();
		if ( $ids !== null ) {
			$conds['tgs_group'] = $ids;
		}

		if ( $codes !== null ) {
			$conds['tgs_lang'] = $codes;
		}

		$dbr = wfGetDB( DB_SLAVE );
		$res = $dbr->select( self::TABLE, '*', $conds, __METHOD__ );
		return $res;
	}
 
	protected static function forItemInternal( &$stats, $group, $code ) {
		$id = $group->getId();

		if ( self::$timeStart !== null && ( microtime( true ) - self::$timeStart ) > self::$limit ) {
			return array( null, null, null );
		}

		// Might happen when called recursively
		if ( isset( $stats[$id][$code] ) ) {
			return $stats[$id][$code];
		}

		if ( $group instanceof AggregateMessageGroup ) {
			$aggregates = array( 0, 0, 0 );
			foreach ( $group->getGroups() as $sid => $sgroup ) {
				if ( !isset( $stats[$sid][$code] ) ) {
					$stats[$sid][$code] = self::forItemInternal( $stats, $sgroup, $code );
				}
				$aggregates = self::multiAdd( $aggregates, $stats[$sid][$code] );
			}
			$stats[$id] = $aggregates;
		} else {
			$aggregates = self::calculateGroup( $group, $code );
		}

		list( $total, $translated, $fuzzy ) = $aggregates;

		$data = array(
			'tgs_group' => $id,
			'tgs_lang' => $code,
			'tgs_total' => $total,
			'tgs_translated' => $translated,
			'tgs_fuzzy' => $fuzzy,
		);

		$dbw = wfGetDB( DB_MASTER );
		$dbw->insert(
			self::TABLE,
			$data,
			__METHOD__
		);

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

	protected static function calculateGroup( $group, $code ) {
		global $wgTranslateDocumentationLanguageCode;
		# Calculate if missing and store in the db
		$collection = $group->initCollection( $code );


		if ( $code === $wgTranslateDocumentationLanguageCode ) {
			$ffs = $group->getFFS();
			if ( $ffs instanceof GettextFFS ) {
				$template = $ffs->read( 'en' );
				$infile = array();
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

		return array( $total, $translated, $fuzzy );
	}

	/**
	 * Converts input to "+2" "-4" type of string.
	 * @param $number int
	 * @returns string
	 */
	protected static function stringifyNumber( $number ) {
		$number = intval( $number );
		return $number < 0 ? "$number" : "+$number";
	}
}
