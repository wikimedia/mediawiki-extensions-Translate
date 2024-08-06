<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Statistics;

use MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroups;
use Wikimedia\Rdbms\IReadableDatabase;

/**
 * Provides some hand default implementations for TranslationStatsInterface.
 * @ingroup Stats
 * @license GPL-2.0-or-later
 * @since 2010.07
 */
abstract class TranslationStatsBase implements TranslationStatsInterface {
	protected TranslationStatsGraphOptions $opts;

	public function __construct( TranslationStatsGraphOptions $opts ) {
		$this->opts = $opts;
	}

	public function indexOf( $row ) {
		return [ 'all' ];
	}

	public function labels() {
		return [ 'all' ];
	}

	public function getDateFormat() {
		$dateFormat = 'Y-m-d';
		$scale = $this->opts->getValue( 'scale' );
		if ( $scale === 'years' ) {
			$dateFormat = 'Y';
		} elseif ( $scale === 'months' ) {
			$dateFormat = 'Y-m';
		} elseif ( $scale === 'weeks' ) {
			$dateFormat = 'Y-\WW';
		} elseif ( $scale === 'hours' ) {
			$dateFormat .= ';H';
		}

		return $dateFormat;
	}

	protected static function makeTimeCondition( IReadableDatabase $database, $field, $start, $end ) {
		$conds = [];
		if ( $start !== null ) {
			$conds[] = "$field >= " . $database->addQuotes( $database->timestamp( $start ) );
		}
		if ( $end !== null ) {
			$conds[] = "$field <= " . $database->addQuotes( $database->timestamp( $end ) );
		}

		return $conds;
	}

	/**
	 * @since 2012-03-05
	 * @param array $groupIds
	 * @return array
	 */
	protected static function namespacesFromGroups( $groupIds ) {
		$namespaces = [];
		foreach ( $groupIds as $id ) {
			$group = MessageGroups::getGroup( $id );
			if ( $group ) {
				$namespace = $group->getNamespace();
				$namespaces[$namespace] = true;
			}
		}

		return array_keys( $namespaces );
	}
}
