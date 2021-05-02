<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Statistics;

use stdClass;

/**
 * Interface for producing different kinds of graphs.
 * The graphs are based on data queried from the database.
 *
 * @ingroup Stats
 * @license GPL-2.0-or-later
 * @since 2010.07
 */
interface TranslationStatsInterface {
	/**
	 * Constructor. The implementation can access the graph options, but not
	 * define new ones.
	 * @param TranslationStatsGraphOptions $opts
	 */
	public function __construct( TranslationStatsGraphOptions $opts );

	/**
	 * Query details that the graph must fill.
	 * @param array &$tables Empty list. Append table names.
	 * @param array &$fields Empty list. Append field names.
	 * @param array &$conds Empty array. Append select conditions.
	 * @param string &$type Append graph type (used to identify queries).
	 * @param array &$options Empty array. Append extra query options.
	 * @param array &$joins Empty array. Append extra join conditions.
	 * @param string $start Precalculated start cutoff timestamp
	 * @param string $end Precalculated end cutoff timestamp
	 */
	public function preQuery( &$tables, &$fields, &$conds, &$type, &$options, &$joins, $start, $end );

	/**
	 * Return the indexes which this result contributes to.
	 * Return 'all' if only one variable is measured. Return false if none.
	 * @param stdClass $row Database Result Row
	 * @return array|false
	 */
	public function indexOf( $row );

	/**
	 * Return the names of the variables being measured.
	 * Return 'all' if only one variable is measured. Must match indexes
	 * returned by indexOf() and contain them all.
	 * @return string[]
	 */
	public function labels();

	/**
	 * Return the timestamp associated with this result row.
	 * @param stdClass $row Database Result Row
	 * @return string Timestamp.
	 */
	public function getTimestamp( $row );

	/**
	 * Return time formatting string.
	 * @see Language::sprintfDate()
	 * @return string
	 */
	public function getDateFormat();
}
