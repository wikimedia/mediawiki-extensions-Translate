<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Statistics;

use MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroups;
use stdClass;

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

	public function indexOf( stdClass $row ): ?array {
		return [ 'all' ];
	}

	public function labels(): array {
		return [ 'all' ];
	}

	public function getDateFormat(): string {
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
