<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Utilities;

use MediaWiki\Maintenance\Maintenance;

/**
 * Base maintenance script containing constants and methods used in multiple scripts
 * Hopefully the constants can be removed after https://phabricator.wikimedia.org/T271787 is fixed.
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */
abstract class BaseMaintenanceScript extends Maintenance {
	protected const OPTIONAL = false;
	protected const REQUIRED = true;
	protected const HAS_ARG = true;
	protected const NO_ARG = false;

	/**
	 * Converts a comma seperated list to an array. Removes empty strings and duplicate values.
	 * @return string[]
	 */
	protected static function commaList2Array( string $list ): array {
		return array_unique(
			array_filter(
				array_map( 'trim', explode( ',', $list ) ),
				'strlen'
			)
		);
	}
}
