<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Utilities;

use Maintenance;

/**
 * Constants for making code for maintenance scripts more readable.
 *
 * Hopefully temporary until https://phabricator.wikimedia.org/T271787 is fixed.
 *
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */
abstract class BaseMaintenanceScript extends Maintenance {
	protected const OPTIONAL = false;
	protected const REQUIRED = true;
	protected const HAS_ARG = true;
	protected const NO_ARG = false;
}
