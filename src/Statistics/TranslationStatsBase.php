<?php
declare( strict_types = 1 );

namespace MediaWiki\Extensions\Translate\Statistics;

use FormOptions;
use MessageGroups;

/**
 * Provides some hand default implementations for TranslationStatsInterface.
 * @ingroup Stats
 * @license GPL-2.0-or-later
 * @since 2010.07
 */
abstract class TranslationStatsBase implements TranslationStatsInterface {
	/** @var FormOptions Graph options. */
	protected $opts;

	public function __construct( FormOptions $opts ) {
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
		if ( $this->opts['scale'] === 'months' ) {
			$dateFormat = 'Y-m';
		} elseif ( $this->opts['scale'] === 'weeks' ) {
			$dateFormat = 'Y-\WW';
		} elseif ( $this->opts['scale'] === 'hours' ) {
			$dateFormat .= ';H';
		}

		return $dateFormat;
	}

	protected static function makeTimeCondition( $field, $start, $end ) {
		$db = wfGetDB( DB_REPLICA );

		$conds = [];
		if ( $start !== null ) {
			$conds[] = "$field >= '{$db->timestamp( $start )}'";
		}
		if ( $end !== null ) {
			$conds[] = "$field <= '{$db->timestamp( $end )}'";
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
