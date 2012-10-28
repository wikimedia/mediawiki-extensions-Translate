<?php

/**
 * Just call SuperUser when you need to be able to do everything.
 */
class MockSuperUser extends User {
	protected $id = 666;

	public function setId( $id ) {
		$this->id = $id;
	}

	public function getId() {
		return $this->id;
	}

	public function getName() {
		return 'SuperUser';
	}

	public function isAllowed( $right = '' ) {
		return true;
	}
}
