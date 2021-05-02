<?php
/**
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */

namespace MediaWiki\Extension\Translate\Statistics;

use Maintenance;
use MediaWiki\Extension\Translate\Services;

/** @since 2020.04 */
class UpdateTranslatorActivityMaintenanceScript extends Maintenance {
	public function __construct() {
		parent::__construct();
		$this->addDescription( 'Updates cached translator activity statistics' );
		$this->requireExtension( 'Translate' );
	}

	public function execute() {
		Services::getInstance()->getTranslatorActivity()->updateAllLanguages();
		$this->output( "Done.\n" );
	}
}
