<?php
/**
 * This file contains an unmanaged message group implementation.
 *
 * @file
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @copyright Copyright © 2008-2013, Niklas Laxström, Siebrand Mazeland
 * @license GPL-2.0-or-later
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

	public function getValidator() {
		$validator = new MessageValidator( $this );
		$validator->setValidators( [
			[ 'class' => 'AnotherMockTranslateValidator' ],
			[ 'class' => 'MockTranslateValidator', 'enforce' => true ],
		] );

		return $validator;
	}
}
