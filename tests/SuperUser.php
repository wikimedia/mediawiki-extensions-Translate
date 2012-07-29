<?php

/**
 * Just call SuperUser when you need to be able to do everything.
 */
class SuperUser extends User {
	public function getId() {
		return 666;
	}

	public function getName() {
		return 'SuperUser';
	}

	public function isAllowed( $right = '' ) {
		return true;
	}
}
