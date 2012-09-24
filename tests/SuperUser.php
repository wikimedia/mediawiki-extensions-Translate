<?php

/**
 * Just call SuperUser when you need to be able to do everything.
 */
class SuperUser extends TestUser {

	function __construct( ) {
		parent::__construct( 'SuperUser' );
		$this->setId( 666 );
	}

	public function isAllowed( $right = '' ) {
		return true;
	}
}
