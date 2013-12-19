<?php
/**
 * This file contains an unmanaged message group implementation.
 *
 * @file
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @copyright Copyright © 2008-2013, Niklas Laxström, Siebrand Mazeland
 * @license GPL-2.0+
 */

class MockWikiMessageGroup extends WikiMessageGroup {
	public function __construct( $id, array $messages ) {
		parent::__construct( $id, 'unused' );
		$this->id = $id;
		$this->messages = $messages;
	}

	public function getDefinitions() {
		return $this->messages;
	}
}
