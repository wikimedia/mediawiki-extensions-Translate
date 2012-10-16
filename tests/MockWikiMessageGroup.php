<?php
/**
 * This file contains a unmanaged message group implementation.
 *
 * @file
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @copyright Copyright © 2008-2012, Niklas Laxström, Siebrand Mazeland
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
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
