<?php
/**
 * This file contains a managed message group implementation mock object.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2012-2013, Niklas Laxström
 * @license GPL-2.0+
 */

class MockFileBasedMessageGroup extends FileBasedMessageGroup {
	public function load( $code ) {
		return array( $this->getId() . '-messagekey' => 'üga' );
	}

	public function exists() {
		return true;
	}

	public function getKeys() {
		return array_keys( $this->load( 'en' ) );
	}
}
