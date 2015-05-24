<?php
/**
 * Test for the utilities for the sandbox feature of Translate.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0+
 */

/**
 * @group Database
 */
class TranslateSandboxTest extends MediaWikiTestCase {
	protected function setUp() {
		parent::setUp();
		$this->setMwGlobals( array(
			'wgTranslateUseSandbox' => true,
			'wgTranslateSandboxPromotedGroup' => 'translator',
		) );

		// Make sure the hooks are installed even if $wgTranslateUseSandbox is false.
		TranslateHooks::setupTranslate();
	}

	public function testAddUser() {
		$user = TranslateSandbox::addUser( 'Test user', 'test@blackhole.io', 'test password' );
		$this->assertTrue( $user->isLoggedIn(), 'User exists' );

		$groups = array_unique( $user->getGroups() );

		$this->assertSame( array( 'translate-sandboxed' ), $groups, 'User is in the sandboxed group' );
	}

	public function testDeleteUser() {
		$user = TranslateSandbox::addUser( 'Test user2', 'test@blackhole.io', 'test password' );
		TranslateSandbox::deleteUser( $user );
		$this->assertFalse( $user->isLoggedIn(), 'User no longer exists' );
	}

	/**
	 * @expectedException MWException
	 * @expectedExceptionMessage Not a sandboxed user
	 */
	public function testDeleteUserPromoted() {
		$user = TranslateSandbox::addUser( 'Test user3', 'test@blackhole.io', 'test password' );
		TranslateSandbox::promoteUser( $user );
		TranslateSandbox::deleteUser( $user );
	}

	public function testGetUsers() {
		$atStart = TranslateSandbox::getUsers()->count();

		$user = TranslateSandbox::addUser( 'Test user4', 'test@blackhole.io', 'test password' );

		$this->assertEquals(
			$atStart + 1, TranslateSandbox::getUsers()->count(),
			'One sandboxed user created'
		);

		TranslateSandbox::deleteUser( $user );
		$this->assertEquals(
			$atStart, TranslateSandbox::getUsers()->count(),
			'No sandboxed users after deleted'
		);
	}

	public function testGetUsersPromotion() {
		$atStart = TranslateSandbox::getUsers()->count();

		$user = TranslateSandbox::addUser( 'Test user5', 'test@blackhole.io', 'test password' );
		$this->assertEquals(
			$atStart + 1,
			TranslateSandbox::getUsers()->count(),
			'One sandboxed user created'
		);

		TranslateSandbox::promoteUser( $user );
		$this->assertEquals(
			$atStart,
			TranslateSandbox::getUsers()->count(),
			'No sandboxed users after promotion'
		);
	}

	public function testPromoteUser() {
		$user = TranslateSandbox::addUser( 'Test user6', 'test@blackhole.io', 'test password' );
		TranslateSandbox::promoteUser( $user );

		$this->assertContains( 'translator', $user->getGroups() );
	}

	public function testPermissions() {
		$user = TranslateSandbox::addUser( 'Test user7', 'test@blackhole.io', 'test password' );
		$title = Title::makeTitle( NS_USER_TALK, $user->getName() );

		$this->assertFalse(
			$title->userCan( 'edit', $user ),
			'Sandboxed users cannot edit their own talk page'
		);
		TranslateSandbox::promoteUser( $user );
		$this->assertTrue(
			$title->userCan( 'edit', $user ),
			'Promoted users can edit their own talk page'
		);
	}
}
