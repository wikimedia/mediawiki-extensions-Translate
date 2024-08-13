<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Statistics;

use stdClass;
use Wikimedia\Rdbms\IReadableDatabase;
use Wikimedia\Rdbms\SelectQueryBuilder;

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
	 * Return the SelectQueryBuilder to fetch the data needed for the graph
	 * @param IReadableDatabase $database
	 * @param string $caller Appended with the graph type and passed to `->caller()` (used to identify queries).
	 * @param string $start Precalculated start cutoff timestamp
	 * @param string|null $end Precalculated end cutoff timestamp
	 */
	public function createQueryBuilder(
		IReadableDatabase $database,
		string $caller,
		string $start,
		?string $end
	): SelectQueryBuilder;

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
