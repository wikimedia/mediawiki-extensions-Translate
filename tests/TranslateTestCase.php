<?php
/**
 * Handy wrapper to get "clean" state
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2012, Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

/**
 * @group Database
 */
class TranslateTestCase extends MediaWikiTestCase {

	protected $store = array();
	protected $storables = array(
		'wgTranslateEC',
		'wgTranslateAC',
		'wgTranslateCC',
		'wgHooks',
		'wgTranslateMessageIndex',
		'wgGroupPermissions',
		'wgEnablePageTranslation',
		'wgTranslateGroupFiles',
		'wgRC2UDPAddress',
		'wgTranslateWorkflowStates',
	);

	public function setUp() {
		foreach ( $this->storables as $var ) {
			global $$var;
			$this->store[$var] = $$var;
		}
		$this->clean();
	}

	public function clean() {
		foreach ( $this->storables as $var ) {
			global $$var;
		}
		$wgTranslateMessageIndex = array( 'DatabaseMessageIndex' );
		$wgGroupPermissions = array();
		$wgTranslateAC = $wgTranslateCC = $wgTranslateEC = array();
		$wgEnablePageTranslation = false;
		$wgTranslateGroupFiles = array();
		$wgHooks['TranslatePostInitGroups'] = array();
		$wgRC2UDPAddress = false;
		$wgTranslateWorkflowStates = false;

		MessageGroups::clearCache();
		MessageIndexRebuildJob::newJob()->run();
	}

	public function tearDown() {
		$this->clean();
		foreach ( $this->storables as $var ) {
			global $$var;
			$$var = $this->store[$var];
		}
	}
}