<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Statistics;

/**
 * Graph which provides statistics about amount of registered users in a given time.
 * @ingroup Stats
 * @license GPL-2.0-or-later
 * @since 2010.07
 */
class TranslateRegistrationStats extends TranslationStatsBase {
	public function preQuery( &$tables, &$fields, &$conds, &$type, &$options, &$joins, $start, $end ) {
		$tables = 'user';
		$fields = 'user_registration';
		$conds = self::makeTimeCondition( 'user_registration', $start, $end );
		$type .= '-registration';
		$options = [];
		$joins = [];
	}

	public function getTimestamp( $row ) {
		return $row->user_registration;
	}
}
