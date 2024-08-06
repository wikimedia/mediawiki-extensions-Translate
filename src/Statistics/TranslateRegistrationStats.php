<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Statistics;

use Wikimedia\Rdbms\IReadableDatabase;

/**
 * Graph which provides statistics about amount of registered users in a given time.
 * @ingroup Stats
 * @license GPL-2.0-or-later
 * @since 2010.07
 */
class TranslateRegistrationStats extends TranslationStatsBase {
	public function preQuery(
		IReadableDatabase $database,
		&$tables,
		&$fields,
		&$conds,
		&$type,
		&$options,
		&$joins,
		$start,
		$end
	) {
		$tables = [ 'user' ];
		$fields = 'user_registration';
		$conds = self::makeTimeCondition( $database, 'user_registration', $start, $end );
		$type .= '-registration';
		$options = [];
		$joins = [];
	}

	public function getTimestamp( $row ) {
		return $row->user_registration;
	}
}
