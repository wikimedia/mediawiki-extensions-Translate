<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Statistics;

use Wikimedia\Rdbms\IReadableDatabase;
use Wikimedia\Rdbms\SelectQueryBuilder;

/**
 * Graph which provides statistics about amount of registered users in a given time.
 * @ingroup Stats
 * @license GPL-2.0-or-later
 * @since 2010.07
 */
class TranslateRegistrationStats extends TranslationStatsBase {

	public function createQueryBuilder( IReadableDatabase $database, string $caller ): SelectQueryBuilder {
		return $database->newSelectQueryBuilder()
			->table( 'user' )
			->fields( 'user_registration' )
			->caller( $caller . '-registration' );
	}

	public function getTimestampColumn(): string {
		return 'user_registration';
	}
}
