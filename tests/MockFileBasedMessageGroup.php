<?php
/**
 * This file contains a managed message group implementation mock object.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2012, Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

class MockFileBasedMessageGroup extends FileBasedMessageGroup {
	private $randomKey;

	public function load( $code ) {
		if ( !$this->randomKey ) {
			$this->randomKey = "randomKey" . rand( 40000, 80000 );
		}

		return array( $this->randomKey => 'üga' );
	}

	public function exists() {
		return true;
	}

	public function getKeys() {
		return array_keys( $this->load( 'en' ) );
	}

}
